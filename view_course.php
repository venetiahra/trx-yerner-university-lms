<?php
require 'config.php';
require 'partials.php';
require 'student_common.php';

$stu = require_student_record($conn);

// ── Validate course ID ──
$course_id = (int)($_GET['id'] ?? 0);
if (!$course_id) {
    redirect('student_courses.php');
}

// ── Security: make sure this student is enrolled in this course ──
$enroll_check = $conn->prepare('
    SELECT c.* FROM enrollments e
    JOIN courses c ON c.id = e.course_id
    WHERE e.student_id = ? AND c.id = ?
');
$enroll_check->execute([$stu['id'], $course_id]);
$course = $enroll_check->fetch();

if (!$course) {
    redirect('student_courses.php'); // Not enrolled — redirect away
}

// ── Activities for this course (with student's grade) ──
$q_acts = $conn->prepare('
    SELECT
        a.id,
        a.title,
        a.description,
        a.type,
        a.max_score,
        a.due_date,
        a.pdf_path,
        g.score,
        g.remarks,
        g.graded_at
    FROM activities a
    LEFT JOIN grades g ON g.activity_id = a.id AND g.student_id = ?
    WHERE a.course_id = ?
      AND a.is_published = 1
    ORDER BY a.due_date ASC, a.id ASC
');
$q_acts->execute([$stu['id'], $course_id]);
$activities = $q_acts->fetchAll();

// ── Professors for this course ──
$q_profs = $conn->prepare('
    SELECT u.name AS full_name, u.email
    FROM course_professors cp
    JOIN professors p ON p.id = cp.professor_id
    JOIN users u ON u.id = p.user_id
    WHERE cp.course_id = ?
');
$q_profs->execute([$course_id]);
$profs = $q_profs->fetchAll();

// ── Which activity PDF is open (via ?pdf=activity_id) ──
$open_pdf = (int)($_GET['pdf'] ?? 0);

// ── Handle submission upload ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_activity'])) {
    $act_id  = (int)$_POST['activity_id'];
    $sub_dir = __DIR__ . '/uploads/submissions/';
    if (!is_dir($sub_dir)) mkdir($sub_dir, 0755, true);

    if (!empty($_FILES['submission_file']['name'])) {
        $file     = $_FILES['submission_file'];
        $allowed  = ['pdf','doc','docx','png','jpg','jpeg'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $orig     = basename($file['name']);

        if (in_array($ext, $allowed) && $file['size'] <= 20 * 1024 * 1024) {
            $filename = 'sub_' . $stu['id'] . '_' . $act_id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $sub_dir . $filename)) {
                // Delete old submission file if exists
                $old_q = $conn->prepare('SELECT file_path FROM submissions WHERE student_id=? AND activity_id=?');
                $old_q->execute([$stu['id'], $act_id]);
                $old_sub = $old_q->fetch();
                if ($old_sub && file_exists(__DIR__ . '/' . $old_sub['file_path'])) {
                    unlink(__DIR__ . '/' . $old_sub['file_path']);
                }
                // Upsert submission
                $conn->prepare('
                    INSERT INTO submissions (student_id, activity_id, file_path, original_filename)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE file_path=VALUES(file_path), original_filename=VALUES(original_filename), submitted_at=NOW()
                ')->execute([$stu['id'], $act_id, 'uploads/submissions/' . $filename, $orig]);

                // ── Notify professors + members + admins ──
                require_once __DIR__ . '/notif_helper.php';
                $act_info = $conn->prepare('SELECT a.title, c.code, c.id cid FROM activities a JOIN courses c ON c.id=a.course_id WHERE a.id=?');
                $act_info->execute([$act_id]);
                $arow = $act_info->fetch();
                if ($arow) {
                    // Get all professor_ids for this course
                    $np = $conn->prepare('SELECT cp.professor_id FROM course_professors cp WHERE cp.course_id = ?');
                    $np->execute([$arow['cid']]);
                    $prof_ids = $np->fetchAll(PDO::FETCH_COLUMN);

                    $notified = [];
                    foreach ($prof_ids as $pid) {
                        // Try as professors.id first → get user_id
                        $chk = $conn->prepare('SELECT user_id FROM professors WHERE id = ?');
                        $chk->execute([$pid]);
                        $prof_user_id = $chk->fetchColumn();

                        if ($prof_user_id) {
                            // it's a professors.id reference
                            $uid_to_notify = (int)$prof_user_id;
                        } else {
                            // it's a users.id reference (member/direct)
                            $uid_to_notify = (int)$pid;
                        }

                        if ($uid_to_notify && !in_array($uid_to_notify, $notified)) {
                            $notified[] = $uid_to_notify;
                            notif_push($conn, $uid_to_notify, 'new_submission',
                                'New submission in ' . $arow['code'],
                                $stu['full_name'] . ' submitted "' . $arow['title'] . '"',
                                'member_submissions.php?course_id=' . $arow['cid']
                            );
                        }
                    }
                    // notify admins
                    notif_push_admins($conn, 'new_submission',
                        'New submission: ' . $arow['title'],
                        $stu['full_name'] . ' submitted in ' . $arow['code'],
                        'submissions.php'
                    );
                }
            }
        }
    }
    redirect('view_course.php?id=' . $course_id);
}

// ── Fetch this student's submissions for this course ──
$q_subs = $conn->prepare('
    SELECT s.*, a.id act_id
    FROM submissions s
    JOIN activities a ON a.id = s.activity_id
    WHERE s.student_id = ? AND a.course_id = ?
');
$q_subs->execute([$stu['id'], $course_id]);
$submissions_map = [];
foreach ($q_subs->fetchAll() as $sub) {
    $submissions_map[$sub['act_id']] = $sub;
}

icloud_header('Course: ' . ($course['code'] ?? ''));
?>
<?php render_school_banner($conn); ?>

<div class="ic-body-solo">

  <!-- Page header -->
  <div class="ic-page-header">
    <a href="student_courses.php" class="ic-page-back">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M7.5 2L3.5 6l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      My Courses
    </a>
    <div class="ic-page-title-solo"><?= e($course['code']) ?> — <?= e($course['title'] ?? $course['name'] ?? '') ?></div>
  </div>

  <!-- Course Info Card -->
  <div class="ic-widget vc-info-widget">
    <div class="ic-widget-header">
      <div class="ic-widget-icon ic-icon-blue">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="#fff" stroke-width="1.5"/><path d="M5 6h6M5 9h4" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
      </div>
      <div>
        <div class="ic-widget-title"><?= e($course['title'] ?? $course['name'] ?? '') ?></div>
        <div class="ic-widget-sub"><?= e($course['code']) ?><?= !empty($course['program']) ? ' · ' . e($course['program']) : '' ?></div>
      </div>
    </div>
    <div class="ic-widget-body">
      <div class="vc-meta-grid">
        <?php if (!empty($course['units'])): ?>
        <div class="vc-meta-item">
          <span class="vc-meta-label">Units</span>
          <span class="vc-meta-value"><?= e($course['units']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($course['semester'])): ?>
        <div class="vc-meta-item">
          <span class="vc-meta-label">Semester</span>
          <span class="vc-meta-value"><?= e($course['semester']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($course['school_year'])): ?>
        <div class="vc-meta-item">
          <span class="vc-meta-label">School Year</span>
          <span class="vc-meta-value"><?= e($course['school_year']) ?></span>
        </div>
        <?php endif; ?>
        <div class="vc-meta-item">
          <span class="vc-meta-label">Activities</span>
          <span class="vc-meta-value"><?= count($activities) ?></span>
        </div>
      </div>
      <?php if (!empty($course['description'])): ?>
      <div class="vc-description"><?= e($course['description']) ?></div>
      <?php endif; ?>

      <?php if ($profs): ?>
      <div class="vc-profs">
        <div class="vc-section-label">👩‍🏫 Professors</div>
        <div class="vc-profs-list">
          <?php foreach ($profs as $p):
            $parts = explode(' ', trim($p['full_name']));
            $ini   = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
          ?>
          <div class="vc-prof-chip">
            <div class="ic-prof-avatar vc-prof-avatar"><?= e($ini) ?></div>
            <div>
              <div class="vc-prof-name"><?= e($p['full_name']) ?></div>
              <div class="vc-prof-email"><?= e($p['email']) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Activities List -->
  <div class="ic-widget">
    <div class="ic-widget-header">
      <div class="ic-widget-icon ic-icon-teal">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="#fff" stroke-width="1.5"/><path d="M5.5 8l1.5 1.5L10 6" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div>
        <div class="ic-widget-title">Activities & Grades</div>
        <div class="ic-widget-sub">Click an activity to expand details<?= count($activities) > 0 ? ' · ' . count($activities) . ' total' : '' ?></div>
      </div>
    </div>
    <div class="ic-widget-body" style="padding:0;">
      <?php if ($activities): ?>
        <table class="ic-table ic-grades-table">
          <thead>
            <tr>
              <th>Activity</th>
              <th>Type</th>
              <th style="text-align:right;">Score</th>
              <th style="width:28px;"></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($activities as $i => $a):
            $pct = ($a['max_score'] > 0 && $a['score'] !== null)
                   ? ($a['score'] / $a['max_score'] * 100) : 0;
            $cls     = $pct >= 85 ? 'ic-pill-hi' : ($pct >= 70 ? 'ic-pill-mid' : 'ic-pill-lo');
            $no_score = $a['score'] === null;
            $type_icon = match($a['type'] ?? '') {
              'quiz'       => '📝',
              'exam'       => '📋',
              'assignment' => '📌',
              'project'    => '🗂️',
              default      => '📄',
            };
            $due    = !empty($a['due_date']) ? date('M d, Y', strtotime($a['due_date'])) : '—';
            $graded = !empty($a['graded_at']) ? date('M d, Y', strtotime($a['graded_at'])) : 'Not yet graded';
            $row_id = 'act-detail-' . $i;
            $auto_open = ($open_pdf && $open_pdf == $a['id']) ? 'true' : 'false';
          ?>
          <!-- Activity row -->
          <tr class="ic-grade-row <?= $auto_open === 'true' ? 'ic-row-autoopen' : '' ?>"
              onclick="toggleActDetail('<?= $row_id ?>', this)"
              aria-expanded="<?= $auto_open ?>">
            <td>
              <?= e($a['title']) ?>
              <?php if (!empty($a['pdf_path'])): ?>
                <span class="vc-pdf-badge">📄 PDF</span>
              <?php endif; ?>
            </td>
            <td><?= $type_icon ?> <?= ucfirst(e($a['type'] ?? 'activity')) ?></td>
            <td style="text-align:right;">
              <?php if ($no_score): ?>
                <span class="ic-grade-pill" style="opacity:.5;">—/<?= e($a['max_score']) ?></span>
              <?php else: ?>
                <span class="ic-grade-pill <?= $cls ?>"><?= e($a['score']) ?>/<?= e($a['max_score']) ?></span>
              <?php endif; ?>
            </td>
            <td class="ic-grade-chevron">
              <svg class="ic-chevron-icon" width="14" height="14" viewBox="0 0 12 12" fill="none">
                <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </td>
          </tr>

          <!-- Accordion detail row -->
          <tr class="ic-grade-detail-row <?= $auto_open === 'true' ? 'ic-open' : '' ?>" id="<?= $row_id ?>">
            <td colspan="4" style="padding:0;">
              <div class="ic-grade-detail-body">
                <div class="ic-grade-detail-grid">

                  <div class="ic-detail-item">
                    <span class="ic-detail-label">Due Date</span>
                    <span class="ic-detail-value"><?= $due ?></span>
                  </div>

                  <div class="ic-detail-item">
                    <span class="ic-detail-label">Max Score</span>
                    <span class="ic-detail-value"><?= e($a['max_score']) ?> pts</span>
                  </div>

                  <div class="ic-detail-item">
                    <span class="ic-detail-label">Your Score</span>
                    <span class="ic-detail-value">
                      <?php if ($no_score): ?>
                        <span style="opacity:.5;">Not yet graded</span>
                      <?php else: ?>
                        <span class="ic-grade-pill <?= $cls ?> ic-pill-lg">
                          <?= e($a['score']) ?> / <?= e($a['max_score']) ?>
                          (<?= number_format($pct, 1) ?>%)
                        </span>
                      <?php endif; ?>
                    </span>
                  </div>

                  <div class="ic-detail-item">
                    <span class="ic-detail-label">Graded On</span>
                    <span class="ic-detail-value"><?= $graded ?></span>
                  </div>

                  <?php if (!empty($a['remarks'])): ?>
                  <div class="ic-detail-item ic-detail-full">
                    <span class="ic-detail-label">Remarks</span>
                    <span class="ic-detail-value"><?= e($a['remarks']) ?></span>
                  </div>
                  <?php endif; ?>

                  <?php if (!empty($a['description'])): ?>
                  <div class="ic-detail-item ic-detail-full">
                    <span class="ic-detail-label">Description</span>
                    <span class="ic-detail-value ic-detail-desc"><?= e($a['description']) ?></span>
                  </div>
                  <?php endif; ?>

                </div>

                <!-- ── PDF Download Button ── -->
                <?php if (!empty($a['pdf_path'])): ?>
                <div style="margin-bottom:14px;">
                  <a href="<?= e($a['pdf_path']) ?>" download
                     class="btn ic-icon-teal"
                     style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                    📄 Download Activity PDF
                  </a>
                </div>
                <?php endif; ?>

                <!-- ── Submission Section ── -->
                <?php $my_sub = $submissions_map[$a['id']] ?? null; ?>
                <div class="vc-submit-section">
                  <div class="vc-submit-label">
                    📤 My Submission
                    <?php if ($my_sub): ?>
                      <span class="vc-submit-badge vc-badge-ok">✓ Submitted</span>
                    <?php else: ?>
                      <span class="vc-submit-badge vc-badge-pending">Not yet submitted</span>
                    <?php endif; ?>
                  </div>

                  <?php if ($my_sub): ?>
                  <div class="vc-submit-info">
                    <span>📎 <?= e($my_sub['original_filename']) ?></span>
                    <span style="opacity:.55;font-size:11px;">Submitted <?= date('M d, Y h:i A', strtotime($my_sub['submitted_at'])) ?></span>
                    <a href="<?= e($my_sub['file_path']) ?>" target="_blank" class="vc-pdf-dl">↗ View file</a>
                  </div>
                  <?php endif; ?>

                  <form method="post" enctype="multipart/form-data" class="vc-submit-form">
                    <input type="hidden" name="submit_activity" value="1">
                    <input type="hidden" name="activity_id" value="<?= $a['id'] ?>">
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                      <input type="file" name="submission_file"
                             accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                             style="flex:1;min-width:200px;font-size:13px;"
                             required>
                      <button type="submit" class="btn ic-icon-teal" style="white-space:nowrap;">
                        <?= $my_sub ? '🔄 Re-submit' : '📤 Submit Answer' ?>
                      </button>
                    </div>
                    <div style="font-size:11px;color:rgba(255,255,255,.4);margin-top:5px;">
                      Accepted: PDF, DOC, DOCX, PNG, JPG · Max 20MB
                    </div>
                  </form>
                </div>

              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="ic-empty-state">
          <div class="ic-empty-state-icon">
            <svg width="22" height="22" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="rgba(255,255,255,.35)" stroke-width="1.5"/><path d="M5.5 8l1.5 1.5L10 6" stroke="rgba(255,255,255,.35)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <p>No published activities yet.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Quick Access -->
  <div class="ic-apps-widget">
    <div class="ic-apps-label">Quick access</div>
    <div class="ic-apps-grid">
      <a href="student_dashboard.php" class="ic-app"><div class="ic-app-icon ic-icon-gold"><svg width="20" height="20" viewBox="0 0 16 16" fill="none"><path d="M2 7l6-5 6 5v7a1 1 0 01-1 1H3a1 1 0 01-1-1V7z" stroke="#fff" stroke-width="1.5" stroke-linejoin="round"/><path d="M6 14V9h4v5" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg></div><span>Home</span></a>
      <a href="student_courses.php" class="ic-app ic-app-active"><div class="ic-app-icon ic-icon-blue"><svg width="20" height="20" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="#fff" stroke-width="1.5"/><path d="M5 6h6M5 9h4" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg></div><span>Courses</span></a>
      <a href="student_grades.php" class="ic-app"><div class="ic-app-icon ic-icon-teal"><svg width="20" height="20" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="#fff" stroke-width="1.5"/><path d="M5.5 8l1.5 1.5L10 6" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></div><span>Grades</span></a>
      <a href="student_professors.php" class="ic-app"><div class="ic-app-icon ic-icon-navy"><svg width="20" height="20" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="4.5" r="2.5" stroke="#fff" stroke-width="1.5"/><path d="M2 14c0-2.761 2.686-4.5 6-4.5s6 1.739 6 4.5" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg></div><span>Professors</span></a>
      <a href="profile.php" class="ic-app"><div class="ic-app-icon ic-icon-coral"><svg width="20" height="20" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="#fff" stroke-width="1.5"/><circle cx="8" cy="6" r="2.5" stroke="#fff" stroke-width="1.3"/><path d="M3.5 13c0-2.485 2.015-4 4.5-4s4.5 1.515 4.5 4" stroke="#fff" stroke-width="1.3" stroke-linecap="round"/></svg></div><span>Profile</span></a>
    </div>
  </div>

</div>

<!-- ── Styles ── -->
<style>
/* Course meta grid */
.vc-meta-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 12px 24px;
  margin-bottom: 12px;
}
.vc-meta-item { display: flex; flex-direction: column; gap: 2px; }
.vc-meta-label { font-size: 10px; text-transform: uppercase; letter-spacing: .06em; color: rgba(255,255,255,.4); font-weight: 600; }
.vc-meta-value { font-size: 13.5px; color: rgba(255,255,255,.85); }

.vc-description {
  font-size: 13px;
  color: rgba(255,255,255,.65);
  line-height: 1.55;
  margin-top: 4px;
  margin-bottom: 12px;
}

/* Professors */
.vc-section-label { font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: rgba(255,255,255,.4); font-weight: 600; margin-bottom: 8px; }
.vc-profs-list { display: flex; flex-wrap: wrap; gap: 10px; }
.vc-prof-chip { display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,.06); border-radius: 8px; padding: 8px 12px; }
.vc-prof-avatar { width: 30px; height: 30px; font-size: 11px; }
.vc-prof-name { font-size: 13px; color: rgba(255,255,255,.85); font-weight: 500; }
.vc-prof-email { font-size: 11px; color: rgba(255,255,255,.4); }
.vc-profs { margin-top: 14px; border-top: 1px solid rgba(255,255,255,.08); padding-top: 14px; }

/* PDF badge on activity row */
.vc-pdf-badge {
  display: inline-block;
  font-size: .7rem;
  background: rgba(59,130,246,.18);
  color: #93c5fd;
  border: 1px solid rgba(59,130,246,.3);
  border-radius: 4px;
  padding: 1px 5px;
  margin-left: 6px;
  vertical-align: middle;
}

/* PDF section inside accordion */
.vc-pdf-section {
  margin-top: 14px;
  border-top: 1px solid rgba(255,255,255,.1);
  padding-top: 12px;
}
.vc-pdf-label {
  font-size: 12px;
  font-weight: 600;
  color: rgba(255,255,255,.55);
  text-transform: uppercase;
  letter-spacing: .05em;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.vc-pdf-dl {
  font-size: 12px;
  color: #93c5fd;
  text-decoration: none;
  font-weight: 500;
}
.vc-pdf-dl:hover { text-decoration: underline; }

.vc-pdf-iframe {
  width: 100%;
  height: 520px;
  border: none;
  border-radius: 8px;
  background: #fff;
}

/* Reuse accordion styles from student_grades.php */
.ic-grade-row { cursor: pointer; transition: background 0.15s; }
.ic-grade-row:hover { background: rgba(255,255,255,0.06); }
.ic-grade-row[aria-expanded="true"] { background: rgba(255,255,255,0.08); }
.ic-grade-chevron { text-align: center; color: rgba(255,255,255,0.4); }
.ic-chevron-icon { transition: transform 0.25s ease; display: inline-block; }
.ic-grade-row[aria-expanded="true"] .ic-chevron-icon { transform: rotate(180deg); }
.ic-grade-detail-row { display: none; }
.ic-grade-detail-row.ic-open { display: table-row; }
.ic-grade-detail-body {
  background: rgba(255,255,255,0.04);
  border-top: 1px solid rgba(255,255,255,0.08);
  padding: 14px 18px 16px;
  animation: ic-slide-down 0.22s ease;
}
@keyframes ic-slide-down {
  from { opacity: 0; transform: translateY(-6px); }
  to   { opacity: 1; transform: translateY(0); }
}
.ic-grade-detail-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px 20px; }
.ic-detail-full { grid-column: 1 / -1; }
.ic-detail-item { display: flex; flex-direction: column; gap: 3px; }
.ic-detail-label { font-size: 10px; text-transform: uppercase; letter-spacing: .06em; color: rgba(255,255,255,.4); font-weight: 600; }
.ic-detail-value { font-size: 13px; color: rgba(255,255,255,.85); }
.ic-detail-desc { font-size: 12.5px; color: rgba(255,255,255,.65); line-height: 1.5; }
.ic-pill-lg { font-size: 13px; padding: 4px 10px; }

/* Clickable course rows */
.ic-course-row-link { display: flex; text-decoration: none; color: inherit; transition: background 0.15s; }
.ic-course-row-link:hover { background: rgba(255,255,255,.06); border-radius: 8px; }

/* Submission section */
.vc-submit-section {
  margin-top: 16px;
  border-top: 1px solid rgba(255,255,255,.1);
  padding-top: 14px;
}
.vc-submit-label {
  font-size: 12px;
  font-weight: 600;
  color: rgba(255,255,255,.55);
  text-transform: uppercase;
  letter-spacing: .05em;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.vc-submit-badge {
  font-size: 11px;
  font-weight: 600;
  border-radius: 20px;
  padding: 2px 10px;
  text-transform: none;
  letter-spacing: 0;
}
.vc-badge-ok      { background: rgba(34,197,94,.2);  color: #86efac; border: 1px solid rgba(34,197,94,.3); }
.vc-badge-pending { background: rgba(234,179,8,.15); color: #fde047; border: 1px solid rgba(234,179,8,.3); }
.vc-submit-info {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 12px;
  font-size: 13px;
  color: rgba(255,255,255,.75);
  background: rgba(255,255,255,.05);
  border-radius: 8px;
  padding: 10px 14px;
  margin-bottom: 12px;
}
.vc-submit-form { }
</style>

<!-- ── Accordion Script ── -->
<script>
function toggleActDetail(id, row) {
  var detail = document.getElementById(id);
  var isOpen = detail.classList.contains('ic-open');

  // Close all
  document.querySelectorAll('.ic-grade-detail-row.ic-open').forEach(function(el) { el.classList.remove('ic-open'); });
  document.querySelectorAll('.ic-grade-row[aria-expanded="true"]').forEach(function(el) { el.setAttribute('aria-expanded', 'false'); });

  if (!isOpen) {
    detail.classList.add('ic-open');
    row.setAttribute('aria-expanded', 'true');
  }
}

// Auto-open if ?pdf= param is set
document.querySelectorAll('.ic-row-autoopen').forEach(function(row) {
  row.setAttribute('aria-expanded', 'true');
});
</script>

<?php icloud_footer(); ?>
