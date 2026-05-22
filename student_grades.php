<?php
require 'config.php';
require 'partials.php';
require 'student_common.php';

$stu = require_student_record($conn);

$q = $conn->prepare("
    SELECT
        c.code AS course_code,
        a.id AS activity_id,
        a.title AS activity_title,
        a.description AS activity_description,
        a.type AS activity_type,
        a.due_date,
        a.max_score,
        g.score,
        g.remarks,
        g.graded_at,
        l.title AS lesson_title
    FROM grades g
    JOIN activities a ON a.id = g.activity_id
    JOIN courses c ON c.id = a.course_id
    LEFT JOIN lessons l ON l.id = a.lesson_id
    WHERE g.student_id = ?
    ORDER BY g.created_at DESC
");
$q->execute([$stu['id']]);
$grades = $q->fetchAll();

icloud_header('My Grades');
?>
<?php render_school_banner($conn);
render_left_quick_access('student_grades'); ?>

<div class="ic-body-solo">

  <div class="ic-page-header">
    <a href="student_dashboard.php" class="ic-page-back">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M7.5 2L3.5 6l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      My Home
    </a>
    <div class="ic-page-title-solo">My Grades</div>
  </div>

  <!-- Search bar -->
  <div class="crd-search-bar">
    <input type="text" id="gradeSearch" placeholder="Search by activity or course…" class="crd-search-input">
    <select id="gradeFilter" class="crd-search-select">
      <option value="">All</option>
      <option value="graded">Graded</option>
      <option value="ungraded">Ungraded</option>
    </select>
    <button onclick="runGradeSearch()" class="crd-search-btn">
      <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="6.5" cy="6.5" r="4" stroke="#fff" stroke-width="1.5"/><path d="M10 10l3 3" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
      Search
    </button>
    <span id="gradeCount" class="crd-search-count"></span>
  </div>

  <?php if ($grades): ?>
  <div id="gradeGrid" class="crd-grid">
    <?php foreach ($grades as $i => $g):
      $hasScore = $g['score'] !== null;
      $pct  = ($hasScore && $g['max_score'] > 0) ? ($g['score'] / $g['max_score'] * 100) : 0;
      $cls  = !$hasScore ? 'crd-pill-ungraded' : ($pct >= 85 ? 'crd-pill-green' : ($pct >= 70 ? 'crd-pill-amber' : 'crd-pill-red'));
      $type_icon = match($g['activity_type'] ?? '') {
        'quiz'       => '📝',
        'exam'       => '📋',
        'assignment' => '📌',
        'project'    => '🗂️',
        default      => '📄',
      };
      $due    = $g['due_date']   ? date('M d, Y', strtotime($g['due_date']))   : '—';
      $graded = $g['graded_at']  ? date('M d, Y', strtotime($g['graded_at'])) : null;
      $detail_id = 'gd-' . $i;
    ?>
    <div class="grade-card"
         data-search="<?= e(strtolower($g['activity_title'] . ' ' . $g['course_code'])) ?>"
         data-status="<?= $hasScore ? 'graded' : 'ungraded' ?>">

      <!-- Card header -->
      <div class="grade-card-top">
        <div class="grade-type-icon"><?= $type_icon ?></div>
        <div class="grade-card-info">
          <div class="grade-card-title"><?= e($g['activity_title']) ?></div>
          <div class="grade-card-meta">
            <span class="ic-tag ic-tag-blue" style="font-size:.67rem;"><?= e($g['course_code']) ?></span>
            <span class="grade-type-badge"><?= ucfirst($g['activity_type'] ?? 'activity') ?></span>
          </div>
        </div>
        <div class="grade-score-block <?= $cls ?>">
          <?php if ($hasScore): ?>
            <div class="grade-score-val"><?= e($g['score']) ?><span class="grade-score-max">/<?= e($g['max_score']) ?></span></div>
            <?php if ($g['max_score'] > 0): ?>
              <div class="grade-pct"><?= number_format($pct, 1) ?>%</div>
            <?php endif; ?>
          <?php else: ?>
            <div class="grade-ungraded">—/<?= e($g['max_score']) ?></div>
            <div class="grade-pct-label">Pending</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Expandable detail -->
      <div class="grade-detail" id="<?= $detail_id ?>" style="display:none;">
        <div class="grade-detail-grid">
          <div class="grade-detail-item">
            <span class="grade-detail-label">Due Date</span>
            <span class="grade-detail-val"><?= $due ?></span>
          </div>
          <?php if ($graded): ?>
          <div class="grade-detail-item">
            <span class="grade-detail-label">Graded On</span>
            <span class="grade-detail-val"><?= $graded ?></span>
          </div>
          <?php endif; ?>
          <?php if ($g['lesson_title']): ?>
          <div class="grade-detail-item">
            <span class="grade-detail-label">Lesson</span>
            <span class="grade-detail-val"><?= e($g['lesson_title']) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($g['remarks']): ?>
          <div class="grade-detail-item grade-detail-full">
            <span class="grade-detail-label">Remarks</span>
            <span class="grade-detail-val"><?= e($g['remarks']) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($g['activity_description']): ?>
          <div class="grade-detail-item grade-detail-full">
            <span class="grade-detail-label">Description</span>
            <span class="grade-detail-val" style="color:rgba(255,255,255,.6);line-height:1.4;"><?= e($g['activity_description']) ?></span>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Toggle button -->
      <button class="grade-toggle-btn" onclick="toggleDetail('<?= $detail_id ?>', this)">
        <span class="grade-toggle-label">Details</span>
        <svg class="grade-chevron" width="13" height="13" viewBox="0 0 12 12" fill="none">
          <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>

    </div>
    <?php endforeach; ?>
  </div>
  <div id="gradeNoResults" class="crd-empty" style="display:none;">😕 No grades match your search.</div>
  <?php else: ?>
  <div class="crd-empty">No grades recorded yet.</div>
  <?php endif; ?>

</div>

<style>
.crd-search-bar{display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap;}
.crd-search-input{flex:1;min-width:160px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff;border-radius:8px;padding:.45rem .8rem;font-size:.82rem;outline:none;}
.crd-search-input::placeholder{color:rgba(255,255,255,.3);}
.crd-search-input:focus{border-color:rgba(255,255,255,.3);}
.crd-search-select{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff;border-radius:8px;padding:.45rem .7rem;font-size:.8rem;outline:none;cursor:pointer;}
.crd-search-select option{background:#1a2030;color:#fff;}
.crd-search-btn{display:inline-flex;align-items:center;gap:.35rem;background:linear-gradient(135deg,#1e40af,#1d4ed8);color:#fff;border:none;border-radius:8px;padding:.45rem .9rem;font-size:.8rem;font-weight:700;cursor:pointer;transition:opacity .15s;white-space:nowrap;}
.crd-search-btn:hover{opacity:.85;}
.crd-search-count{font-size:.75rem;color:rgba(255,255,255,.35);white-space:nowrap;}

.crd-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:.75rem;}

/* Grade card */
.grade-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:14px;padding:1rem;display:flex;flex-direction:column;gap:.55rem;transition:background .15s;}
.grade-card:hover{background:rgba(255,255,255,.08);}

.grade-card-top{display:flex;align-items:flex-start;gap:.65rem;}
.grade-type-icon{font-size:1.35rem;flex-shrink:0;margin-top:2px;}
.grade-card-info{flex:1;min-width:0;}
.grade-card-title{font-size:.88rem;font-weight:700;color:#fff;margin-bottom:.25rem;line-height:1.3;}
.grade-card-meta{display:flex;flex-wrap:wrap;gap:.3rem;align-items:center;}
.grade-type-badge{font-size:.68rem;background:rgba(255,255,255,.08);color:rgba(255,255,255,.5);border-radius:4px;padding:1px 6px;}

/* Score block */
.grade-score-block{text-align:right;flex-shrink:0;}
.grade-score-val{font-size:1rem;font-weight:800;line-height:1;}
.grade-score-max{font-size:.67rem;font-weight:400;color:rgba(255,255,255,.35);}
.grade-pct{font-size:.68rem;margin-top:2px;}
.grade-ungraded{font-size:.88rem;font-weight:700;}
.grade-pct-label{font-size:.65rem;margin-top:2px;}

.crd-pill-green .grade-score-val,.crd-pill-green .grade-pct{color:#4ade80;}
.crd-pill-amber .grade-score-val,.crd-pill-amber .grade-pct{color:#f59e0b;}
.crd-pill-red   .grade-score-val,.crd-pill-red   .grade-pct{color:#f87171;}
.crd-pill-ungraded .grade-ungraded,.crd-pill-ungraded .grade-pct-label{color:rgba(255,255,255,.35);}

/* Detail section */
.grade-detail{border-top:1px solid rgba(255,255,255,.07);padding-top:.55rem;animation:slideDown .2s ease;}
@keyframes slideDown{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:translateY(0)}}
.grade-detail-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.5rem .8rem;}
.grade-detail-full{grid-column:1/-1;}
.grade-detail-item{display:flex;flex-direction:column;gap:2px;}
.grade-detail-label{font-size:.65rem;text-transform:uppercase;letter-spacing:.05em;color:rgba(255,255,255,.38);font-weight:600;}
.grade-detail-val{font-size:.78rem;color:rgba(255,255,255,.8);}

/* Toggle button */
.grade-toggle-btn{display:flex;align-items:center;justify-content:center;gap:.3rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.09);border-radius:7px;padding:.3rem .7rem;font-size:.73rem;color:rgba(255,255,255,.5);cursor:pointer;transition:background .15s;width:100%;}
.grade-toggle-btn:hover{background:rgba(255,255,255,.1);}
.grade-chevron{transition:transform .22s ease;}
.grade-chevron.open{transform:rotate(180deg);}

.crd-empty{text-align:center;padding:2.5rem;font-size:.83rem;color:rgba(255,255,255,.3);background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:14px;}
</style>

<script>
function toggleDetail(id, btn) {
  var el = document.getElementById(id);
  var chevron = btn.querySelector('.grade-chevron');
  var isOpen = el.style.display !== 'none';
  el.style.display = isOpen ? 'none' : 'block';
  chevron.classList.toggle('open', !isOpen);
  btn.querySelector('.grade-toggle-label').textContent = isOpen ? 'Details' : 'Hide';
}
function runGradeSearch() {
  var q  = document.getElementById('gradeSearch').value.toLowerCase().trim();
  var fv = document.getElementById('gradeFilter').value;
  var cards = document.querySelectorAll('#gradeGrid .grade-card');
  var vis = 0;
  cards.forEach(function(c) {
    var matchQ = !q  || (c.dataset.search || '').includes(q);
    var matchF = !fv || (c.dataset.status || '') === fv;
    var show = matchQ && matchF;
    c.style.display = show ? '' : 'none';
    if (show) vis++;
  });
  var cnt = document.getElementById('gradeCount');
  if (cnt) cnt.textContent = vis + ' of ' + cards.length + ' grade' + (cards.length !== 1 ? 's' : '');
  var noRes = document.getElementById('gradeNoResults');
  if (noRes) noRes.style.display = (vis === 0 && cards.length > 0) ? '' : 'none';
}
document.getElementById('gradeSearch').addEventListener('keydown', function(e){ if(e.key==='Enter') runGradeSearch(); });
runGradeSearch();
</script>

<?php icloud_footer(); ?>
