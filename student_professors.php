<?php
require 'config.php';
require 'partials.php';
require 'student_common.php';

$stu = require_student_record($conn);

$q = $conn->prepare('
    SELECT DISTINCT
        u.name AS full_name,
        u.email,
        c.code
    FROM enrollments e
    JOIN courses c ON c.id = e.course_id
    JOIN course_professors cp ON cp.course_id = c.id
    JOIN professors p ON p.id = cp.professor_id
    JOIN users u ON u.id = p.user_id
    WHERE e.student_id = ?
');
$q->execute([$stu['id']]);
$profs = $q->fetchAll();

icloud_header('My Professors');
?>
<?php render_school_banner($conn);
render_left_quick_access('student_professors'); ?>

<div class="ic-body-solo">

  <div class="ic-page-header">
    <a href="student_dashboard.php" class="ic-page-back">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M7.5 2L3.5 6l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      My Home
    </a>
    <div class="ic-page-title-solo">My Professors</div>
  </div>

  <!-- Search bar -->
  <div class="crd-search-bar">
    <input type="text" id="profSearch" placeholder="Search by name or course…" class="crd-search-input">
    <button onclick="runProfSearch()" class="crd-search-btn">
      <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="6.5" cy="6.5" r="4" stroke="#fff" stroke-width="1.5"/><path d="M10 10l3 3" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
      Search
    </button>
    <span id="profCount" class="crd-search-count"></span>
  </div>

  <?php if ($profs): ?>
  <div id="profGrid" class="crd-grid">
    <?php foreach ($profs as $p):
      $parts = explode(' ', trim($p['full_name']));
      $ini   = strtoupper(substr($parts[0],0,1) . substr(end($parts),0,1));
    ?>
    <div class="crd-card prof-card" data-search="<?= e(strtolower($p['full_name'] . ' ' . $p['code'])) ?>">
      <!-- Avatar -->
      <div class="prof-avatar"><?= e($ini) ?></div>
      <!-- Info -->
      <div class="crd-card-body">
        <div class="crd-card-title"><?= e($p['full_name']) ?></div>
        <div class="crd-card-meta" style="margin-bottom:.3rem;">
          <span class="ic-tag ic-tag-blue" style="font-size:.68rem;"><?= e($p['code']) ?></span>
        </div>
        <div style="font-size:.72rem;color:rgba(255,255,255,.4);"><?= e($p['email']) ?></div>
      </div>
      <!-- Email button -->
      <a href="mailto:<?= e($p['email']) ?>" class="crd-btn crd-btn-navy">
        <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><rect x="2" y="3" width="12" height="10" rx="1.5" stroke="#fff" stroke-width="1.4"/><path d="M2 5l6 4 6-4" stroke="#fff" stroke-width="1.4" stroke-linecap="round"/></svg>
        Email
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <div id="profNoResults" class="crd-empty" style="display:none;">😕 No professors match your search.</div>
  <?php else: ?>
  <div class="crd-empty">No professors assigned yet.</div>
  <?php endif; ?>

</div>

<style>
.crd-search-bar{display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap;}
.crd-search-input{flex:1;min-width:180px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff;border-radius:8px;padding:.45rem .8rem;font-size:.82rem;outline:none;}
.crd-search-input::placeholder{color:rgba(255,255,255,.3);}
.crd-search-input:focus{border-color:rgba(255,255,255,.3);}
.crd-search-btn{display:inline-flex;align-items:center;gap:.35rem;background:linear-gradient(135deg,#1e40af,#1d4ed8);color:#fff;border:none;border-radius:8px;padding:.45rem .9rem;font-size:.8rem;font-weight:700;cursor:pointer;transition:opacity .15s;white-space:nowrap;}
.crd-search-btn:hover{opacity:.85;}
.crd-search-count{font-size:.75rem;color:rgba(255,255,255,.35);white-space:nowrap;}

.crd-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.75rem;}
.crd-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:14px;padding:1.1rem 1rem;display:flex;flex-direction:column;gap:.6rem;transition:background .15s,border-color .15s;}
.crd-card:hover{background:rgba(255,255,255,.09);border-color:rgba(255,255,255,.2);}
.crd-card-body{flex:1;}
.crd-card-title{font-size:.88rem;font-weight:700;color:#fff;margin-bottom:.3rem;}
.crd-card-meta{display:flex;flex-wrap:wrap;gap:.3rem;align-items:center;}
.crd-btn{display:inline-flex;align-items:center;justify-content:center;gap:.35rem;border:none;border-radius:8px;padding:.38rem .9rem;font-size:.77rem;font-weight:700;cursor:pointer;text-decoration:none;transition:opacity .15s;white-space:nowrap;width:100%;}
.crd-btn-navy{background:linear-gradient(135deg,#1e3a5f,#2a5298);color:#fff;}
.crd-btn:hover{opacity:.82;}

.prof-avatar{width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#1e3a5f,#2a5298);display:flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:700;color:#fff;align-self:center;}

.crd-empty{text-align:center;padding:2.5rem;font-size:.83rem;color:rgba(255,255,255,.3);background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:14px;}
</style>

<script>
function runProfSearch() {
  var q = document.getElementById('profSearch').value.toLowerCase().trim();
  var cards = document.querySelectorAll('#profGrid .prof-card');
  var vis = 0;
  cards.forEach(function(c) {
    var match = !q || (c.dataset.search || '').includes(q);
    c.style.display = match ? '' : 'none';
    if (match) vis++;
  });
  var cnt = document.getElementById('profCount');
  if (cnt) cnt.textContent = vis + ' of ' + cards.length + ' professor' + (cards.length !== 1 ? 's' : '');
  var noRes = document.getElementById('profNoResults');
  if (noRes) noRes.style.display = (vis === 0 && cards.length > 0) ? '' : 'none';
}
document.getElementById('profSearch').addEventListener('keydown', function(e){ if(e.key==='Enter') runProfSearch(); });
runProfSearch();
</script>

<?php icloud_footer(); ?>
