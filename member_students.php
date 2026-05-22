<?php
require 'config.php';
require 'partials.php';
require_member();

$uid = (int)($_SESSION['user_id'] ?? 0);

$q = $conn->prepare('
    SELECT c.id, c.code, c.title FROM course_professors cp
    JOIN courses c ON c.id = cp.course_id
    WHERE cp.professor_id = ?
    ORDER BY c.code ASC
');
$q->execute([$uid]);
$my_courses = $q->fetchAll();

$students = [];
$total    = 0;

if ($my_courses) {
    $course_ids = implode(',', array_column($my_courses, 'id'));
    $stq = $conn->query("
        SELECT
            s.id, s.full_name, s.student_no, s.program, s.year_level,
            GROUP_CONCAT(DISTINCT c.code ORDER BY c.code SEPARATOR ', ') AS course_codes,
            COUNT(DISTINCT sub.id)  AS submission_count,
            AVG(CASE WHEN g.score IS NOT NULL AND a.max_score > 0
                THEN g.score / a.max_score * 100 END) AS avg_score
        FROM enrollments e
        JOIN students s ON s.id = e.student_id
        JOIN courses c ON c.id = e.course_id
        LEFT JOIN submissions sub ON sub.student_id = s.id
        LEFT JOIN activities a ON a.id = sub.activity_id AND a.course_id = e.course_id
        LEFT JOIN grades g ON g.student_id = s.id AND g.activity_id = a.id
        WHERE e.course_id IN ($course_ids) AND e.status = 'enrolled'
        GROUP BY s.id
        ORDER BY s.full_name ASC
    ");
    $students = $stq->fetchAll();
    $total    = count($students);
}

icloud_header('My Students');
render_school_banner($conn);
render_left_quick_access('member_students');
?>

<div class="ic-body-solo">

  <div class="ic-page-header">
    <a href="member_dashboard.php" class="ic-page-back">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M7.5 2L3.5 6l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Home
    </a>
    <div class="ic-page-title-solo">My Students</div>
  </div>

  <!-- Search + Filter bar -->
  <div class="crd-search-bar">
    <input type="text" id="stuSearch" placeholder="Search by name or student no…" class="crd-search-input">
    <select id="stuCourse" class="crd-search-select">
      <option value="">All Courses</option>
      <?php foreach ($my_courses as $mc): ?>
      <option value="<?= e($mc['code']) ?>"><?= e($mc['code']) ?> — <?= e($mc['title']) ?></option>
      <?php endforeach; ?>
    </select>
    <button onclick="runStuSearch()" class="crd-search-btn">
      <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="6.5" cy="6.5" r="4" stroke="#fff" stroke-width="1.5"/><path d="M10 10l3 3" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
      Search
    </button>
    <span id="stuCount" class="crd-search-count"></span>
  </div>

  <?php if ($students): ?>
  <div id="stuGrid" class="crd-grid">
    <?php foreach ($students as $stu):
      $parts = explode(' ', trim($stu['full_name']));
      $ini   = strtoupper(substr($parts[0],0,1) . substr(end($parts),0,1));
      $avg   = $stu['avg_score'] !== null ? round((float)$stu['avg_score']) : null;
      $avgcls = $avg === null ? '' : ($avg >= 85 ? 'crd-pill-green' : ($avg >= 70 ? 'crd-pill-amber' : 'crd-pill-red'));
    ?>
    <div class="crd-card stu-card"
         data-search="<?= e(strtolower($stu['full_name'] . ' ' . $stu['student_no'])) ?>"
         data-courses="<?= e(strtolower($stu['course_codes'])) ?>">
      <!-- Avatar -->
      <div class="stu-avatar"><?= e($ini) ?></div>
      <!-- Info -->
      <div class="crd-card-body">
        <div class="crd-card-title"><?= e($stu['full_name']) ?></div>
        <div class="crd-card-meta" style="margin-bottom:.3rem;">
          <?php if ($stu['student_no']): ?>
            <span class="crd-meta-pill"><?= e($stu['student_no']) ?></span>
          <?php endif; ?>
          <?php if ($stu['program']): ?>
            <span class="crd-meta-pill"><?= e($stu['program']) ?></span>
          <?php endif; ?>
        </div>
        <div class="crd-card-meta">
          <?php foreach (explode(', ', $stu['course_codes']) as $cc): ?>
            <span class="ic-tag ic-tag-green" style="font-size:.68rem;"><?= e(trim($cc)) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <!-- Stats -->
      <div class="stu-stats">
        <span class="crd-meta-pill"><?= (int)$stu['submission_count'] ?> sub<?= $stu['submission_count'] != 1 ? 's' : '' ?></span>
        <?php if ($avg !== null): ?>
          <span class="crd-meta-pill <?= $avgcls ?>"><?= $avg ?>%</span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div id="stuNoResults" class="crd-empty" style="display:none;">😕 No students match your search.</div>
  <?php else: ?>
  <div class="crd-empty">No students enrolled in your courses yet.</div>
  <?php endif; ?>

</div>

<style>
.crd-search-bar {
  display: flex; align-items: center; gap: .5rem;
  margin-bottom: 1rem; flex-wrap: wrap;
}
.crd-search-input {
  flex: 1; min-width: 180px;
  background: rgba(255,255,255,.07);
  border: 1px solid rgba(255,255,255,.12);
  color: #fff; border-radius: 8px;
  padding: .45rem .8rem; font-size: .82rem; outline: none;
}
.crd-search-input::placeholder { color: rgba(255,255,255,.3); }
.crd-search-input:focus { border-color: rgba(255,255,255,.3); }
.crd-search-select {
  background: rgba(255,255,255,.07);
  border: 1px solid rgba(255,255,255,.12);
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

.crd-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: .75rem;
}
.crd-card {
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.1);
  border-radius: 14px;
  padding: 1rem;
  display: flex; flex-direction: column; gap: .55rem;
  transition: background .15s, border-color .15s;
}
.crd-card:hover { background: rgba(255,255,255,.09); border-color: rgba(255,255,255,.2); }
.crd-card-body { flex: 1; }
.crd-card-title { font-size: .88rem; font-weight: 700; color: #fff; margin-bottom: .3rem; }
.crd-card-meta { display: flex; flex-wrap: wrap; gap: .3rem; align-items: center; }
.crd-meta-pill {
  font-size: .7rem; color: rgba(255,255,255,.5);
  background: rgba(255,255,255,.07);
  border-radius: 5px; padding: 2px 7px;
}
.crd-pill-amber { color: #f59e0b !important; background: rgba(245,158,11,.12) !important; }
.crd-pill-red   { color: #f87171 !important; background: rgba(248,113,113,.12) !important; }
.crd-pill-green { color: #4ade80 !important; background: rgba(74,222,128,.12) !important; }

.stu-avatar {
  width: 46px; height: 46px; border-radius: 50%;
  background: linear-gradient(135deg,#1e3a5f,#2a5298);
  display: flex; align-items: center; justify-content: center;
  font-size: .8rem; font-weight: 700; color: #fff; flex-shrink: 0;
  align-self: center;
}
.stu-stats { display: flex; gap: .35rem; flex-wrap: wrap; }

.crd-empty {
  text-align: center; padding: 2.5rem;
  font-size: .83rem; color: rgba(255,255,255,.3);
  background: rgba(255,255,255,.03);
  border: 1px solid rgba(255,255,255,.07);
  border-radius: 14px;
}
</style>

<script>
function runStuSearch() {
  var q  = document.getElementById('stuSearch').value.toLowerCase().trim();
  var cv = document.getElementById('stuCourse').value.toLowerCase();
  var cards = document.querySelectorAll('#stuGrid .stu-card');
  var vis = 0;
  cards.forEach(function(c) {
    var matchQ = !q  || (c.dataset.search  || '').includes(q);
    var matchC = !cv || (c.dataset.courses || '').includes(cv);
    var show = matchQ && matchC;
    c.style.display = show ? '' : 'none';
    if (show) vis++;
  });
  var cnt = document.getElementById('stuCount');
  if (cnt) cnt.textContent = vis + ' of ' + cards.length + ' student' + (cards.length !== 1 ? 's' : '');
  var noRes = document.getElementById('stuNoResults');
  if (noRes) noRes.style.display = (vis === 0 && cards.length > 0) ? '' : 'none';
}
document.getElementById('stuSearch').addEventListener('keydown', function(e) {
  if (e.key === 'Enter') runStuSearch();
});
runStuSearch();
</script>

<?php icloud_footer(); ?>
