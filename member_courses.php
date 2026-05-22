<?php
require 'config.php';
require 'partials.php';
require_member();

$uid = (int)($_SESSION['user_id'] ?? 0);

$q = $conn->prepare('
    SELECT c.* FROM course_professors cp
    JOIN courses c ON c.id = cp.course_id
    WHERE cp.professor_id = ?
    ORDER BY c.code ASC
');
$q->execute([$uid]);
$courses = $q->fetchAll();

$course_meta = [];
foreach ($courses as $c) {
    $cid = (int)$c['id'];
    $sc = $conn->prepare('SELECT COUNT(*) n FROM enrollments WHERE course_id = ? AND status = "enrolled"');
    $sc->execute([$cid]);
    $students = (int)$sc->fetch()['n'];
    $pc = $conn->query("
        SELECT COUNT(*) n FROM submissions sub
        JOIN activities a ON a.id = sub.activity_id
        LEFT JOIN grades g ON g.student_id = sub.student_id AND g.activity_id = sub.activity_id
        WHERE a.course_id = $cid AND (g.score IS NULL OR g.id IS NULL)
    ")->fetch()['n'];
    $course_meta[$cid] = ['students' => $students, 'pending' => (int)$pc];
}

icloud_header('My Courses');
render_school_banner($conn);
render_left_quick_access('member_courses');
?>

<div class="ic-body-solo">

  <div class="ic-page-header">
    <a href="member_dashboard.php" class="ic-page-back">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M7.5 2L3.5 6l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Home
    </a>
    <div class="ic-page-title-solo">My Courses</div>
  </div>

  <!-- Search bar -->
  <div class="crd-search-bar">
    <input type="text" id="courseSearch" placeholder="Search courses…" class="crd-search-input">
    <button onclick="runCourseSearch()" class="crd-search-btn">
      <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="6.5" cy="6.5" r="4" stroke="#fff" stroke-width="1.5"/><path d="M10 10l3 3" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
      Search
    </button>
    <span id="courseCount" class="crd-search-count"></span>
  </div>

  <?php if ($courses): ?>
  <div id="courseGrid" class="crd-grid">
    <?php foreach ($courses as $c): ?>
    <?php $meta = $course_meta[(int)$c['id']]; ?>
    <div class="crd-card" data-search="<?= e(strtolower($c['title'] . ' ' . $c['code'])) ?>">
      <div class="crd-card-icon crd-icon-blue">
        <svg width="22" height="22" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="#fff" stroke-width="1.5"/><path d="M5 6h6M5 9h4" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
      </div>
      <div class="crd-card-body">
        <div class="crd-card-title"><?= e($c['title']) ?></div>
        <div class="crd-card-meta">
          <span class="ic-tag ic-tag-green"><?= e($c['code']) ?></span>
          <span class="crd-meta-pill"><?= $meta['students'] ?> student<?= $meta['students'] !== 1 ? 's' : '' ?></span>
          <?php if ($meta['pending'] > 0): ?>
            <span class="crd-meta-pill crd-pill-amber"><?= $meta['pending'] ?> ungraded</span>
          <?php endif; ?>
        </div>
      </div>
      <a href="member_view_course.php?id=<?= $c['id'] ?>" class="crd-btn crd-btn-green">Open →</a>
    </div>
    <?php endforeach; ?>
  </div>
  <div id="courseNoResults" class="crd-empty" style="display:none;">😕 No courses match your search.</div>
  <?php else: ?>
  <div class="crd-empty">No courses assigned to you yet.</div>
  <?php endif; ?>

</div>

<style>
/* ── Search bar ── */
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
.crd-search-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  background: linear-gradient(135deg,#1e40af,#1d4ed8);
  color: #fff; border: none; border-radius: 8px;
  padding: .45rem .9rem; font-size: .8rem; font-weight: 700;
  cursor: pointer; transition: opacity .15s; white-space: nowrap;
}
.crd-search-btn:hover { opacity: .85; }
.crd-search-count { font-size: .75rem; color: rgba(255,255,255,.35); white-space: nowrap; }

/* ── Cards grid ── */
.crd-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: .75rem;
}
.crd-card {
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.1);
  border-radius: 14px;
  padding: 1.1rem 1rem;
  display: flex; flex-direction: column; gap: .6rem;
  transition: background .15s, border-color .15s;
}
.crd-card:hover {
  background: rgba(255,255,255,.09);
  border-color: rgba(255,255,255,.2);
}
.crd-card-icon {
  width: 44px; height: 44px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.crd-icon-blue { background: linear-gradient(135deg,#1e3a5f,#2563eb); }
.crd-icon-navy { background: linear-gradient(135deg,#1e3a5f,#2a5298); }
.crd-icon-purple { background: linear-gradient(135deg,#5b21b6,#7c3aed); }
.crd-icon-teal { background: linear-gradient(135deg,#0f766e,#14b8a6); }

.crd-card-body { flex: 1; }
.crd-card-title {
  font-size: .9rem; font-weight: 700; color: #fff;
  margin-bottom: .35rem; line-height: 1.3;
}
.crd-card-meta { display: flex; flex-wrap: wrap; gap: .35rem; align-items: center; }
.crd-meta-pill {
  font-size: .7rem; color: rgba(255,255,255,.5);
  background: rgba(255,255,255,.07);
  border-radius: 5px; padding: 2px 7px;
}
.crd-pill-amber { color: #f59e0b; background: rgba(245,158,11,.12); }
.crd-pill-red   { color: #f87171; background: rgba(248,113,113,.12); }
.crd-pill-green { color: #4ade80; background: rgba(74,222,128,.12); }

/* ── Buttons ── */
.crd-btn {
  display: inline-flex; align-items: center; justify-content: center;
  border: none; border-radius: 8px;
  padding: .38rem .9rem; font-size: .78rem; font-weight: 700;
  cursor: pointer; text-decoration: none; transition: opacity .15s;
  white-space: nowrap; width: 100%;
}
.crd-btn-green { background: linear-gradient(135deg,#16a34a,#15803d); color: #fff; }
.crd-btn-blue  { background: linear-gradient(135deg,#1e40af,#1d4ed8); color: #fff; }
.crd-btn-purple{ background: linear-gradient(135deg,#7c3aed,#5b21b6); color: #fff; }
.crd-btn:hover { opacity: .82; }

.crd-empty {
  text-align: center; padding: 2.5rem;
  font-size: .83rem; color: rgba(255,255,255,.3);
  background: rgba(255,255,255,.03);
  border: 1px solid rgba(255,255,255,.07);
  border-radius: 14px;
}
</style>

<script>
function runCourseSearch() {
  var q = document.getElementById('courseSearch').value.toLowerCase().trim();
  var cards = document.querySelectorAll('#courseGrid .crd-card');
  var vis = 0;
  cards.forEach(function(c) {
    var match = !q || (c.dataset.search || '').includes(q);
    c.style.display = match ? '' : 'none';
    if (match) vis++;
  });
  var cnt = document.getElementById('courseCount');
  if (cnt) cnt.textContent = vis + ' of ' + cards.length + ' course' + (cards.length !== 1 ? 's' : '');
  var noRes = document.getElementById('courseNoResults');
  if (noRes) noRes.style.display = (vis === 0 && cards.length > 0) ? '' : 'none';
}
document.getElementById('courseSearch').addEventListener('keydown', function(e) {
  if (e.key === 'Enter') runCourseSearch();
});
runCourseSearch();
</script>

<?php icloud_footer(); ?>
