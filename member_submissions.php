<?php
require 'config.php';
require 'partials.php';
require_member();

$uid = (int)($_SESSION['user_id'] ?? 0);

$my_course_ids = [];
$cq = $conn->prepare('SELECT course_id FROM course_professors WHERE professor_id = ?');
$cq->execute([$uid]);
$my_course_ids = array_column($cq->fetchAll(), 'course_id');

// ── Save grade (POST) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_grade'])) {
    $student_id  = (int)$_POST['student_id'];
    $activity_id = (int)$_POST['activity_id'];
    $score       = $_POST['score'] !== '' ? (float)$_POST['score'] : null;
    $remarks     = trim($_POST['remarks'] ?? '');
    $graded_by   = $uid;

    $check = $conn->prepare('SELECT id FROM grades WHERE student_id=? AND activity_id=?');
    $check->execute([$student_id, $activity_id]);
    if ($check->fetch()) {
        $conn->prepare('
            UPDATE grades SET score=?, remarks=?, graded_by=?, graded_at=NOW(), updated_at=NOW()
            WHERE student_id=? AND activity_id=?
        ')->execute([$score, $remarks, $graded_by, $student_id, $activity_id]);
    } else {
        $conn->prepare('
            INSERT INTO grades (student_id, activity_id, score, remarks, graded_by, graded_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ')->execute([$student_id, $activity_id, $score, $remarks, $graded_by]);
    }

    flash('success', 'Grade saved.');

    // ── Notify student + admins ──
    require_once __DIR__ . '/notif_helper.php';
    $act_q = $conn->prepare('
        SELECT a.title, a.max_score, c.code
        FROM activities a JOIN courses c ON c.id = a.course_id
        WHERE a.id = ?
    ');
    $act_q->execute([$activity_id]);
    $act_row = $act_q->fetch();

    // get student's user_id
    $stu_q = $conn->prepare('SELECT u.id FROM users u WHERE u.student_id = ?');
    $stu_q->execute([$student_id]);
    $stu_uid = (int)$stu_q->fetchColumn();

    if ($act_row && $score !== null) {
        // notify student
        if ($stu_uid) {
            notif_push($conn, $stu_uid, 'grade_posted',
                'Grade posted: ' . $act_row['title'],
                'You scored ' . $score . ' / ' . $act_row['max_score'] . ' in ' . $act_row['code'],
                'student_grades.php'
            );
        }
        // notify admins
        notif_push_admins($conn, 'grade_posted',
            'Grade posted: ' . $act_row['title'],
            'Score: ' . $score . ' / ' . $act_row['max_score'] . ' (' . $act_row['code'] . ')',
            'submissions.php'
        );
    }

    redirect('member_submissions.php' . (!empty($_GET['course_id']) ? '?course_id=' . (int)$_GET['course_id'] : ''));
}

$filter_course = (int)($_GET['course_id'] ?? 0);
$filter_status = $_GET['status'] ?? '';

$my_courses = [];
if ($my_course_ids) {
    $ids_str = implode(',', $my_course_ids);
    $my_courses = $conn->query("SELECT id, code, title FROM courses WHERE id IN ($ids_str) ORDER BY code ASC")->fetchAll();
}

$submissions = [];
if ($my_course_ids) {
    $ids_str = implode(',', $my_course_ids);
    $where = "WHERE a.course_id IN ($ids_str)";
    if ($filter_course && in_array($filter_course, $my_course_ids))
        $where .= " AND a.course_id = $filter_course";
    if ($filter_status === 'graded')   $where .= ' AND g.score IS NOT NULL';
    if ($filter_status === 'ungraded') $where .= ' AND (g.score IS NULL OR g.id IS NULL)';

    $submissions = $conn->query("
        SELECT
            sub.id sub_id, sub.student_id, sub.activity_id, sub.file_path,
            sub.original_filename, sub.submitted_at,
            s.full_name student_name, s.student_no,
            a.title activity_title, a.max_score, a.type activity_type,
            c.id course_id, c.code course_code, c.title course_title,
            g.id grade_id, g.score, g.remarks, g.graded_at
        FROM submissions sub
        JOIN students  s ON s.id  = sub.student_id
        JOIN activities a ON a.id = sub.activity_id
        JOIN courses   c ON c.id  = a.course_id
        LEFT JOIN grades g ON g.student_id = sub.student_id AND g.activity_id = sub.activity_id
        $where
        ORDER BY sub.submitted_at DESC
    ")->fetchAll();
}

$ok  = flash('success');
$err = flash('error');

icloud_header('Submissions');
render_school_banner($conn);
render_left_quick_access('member_submissions');
?>

<div class="ic-body-solo">

  <div class="ic-page-header">
    <a href="member_dashboard.php" class="ic-page-back">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M7.5 2L3.5 6l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Home
    </a>
    <div class="ic-page-title-solo">Submissions Inbox</div>
  </div>

  <?php if ($ok): ?><div class="ic-alert ic-alert-ok">✓ <?= e($ok) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="ic-alert ic-alert-err">✗ <?= e($err) ?></div><?php endif; ?>

  <!-- Search + Filter bar -->
  <div class="crd-search-bar">
    <input type="text" id="subSearch" placeholder="Search by student or activity…" class="crd-search-input">
    <select id="subCourse" class="crd-search-select">
      <option value="">All Courses</option>
      <?php foreach ($my_courses as $c): ?>
        <option value="<?= e(strtolower($c['code'])) ?>"
          <?= $filter_course == $c['id'] ? 'selected' : '' ?>>
          <?= e($c['code']) ?> – <?= e($c['title']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <select id="subStatus" class="crd-search-select">
      <option value="">All Statuses</option>
      <option value="graded"   <?= $filter_status === 'graded'   ? 'selected' : '' ?>>Graded</option>
      <option value="ungraded" <?= $filter_status === 'ungraded' ? 'selected' : '' ?>>Ungraded</option>
    </select>
    <button onclick="runSubSearch()" class="crd-search-btn">
      <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="6.5" cy="6.5" r="4" stroke="#fff" stroke-width="1.5"/><path d="M10 10l3 3" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
      Search
    </button>
    <span id="subCount" class="crd-search-count"></span>
  </div>

  <?php if (!$my_course_ids): ?>
    <div class="crd-empty">You have no assigned courses. Contact admin to be assigned.</div>
  <?php elseif (!$submissions): ?>
    <div class="crd-empty">No submissions found<?= $filter_status || $filter_course ? ' for these filters' : '' ?>.</div>
  <?php else: ?>
    <div id="subGrid" class="crd-grid-submissions">
      <?php foreach ($submissions as $sub):
        $parts = explode(' ', trim($sub['student_name']));
        $ini   = strtoupper(substr($parts[0],0,1) . substr(end($parts),0,1));
        $isGraded = $sub['score'] !== null;
      ?>
      <div class="sub-card"
           data-search="<?= e(strtolower($sub['student_name'] . ' ' . $sub['activity_title'] . ' ' . $sub['student_no'])) ?>"
           data-course="<?= e(strtolower($sub['course_code'])) ?>"
           data-status="<?= $isGraded ? 'graded' : 'ungraded' ?>">

        <!-- Card header -->
        <div class="sub-card-header">
          <div class="stu-avatar-sm"><?= e($ini) ?></div>
          <div class="sub-card-info">
            <div class="sub-student-name"><?= e($sub['student_name']) ?></div>
            <div class="sub-meta">
              <span><?= e($sub['student_no']) ?></span>
              <span class="ic-tag ic-tag-green" style="font-size:.66rem;"><?= e($sub['course_code']) ?></span>
              <span class="sub-type-badge"><?= ucfirst(e($sub['activity_type'] ?? 'activity')) ?></span>
            </div>
          </div>
          <div class="sub-score-block">
            <?php if ($isGraded): ?>
              <div class="sub-score-val"><?= number_format((float)$sub['score'],2) ?><span class="sub-score-max">/<?= $sub['max_score'] ?></span></div>
              <div class="sub-graded-label">Graded</div>
            <?php else: ?>
              <span class="sub-ungraded-badge">Ungraded</span>
            <?php endif; ?>
          </div>
        </div>

        <!-- Activity title -->
        <div class="sub-activity-title"><?= e($sub['activity_title']) ?></div>

        <!-- File + timestamps -->
        <div class="sub-file-row">
          <?php if (!empty($sub['file_path']) && file_exists(__DIR__ . '/' . $sub['file_path'])): ?>
            <a href="<?= e($sub['file_path']) ?>" target="_blank" class="sub-file-link">
              <svg width="11" height="11" viewBox="0 0 16 16" fill="none"><path d="M3 2h7l3 3v9a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1z" stroke="currentColor" stroke-width="1.5"/></svg>
              <?= e($sub['original_filename'] ?? basename($sub['file_path'])) ?>
            </a>
          <?php endif; ?>
          <span class="sub-time">Submitted <?= date('M j, Y g:i A', strtotime($sub['submitted_at'])) ?></span>
          <?php if ($sub['graded_at']): ?>
            <span class="sub-time">· Graded <?= date('M j, Y', strtotime($sub['graded_at'])) ?></span>
          <?php endif; ?>
        </div>

        <!-- Grade form -->
        <form method="POST" class="sub-grade-form">
          <input type="hidden" name="save_grade"  value="1">
          <input type="hidden" name="student_id"  value="<?= $sub['student_id'] ?>">
          <input type="hidden" name="activity_id" value="<?= $sub['activity_id'] ?>">
          <input type="number" name="score" step="0.01" min="0" max="<?= $sub['max_score'] ?>"
                 value="<?= $isGraded ? htmlspecialchars($sub['score']) : '' ?>"
                 placeholder="Score / <?= $sub['max_score'] ?>"
                 class="sub-grade-input">
          <input type="text" name="remarks"
                 value="<?= e($sub['remarks'] ?? '') ?>"
                 placeholder="Remarks (optional)"
                 class="sub-remarks-input">
          <button type="submit" class="sub-grade-btn">
            <?= $isGraded ? 'Update Grade' : 'Save Grade' ?>
          </button>
        </form>

      </div>
      <?php endforeach; ?>
    </div>
    <div id="subNoResults" class="crd-empty" style="display:none;">😕 No submissions match your search.</div>
  <?php endif; ?>

</div>

<style>
.ic-alert{padding:.65rem 1rem;border-radius:10px;font-size:.82rem;margin-bottom:.6rem;font-weight:600;}
.ic-alert-ok {background:rgba(74,222,128,.12);color:#4ade80;border:1px solid rgba(74,222,128,.2);}
.ic-alert-err{background:rgba(248,113,113,.12);color:#f87171;border:1px solid rgba(248,113,113,.2);}

.crd-search-bar {
  display: flex; align-items: center; gap: .5rem;
  margin-bottom: 1rem; flex-wrap: wrap;
}
.crd-search-input {
  flex: 1; min-width: 160px;
  background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
  color: #fff; border-radius: 8px;
  padding: .45rem .8rem; font-size: .82rem; outline: none;
}
.crd-search-input::placeholder { color: rgba(255,255,255,.3); }
.crd-search-input:focus { border-color: rgba(255,255,255,.3); }
.crd-search-select {
  background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
  color: #fff; border-radius: 8px;
  padding: .45rem .7rem; font-size: .8rem; outline: none; cursor: pointer;
}
.crd-search-select option { background: #1a2030; color: #fff; }
.crd-search-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  background: linear-gradient(135deg,#1e40af,#1d4ed8);
  color: #fff; border: none; border-radius: 8px;
  padding: .45rem .9rem; font-size: .8rem; font-weight: 700;
  cursor: pointer; transition: opacity .15s; white-space: nowrap;
}
.crd-search-btn:hover { opacity: .85; }
.crd-search-count { font-size: .75rem; color: rgba(255,255,255,.35); white-space: nowrap; }

/* Submissions grid */
.crd-grid-submissions {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: .75rem;
}

.sub-card {
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.1);
  border-radius: 14px;
  padding: 1rem;
  display: flex; flex-direction: column; gap: .55rem;
  transition: background .15s;
}
.sub-card:hover { background: rgba(255,255,255,.08); }

.sub-card-header {
  display: flex; align-items: flex-start; gap: .65rem;
}
.stu-avatar-sm {
  width: 38px; height: 38px; border-radius: 50%;
  background: linear-gradient(135deg,#1e3a5f,#2a5298);
  display: flex; align-items: center; justify-content: center;
  font-size: .72rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.sub-card-info { flex: 1; min-width: 0; }
.sub-student-name { font-size: .88rem; font-weight: 700; color: #fff; margin-bottom: .2rem; }
.sub-meta { display: flex; flex-wrap: wrap; gap: .3rem; align-items: center; font-size: .7rem; color: rgba(255,255,255,.4); }
.sub-type-badge {
  background: rgba(255,255,255,.08); border-radius: 4px;
  padding: 1px 6px; font-size: .67rem; color: rgba(255,255,255,.5);
}
.sub-score-block { text-align: right; flex-shrink: 0; }
.sub-score-val { font-size: 1rem; font-weight: 800; color: #4ade80; line-height: 1; }
.sub-score-max { font-size: .68rem; color: rgba(255,255,255,.35); font-weight: 400; }
.sub-graded-label { font-size: .62rem; color: rgba(255,255,255,.3); margin-top: 2px; }
.sub-ungraded-badge {
  background: rgba(245,158,11,.15); color: #f59e0b;
  font-size: .7rem; font-weight: 700; border-radius: 6px;
  padding: 3px 8px; display: inline-block;
}

.sub-activity-title {
  font-size: .82rem; font-weight: 600; color: rgba(255,255,255,.75);
}
.sub-file-row {
  display: flex; flex-wrap: wrap; align-items: center; gap: .5rem;
}
.sub-file-link {
  display: inline-flex; align-items: center; gap: .3rem;
  background: rgba(255,255,255,.07); border-radius: 6px;
  padding: .25rem .6rem; font-size: .72rem; color: rgba(255,255,255,.7);
  text-decoration: none; transition: background .15s;
}
.sub-file-link:hover { background: rgba(255,255,255,.13); }
.sub-time { font-size: .68rem; color: rgba(255,255,255,.3); }

.sub-grade-form {
  display: flex; gap: .4rem; flex-wrap: wrap; align-items: center;
  border-top: 1px solid rgba(255,255,255,.07); padding-top: .55rem;
  margin-top: .1rem;
}
.sub-grade-input {
  width: 110px; background: rgba(255,255,255,.07);
  border: 1px solid rgba(255,255,255,.12);
  color: #fff; border-radius: 8px;
  padding: .35rem .6rem; font-size: .78rem; outline: none;
}
.sub-grade-input:focus { border-color: rgba(255,255,255,.3); }
.sub-remarks-input {
  flex: 1; min-width: 120px;
  background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
  color: #fff; border-radius: 8px;
  padding: .35rem .6rem; font-size: .78rem; outline: none;
}
.sub-remarks-input::placeholder { color: rgba(255,255,255,.28); }
.sub-remarks-input:focus { border-color: rgba(255,255,255,.3); }
.sub-grade-btn {
  background: linear-gradient(135deg,#16a34a,#15803d);
  color: #fff; border: none; border-radius: 8px;
  padding: .35rem .8rem; font-size: .76rem; font-weight: 700;
  cursor: pointer; transition: opacity .15s; white-space: nowrap;
}
.sub-grade-btn:hover { opacity: .85; }

.crd-empty {
  text-align: center; padding: 2.5rem;
  font-size: .83rem; color: rgba(255,255,255,.3);
  background: rgba(255,255,255,.03);
  border: 1px solid rgba(255,255,255,.07);
  border-radius: 14px;
}
</style>

<script>
function runSubSearch() {
  var q  = document.getElementById('subSearch').value.toLowerCase().trim();
  var cv = document.getElementById('subCourse').value.toLowerCase();
  var sv = document.getElementById('subStatus').value.toLowerCase();
  var cards = document.querySelectorAll('#subGrid .sub-card');
  var vis = 0;
  cards.forEach(function(c) {
    var matchQ = !q  || (c.dataset.search  || '').includes(q);
    var matchC = !cv || (c.dataset.course  || '').includes(cv);
    var matchS = !sv || (c.dataset.status  || '') === sv;
    var show = matchQ && matchC && matchS;
    c.style.display = show ? '' : 'none';
    if (show) vis++;
  });
  var cnt = document.getElementById('subCount');
  if (cnt) cnt.textContent = vis + ' of ' + cards.length + ' result' + (cards.length !== 1 ? 's' : '');
  var noRes = document.getElementById('subNoResults');
  if (noRes) noRes.style.display = (vis === 0 && cards.length > 0) ? '' : 'none';
}
document.getElementById('subSearch').addEventListener('keydown', function(e) {
  if (e.key === 'Enter') runSubSearch();
});
runSubSearch();
</script>

<?php icloud_footer(); ?>
