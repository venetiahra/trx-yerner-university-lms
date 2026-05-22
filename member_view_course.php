<?php
require 'config.php';
require 'partials.php';
require_member();

// ── Ensure grades table has graded_by column ──
try { $conn->exec("ALTER TABLE grades ADD COLUMN graded_by INT NULL"); } catch (PDOException $e) {}

// ── Course ID ──
$course_id = (int)($_GET['id'] ?? 0);
if (!$course_id) redirect('member_courses.php');

$course = $conn->prepare('SELECT * FROM courses WHERE id = ?');
$course->execute([$course_id]);
$course = $course->fetch();
if (!$course) redirect('member_courses.php');

// ── Save Grade (POST) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_grade'])) {
    $student_id  = (int)$_POST['student_id'];
    $activity_id = (int)$_POST['activity_id'];
    $score       = $_POST['score'] !== '' ? (float)$_POST['score'] : null;
    $remarks     = trim($_POST['remarks'] ?? '');
    $graded_by   = (int)$_SESSION['user_id'];

    $check = $conn->prepare('SELECT id FROM grades WHERE student_id=? AND activity_id=?');
    $check->execute([$student_id, $activity_id]);
    if ($check->fetch()) {
        $conn->prepare('
            UPDATE grades
            SET score=?, remarks=?, graded_by=?, graded_at=NOW(), updated_at=NOW()
            WHERE student_id=? AND activity_id=?
        ')->execute([$score, $remarks, $graded_by, $student_id, $activity_id]);
    } else {
        $conn->prepare('
            INSERT INTO grades (student_id, activity_id, score, remarks, graded_by, graded_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ')->execute([$student_id, $activity_id, $score, $remarks, $graded_by]);
    }

    flash('success', 'Grade saved successfully.');

    // ── Notify student + admins ──
    require_once __DIR__ . '/notif_helper.php';
    $act_q = $conn->prepare('
        SELECT a.title, a.max_score, c.code
        FROM activities a JOIN courses c ON c.id = a.course_id
        WHERE a.id = ?
    ');
    $act_q->execute([$activity_id]);
    $act_row = $act_q->fetch();

    $stu_q = $conn->prepare('SELECT u.id FROM users u WHERE u.student_id = ?');
    $stu_q->execute([$student_id]);
    $stu_uid = (int)$stu_q->fetchColumn();

    if ($act_row && $score !== null) {
        if ($stu_uid) {
            notif_push($conn, $stu_uid, 'grade_posted',
                'Grade posted: ' . $act_row['title'],
                'You scored ' . $score . ' / ' . $act_row['max_score'] . ' in ' . $act_row['code'],
                'student_grades.php'
            );
        }
        notif_push_admins($conn, 'grade_posted',
            'Grade posted: ' . $act_row['title'],
            'Score: ' . $score . ' / ' . $act_row['max_score'] . ' (' . $act_row['code'] . ')',
            'submissions.php'
        );
    }

    redirect('member_view_course.php?id=' . $course_id . '&activity_id=' . $activity_id);
}

// ── Activities for this course ──
$activities = $conn->prepare('
    SELECT * FROM activities
    WHERE course_id = ?
    ORDER BY due_date ASC, id ASC
');
$activities->execute([$course_id]);
$activities = $activities->fetchAll();

// ── Active activity filter ──
$active_activity = (int)($_GET['activity_id'] ?? ($activities[0]['id'] ?? 0));

// ── Submissions for selected activity ──
$submissions = [];
if ($active_activity) {
    $stmt = $conn->prepare('
        SELECT
            sub.id            sub_id,
            sub.student_id,
            sub.activity_id,
            sub.file_path,
            sub.original_filename,
            sub.submitted_at,
            s.full_name       student_name,
            s.student_no,
            a.title           activity_title,
            a.max_score,
            a.type            activity_type,
            g.score,
            g.remarks,
            g.graded_at
        FROM submissions sub
        JOIN students  s ON s.id  = sub.student_id
        JOIN activities a ON a.id = sub.activity_id
        LEFT JOIN grades g ON g.student_id = sub.student_id AND g.activity_id = sub.activity_id
        WHERE sub.activity_id = ?
        ORDER BY sub.submitted_at DESC
    ');
    $stmt->execute([$active_activity]);
    $submissions = $stmt->fetchAll();
}

// ── Current activity details ──
$current_activity = null;
foreach ($activities as $act) {
    if ($act['id'] == $active_activity) { $current_activity = $act; break; }
}

icloud_header('Course: ' . $course['code']);
render_school_banner($conn);
render_left_quick_access('member_courses');

$ok  = flash('success');
$err = flash('error');
?>

<div class="ic-body-solo">

  <!-- Page Header -->
  <div class="ic-page-header">
    <a href="member_courses.php" class="ic-page-back">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M7.5 2L3.5 6l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Courses
    </a>
    <div class="ic-page-title-solo"><?= e($course['code']) ?> — <?= e($course['title']) ?></div>
  </div>

  <!-- Flash Messages -->
  <?php if ($ok): ?>
  <div class="mvc-flash mvc-flash-ok">✅ <?= e($ok) ?></div>
  <?php endif; ?>
  <?php if ($err): ?>
  <div class="mvc-flash mvc-flash-err">⚠️ <?= e($err) ?></div>
  <?php endif; ?>

  <!-- Course Info -->
  <div class="ic-widget">
    <div class="ic-widget-header">
      <div class="ic-widget-icon ic-icon-blue">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="#fff" stroke-width="1.5"/><path d="M5 6h6M5 9h4" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
      </div>
      <div>
        <div class="ic-widget-title"><?= e($course['title']) ?></div>
        <div class="ic-widget-sub"><?= e($course['code']) ?><?= !empty($course['program']) ? ' · ' . e($course['program']) : '' ?> · <?= count($activities) ?> activities</div>
      </div>
    </div>
  </div>

  <?php if (empty($activities)): ?>
  <!-- No activities yet -->
  <div class="ic-widget">
    <div class="ic-widget-body">
      <div class="ic-empty-state">
        <div class="ic-empty-state-icon">
          <svg width="22" height="22" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="rgba(255,255,255,.35)" stroke-width="1.5"/><path d="M5.5 8l1.5 1.5L10 6" stroke="rgba(255,255,255,.35)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p>No activities for this course yet.</p>
      </div>
    </div>
  </div>

  <?php else: ?>

  <!-- Activity Tabs -->
  <div class="ic-widget" style="padding:0;overflow:hidden;">
    <div class="mvc-tab-label">Select Activity</div>
    <div class="mvc-tabs">
      <?php foreach ($activities as $act):
        $type_icon = match($act['type'] ?? '') {
          'quiz'       => '📝',
          'exam'       => '📋',
          'assignment' => '📌',
          'project'    => '🗂️',
          default      => '📄',
        };
      ?>
      <a href="member_view_course.php?id=<?= $course_id ?>&activity_id=<?= $act['id'] ?>"
         class="mvc-tab <?= $act['id'] == $active_activity ? 'mvc-tab-active' : '' ?>">
        <?= $type_icon ?> <?= e($act['title']) ?>
        <?php if (!empty($act['due_date'])): ?>
          <span class="mvc-tab-due">Due <?= date('M d', strtotime($act['due_date'])) ?></span>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Submissions for Selected Activity -->
  <?php if ($current_activity): ?>
  <div class="ic-widget">
    <div class="ic-widget-header">
      <div class="ic-widget-icon ic-icon-teal">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><path d="M3 2h7l3 3v9a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1z" stroke="#fff" stroke-width="1.5"/><path d="M8 6v5M6 9l2 2 2-2" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div>
        <div class="ic-widget-title">📥 Student Submissions</div>
        <div class="ic-widget-sub">
          <?= $current_activity['title'] ?> · Max: <?= e($current_activity['max_score']) ?> pts ·
          <span style="color:#86efac;"><?= count($submissions) ?> submission<?= count($submissions) !== 1 ? 's' : '' ?></span>
        </div>
      </div>
    </div>

    <div class="ic-widget-body" style="padding-top:0;">

      <?php if (empty($submissions)): ?>
      <div class="ic-empty-state">
        <div class="ic-empty-state-icon">
          <svg width="22" height="22" viewBox="0 0 16 16" fill="none"><path d="M3 2h7l3 3v9a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1z" stroke="rgba(255,255,255,.35)" stroke-width="1.5"/></svg>
        </div>
        <p>No submissions yet for this activity.</p>
      </div>

      <?php else: ?>
      <?php foreach ($submissions as $sub):
        $is_graded = $sub['score'] !== null;
        $pct = ($current_activity['max_score'] > 0 && $is_graded)
               ? ($sub['score'] / $current_activity['max_score'] * 100) : 0;
        $pill_cls = $pct >= 85 ? 'ic-pill-hi' : ($pct >= 70 ? 'ic-pill-mid' : 'ic-pill-lo');
      ?>

      <!-- Submission Card -->
      <div class="mvc-sub-card <?= $is_graded ? 'mvc-graded' : '' ?>">

        <!-- Student Info + Status -->
        <div class="mvc-sub-top">
          <div class="mvc-sub-student">
            <div class="mvc-avatar"><?= strtoupper(substr($sub['student_name'], 0, 1)) ?></div>
            <div>
              <div class="mvc-student-name"><?= e($sub['student_name']) ?></div>
              <div class="mvc-student-no"><?= e($sub['student_no']) ?></div>
            </div>
          </div>
          <div class="mvc-sub-meta">
            <div style="font-size:11px;color:rgba(255,255,255,.45);">
              Submitted <?= date('M d, Y', strtotime($sub['submitted_at'])) ?>
              <br><?= date('h:i A', strtotime($sub['submitted_at'])) ?>
            </div>
            <?php if ($is_graded): ?>
              <span class="mvc-badge mvc-badge-graded">✅ Graded</span>
            <?php else: ?>
              <span class="mvc-badge mvc-badge-pending">⏳ Pending</span>
            <?php endif; ?>
          </div>
        </div>

        <!-- File Link -->
        <div class="mvc-file-row">
          <a href="<?= e($sub['file_path']) ?>" target="_blank" class="mvc-file-btn">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M3 2h7l3 3v9a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1z" stroke="currentColor" stroke-width="1.5"/><path d="M10 2v3h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            📎 <?= e(mb_strimwidth($sub['original_filename'] ?? 'View File', 0, 35, '…')) ?>
          </a>
          <?php if ($is_graded): ?>
          <span class="ic-grade-pill <?= $pill_cls ?>" style="font-size:12px;">
            <?= e($sub['score']) ?> / <?= e($current_activity['max_score']) ?>
          </span>
          <?php endif; ?>
        </div>

        <!-- Grade Form -->
        <form method="post" class="mvc-grade-form">
          <input type="hidden" name="save_grade"   value="1">
          <input type="hidden" name="student_id"   value="<?= $sub['student_id'] ?>">
          <input type="hidden" name="activity_id"  value="<?= $sub['activity_id'] ?>">

          <div class="mvc-form-row">
            <div class="mvc-form-group">
              <label class="mvc-label">Score</label>
              <div style="display:flex;align-items:center;gap:6px;">
                <input type="number" name="score"
                       value="<?= $is_graded ? e($sub['score']) : '' ?>"
                       min="0" max="<?= e($current_activity['max_score']) ?>"
                       step="0.01" placeholder="—"
                       class="mvc-score-input">
                <span style="font-size:12px;color:rgba(255,255,255,.4);">/ <?= e($current_activity['max_score']) ?></span>
              </div>
            </div>

            <div class="mvc-form-group" style="flex:1;">
              <label class="mvc-label">Feedback / Remarks</label>
              <textarea name="remarks" rows="2" placeholder="Optional feedback…" class="mvc-remarks-input"><?= e($sub['remarks'] ?? '') ?></textarea>
            </div>
          </div>

          <div style="display:flex;justify-content:flex-end;margin-top:10px;">
            <button type="submit" class="mvc-save-btn">
              <?= $is_graded ? '🔄 Update Grade' : '✅ Save Grade' ?>
            </button>
          </div>
        </form>

      </div>
      <?php endforeach; ?>
      <?php endif; ?>

    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <!-- Quick Access -->

</div>

<style>
/* Flash */
.mvc-flash { padding:10px 16px; border-radius:10px; margin-bottom:12px; font-size:13px; font-weight:500; }
.mvc-flash-ok  { background:rgba(34,197,94,.15); color:#86efac; border:1px solid rgba(34,197,94,.25); }
.mvc-flash-err { background:rgba(239,68,68,.15);  color:#fca5a5; border:1px solid rgba(239,68,68,.25); }

/* Activity Tabs */
.mvc-tab-label {
  font-size:10px; text-transform:uppercase; letter-spacing:.07em;
  color:rgba(255,255,255,.35); font-weight:600;
  padding:14px 16px 6px;
}
.mvc-tabs {
  display:flex; flex-direction:column; gap:2px;
  padding:0 10px 10px;
}
.mvc-tab {
  display:flex; align-items:center; gap:8px;
  padding:10px 12px; border-radius:8px;
  font-size:13px; color:rgba(255,255,255,.7);
  text-decoration:none; transition:background .15s;
  border:1px solid transparent;
}
.mvc-tab:hover { background:rgba(255,255,255,.06); }
.mvc-tab-active {
  background:rgba(59,130,246,.18);
  color:#93c5fd;
  border-color:rgba(59,130,246,.3);
  font-weight:600;
}
.mvc-tab-due {
  margin-left:auto; font-size:10px;
  color:rgba(255,255,255,.35);
  white-space:nowrap;
}

/* Submission Cards */
.mvc-sub-card {
  background:rgba(255,255,255,.04);
  border:1px solid rgba(255,255,255,.09);
  border-radius:12px;
  padding:14px 16px;
  margin-bottom:12px;
  transition:border-color .2s;
}
.mvc-sub-card:last-child { margin-bottom:0; }
.mvc-graded { border-color:rgba(34,197,94,.2); }

.mvc-sub-top {
  display:flex; justify-content:space-between;
  align-items:flex-start; gap:10px; margin-bottom:12px;
}
.mvc-sub-student { display:flex; align-items:center; gap:10px; }
.mvc-avatar {
  width:34px; height:34px; border-radius:50%;
  background:rgba(59,130,246,.25);
  color:#93c5fd; font-size:14px; font-weight:700;
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0;
}
.mvc-student-name { font-size:13.5px; font-weight:600; color:rgba(255,255,255,.9); }
.mvc-student-no   { font-size:11px; color:rgba(255,255,255,.4); margin-top:1px; }

.mvc-sub-meta { display:flex; flex-direction:column; align-items:flex-end; gap:6px; }
.mvc-badge {
  font-size:10.5px; font-weight:600; border-radius:20px;
  padding:2px 10px; white-space:nowrap;
}
.mvc-badge-graded  { background:rgba(34,197,94,.18); color:#86efac; border:1px solid rgba(34,197,94,.3); }
.mvc-badge-pending { background:rgba(234,179,8,.15); color:#fde047; border:1px solid rgba(234,179,8,.3); }

/* File Row */
.mvc-file-row {
  display:flex; align-items:center; gap:10px;
  flex-wrap:wrap; margin-bottom:14px;
}
.mvc-file-btn {
  display:inline-flex; align-items:center; gap:6px;
  padding:6px 14px; border-radius:8px;
  background:rgba(255,255,255,.08);
  border:1px solid rgba(255,255,255,.12);
  color:rgba(255,255,255,.8); font-size:12.5px;
  text-decoration:none; transition:background .15s;
}
.mvc-file-btn:hover { background:rgba(255,255,255,.14); }

/* Grade Form */
.mvc-grade-form { border-top:1px solid rgba(255,255,255,.08); padding-top:14px; }
.mvc-form-row { display:flex; gap:12px; flex-wrap:wrap; align-items:flex-start; }
.mvc-form-group { display:flex; flex-direction:column; gap:5px; }
.mvc-label {
  font-size:10px; text-transform:uppercase;
  letter-spacing:.06em; color:rgba(255,255,255,.4); font-weight:600;
}
.mvc-score-input {
  width:80px; padding:7px 10px;
  border-radius:8px; border:1px solid rgba(255,255,255,.15);
  background:rgba(255,255,255,.07); color:#fff;
  font-size:14px; font-weight:600;
}
.mvc-score-input:focus { outline:none; border-color:rgba(59,130,246,.5); background:rgba(59,130,246,.1); }
.mvc-remarks-input {
  width:100%; min-width:200px; padding:7px 10px;
  border-radius:8px; border:1px solid rgba(255,255,255,.15);
  background:rgba(255,255,255,.07); color:rgba(255,255,255,.85);
  font-size:13px; resize:vertical;
}
.mvc-remarks-input:focus { outline:none; border-color:rgba(59,130,246,.5); }
.mvc-save-btn {
  padding:8px 20px; border-radius:8px;
  background:linear-gradient(135deg,#2563eb,#1d4ed8);
  color:#fff; font-size:13px; font-weight:600;
  border:none; cursor:pointer; transition:opacity .15s;
}
.mvc-save-btn:hover { opacity:.88; }
</style>

<?php icloud_footer(); ?>
