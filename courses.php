<?php
require 'config.php';
require 'partials.php';
require 'academic_options.php';
require_admin();

$id   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$edit = null;

/* ── DELETE COURSE ── */
if (isset($_GET['delete'])) {
    $conn->prepare('DELETE FROM courses WHERE id=?')->execute([(int)$_GET['delete']]);
    redirect('courses.php');
}

/* ── ASSIGN PROFESSORS (multi) ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'assign_prof') {
    $cid  = (int)($_POST['course_id'] ?? 0);
    $pids = array_map('intval', (array)($_POST['professor_ids'] ?? []));
    if ($cid && !empty($pids)) {
        $chk = $conn->prepare('SELECT id FROM course_professors WHERE course_id=? AND professor_id=?');
        $ins = $conn->prepare('INSERT INTO course_professors (course_id, professor_id) VALUES (?,?)');
        foreach ($pids as $pid) {
            if ($pid <= 0) continue;
            $chk->execute([$cid, $pid]);
            if (!$chk->fetch()) {
                $ins->execute([$cid, $pid]);
            }
        }
    }
    redirect('courses.php');
}

/* ── UNASSIGN PROFESSOR ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'unassign_prof') {
    $cpid = (int)($_POST['cp_id'] ?? 0);
    if ($cpid) {
        $conn->prepare('DELETE FROM course_professors WHERE id=?')->execute([$cpid]);
    }
    redirect('courses.php');
}

/* ── EDIT / LOAD ── */
if ($id) {
    $s = $conn->prepare('SELECT * FROM courses WHERE id=?');
    $s->execute([$id]);
    $edit = $s->fetch();
}

/* ── SAVE COURSE ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program = $_POST['program'];
    if (!in_array($program, allowed_programs(), true)) {
        flash('success', 'Invalid program.');
        redirect('courses.php');
    }
    $data = [trim($_POST['code']), trim($_POST['title']), $program, trim($_POST['description']), $_POST['color'] ?: '#CDB4DB'];
    if (!empty($_POST['id'])) {
        $data[] = (int)$_POST['id'];
        $conn->prepare('UPDATE courses SET code=?,title=?,program=?,description=?,color=? WHERE id=?')->execute($data);
    } else {
        $conn->prepare('INSERT INTO courses(code,title,program,description,color) VALUES(?,?,?,?,?)')->execute($data);
    }
    redirect('courses.php');
}

$rows = $conn->query('SELECT * FROM courses ORDER BY program,code')->fetchAll();

/* ── FETCH ALL ASSIGNMENTS (keyed by course_id) ── */
$assignments = [];
$all_cp = $conn->query('
    SELECT cp.id AS cp_id, cp.course_id, u.id AS user_id, u.name, u.email, u.company, u.role
    FROM course_professors cp
    JOIN users u ON u.id = cp.professor_id
    ORDER BY u.name
')->fetchAll();
foreach ($all_cp as $row) {
    $assignments[$row['course_id']][] = $row;
}

/* ── ALL AVAILABLE PROFESSORS / MEMBERS (active) ── */
$all_profs = $conn->query("
    SELECT id, name, email, role, company
    FROM users
    WHERE role IN ('member','professor') AND status='active' AND is_active=1
    ORDER BY name
")->fetchAll();

/* ── UNIQUE PROGRAMS FOR FILTER ── */
$programs_in_use = array_unique(array_column($rows, 'program'));
sort($programs_in_use);

page_header('Courses', 'courses');
?>

<section class="panel">
    <a class="btn" href="courses.php?new=1">+ Add Course</a>
</section>

<?php if (isset($_GET['new']) || $edit): ?>
<section class="panel">
    <form method="post">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="form-grid">
            <label>Code<input name="code" value="<?= e($edit['code'] ?? '') ?>"></label>
            <label>Title<input name="title" value="<?= e($edit['title'] ?? '') ?>"></label>
            <label>Program<select name="program"><?= program_options_html($edit['program'] ?? '') ?></select></label>
            <label>Color<input type="color" name="color" value="<?= e($edit['color'] ?? '#CDB4DB') ?>"></label>
            <label>Description<textarea name="description"><?= e($edit['description'] ?? '') ?></textarea></label>
        </div>
        <br>
        <button class="btn">Save</button>
    </form>
</section>
<?php endif; ?>

<?php if (empty($rows)): ?>
<section class="panel">
    <div class="empty-state">
        <div style="font-size:3rem;">📚</div>
        <p>No courses added yet.</p>
    </div>
</section>
<?php else: ?>

<!-- SEARCH + FILTER BAR -->
<div class="search-filter-bar">
    <input type="text" id="sfSearch" class="sf-search" placeholder="Search by title or code…">
    <select id="sfProgram" data-filter-attr="program">
        <option value="">All Programs</option>
        <?php foreach ($programs_in_use as $p): ?>
        <option value="<?= e(strtolower($p)) ?>"><?= e($p) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="sf-reset" id="sfReset">Reset</button>
    <span class="sf-count" id="sfCount"></span>
</div>

<div class="card-grid" id="cardGrid">
<?php foreach ($rows as $r): ?>
<?php $prof_count = count($assignments[$r['id']] ?? []); ?>
<div class="record-card <?= school_card_class($r['program']) ?>"
     data-search="<?= e(strtolower($r['title'] . ' ' . $r['code'] . ' ' . $r['program'])) ?>"
     data-program="<?= e(strtolower($r['program'])) ?>">
    <div class="record-card-header">
        <?php $sch = strtolower(school_for_program($r['program'])); ?>
        <div class="record-card-avatar school-logo">
            <img src="assets/images/logo-<?= e($sch) ?>.png" alt="<?= e(strtoupper($sch)) ?>">
        </div>
        <div>
            <div class="record-card-title"><?= e($r['title']) ?></div>
            <div class="record-card-sub"><?= e($r['code']) ?></div>
        </div>
    </div>
    <div class="record-card-body">
        <div class="record-card-field">
            <label>School</label>
            <span><?= e(school_for_program($r['program'])) ?></span>
        </div>
        <div class="record-card-field">
            <label>Program</label>
            <span><span class="tag"><?= e($r['program']) ?></span></span>
        </div>
        <div class="record-card-field">
            <label>Professors</label>
            <span>
                <?php if ($prof_count > 0): ?>
                    <span class="ap-badge"><?= $prof_count ?> assigned</span>
                <?php else: ?>
                    <span style="color:var(--ink-40);font-size:.78rem;">None assigned</span>
                <?php endif; ?>
            </span>
        </div>
        <?php if (!empty($r['description'])): ?>
        <div class="record-card-field" style="grid-column:1/-1;">
            <label>Description</label>
            <span><?= e($r['description']) ?></span>
        </div>
        <?php endif; ?>
    </div>
    <div class="record-card-actions">
        <button class="btn light ap-manage-btn"
                data-course-id="<?= $r['id'] ?>"
                data-course-title="<?= e($r['title']) ?>"
                data-course-code="<?= e($r['code']) ?>">
            👥 Professors
        </button>
        <a class="btn light" href="courses.php?id=<?= $r['id'] ?>">Edit</a>
        <a class="btn danger" href="courses.php?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this course?')">Delete</a>
    </div>
</div>
<?php endforeach; ?>
<div class="sf-no-results" id="sfNoResults">😕 No courses match your search.</div>
</div>

<!-- ── ASSIGN PROFESSORS MODAL ── -->
<div id="apModal" class="ap-modal-overlay" style="display:none;">
    <div class="ap-modal">
        <div class="ap-modal-header">
            <div>
                <div class="ap-modal-title" id="apModalTitle">Manage Professors</div>
                <div class="ap-modal-sub" id="apModalSub"></div>
            </div>
            <button class="ap-close-btn" id="apCloseBtn">✕</button>
        </div>

        <!-- Currently Assigned -->
        <div class="ap-section-label">Assigned Professors</div>
        <div id="apAssignedList" class="ap-assigned-list">
            <div class="ap-empty-msg">No professors assigned yet.</div>
        </div>

        <!-- Assign New -->
        <div class="ap-section-label" style="margin-top:1.2rem;">Add Professors</div>
        <form method="post" id="apAddForm">
            <input type="hidden" name="action" value="assign_prof">
            <input type="hidden" name="course_id" id="apCourseIdInput">
            <div id="apCheckList" class="ap-checklist"></div>
            <div id="apNoAvail" class="ap-empty-msg" style="display:none;">All professors are already assigned.</div>
            <button type="submit" class="btn ap-assign-btn" style="margin-top:var(--sp-3);width:100%;" id="apAssignBtn">Assign Selected</button>
        </form>
    </div>
</div>

<!-- Hidden remove form (reused) -->
<form method="post" id="apRemoveForm" style="display:none;">
    <input type="hidden" name="action" value="unassign_prof">
    <input type="hidden" name="cp_id" id="apRemoveCpId">
</form>

<?php
// Embed data as JSON for JS
$assignments_json = json_encode($assignments, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
$all_profs_json   = json_encode($all_profs,   JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
?>

<script>
(function () {
    /* ── Search/filter ── */
    var search  = document.getElementById('sfSearch');
    var selects = document.querySelectorAll('[data-filter-attr]');
    var cards   = document.querySelectorAll('#cardGrid .record-card');
    var count   = document.getElementById('sfCount');
    var noRes   = document.getElementById('sfNoResults');
    var reset   = document.getElementById('sfReset');

    function run() {
        var q = search.value.toLowerCase().trim();
        var filters = {};
        selects.forEach(function (s) { filters[s.dataset.filterAttr] = s.value.toLowerCase(); });
        var vis = 0;
        cards.forEach(function (c) {
            var text = c.dataset.search || '';
            var show = !q || text.includes(q);
            if (show) {
                for (var attr in filters) {
                    if (filters[attr] && (c.dataset[attr] || '') !== filters[attr]) { show = false; break; }
                }
            }
            c.style.display = show ? '' : 'none';
            if (show) vis++;
        });
        count.textContent = vis + ' of ' + cards.length + ' record' + (cards.length !== 1 ? 's' : '');
        noRes.classList.toggle('visible', vis === 0);
    }
    search.addEventListener('input', run);
    selects.forEach(function (s) { s.addEventListener('change', run); });
    reset.addEventListener('click', function () { search.value = ''; selects.forEach(function (s) { s.value = ''; }); run(); });
    run();

    /* ── Modal data ── */
    var assignments = <?= $assignments_json ?>;
    var allProfs    = <?= $all_profs_json ?>;

    var modal        = document.getElementById('apModal');
    var modalTitle   = document.getElementById('apModalTitle');
    var modalSub     = document.getElementById('apModalSub');
    var assignedList = document.getElementById('apAssignedList');
    var courseInput  = document.getElementById('apCourseIdInput');
    var removeForm   = document.getElementById('apRemoveForm');
    var removeCpId   = document.getElementById('apRemoveCpId');

    function getInitials(name) {
        return name.split(' ').slice(0,2).map(function(n){ return n[0]; }).join('').toUpperCase();
    }

    function openModal(courseId, courseTitle, courseCode) {
        modalTitle.textContent = courseTitle;
        modalSub.textContent   = courseCode;
        courseInput.value      = courseId;

        /* Build assigned list */
        var assigned = assignments[courseId] || [];
        var assignedIds = assigned.map(function(a){ return a.user_id; });

        if (assigned.length === 0) {
            assignedList.innerHTML = '<div class="ap-empty-msg">No professors assigned yet.</div>';
        } else {
            assignedList.innerHTML = assigned.map(function(a) {
                return '<div class="ap-assigned-row">' +
                    '<div class="ap-prof-avatar">' + getInitials(a.name) + '</div>' +
                    '<div class="ap-prof-info">' +
                        '<div class="ap-prof-name">' + escHtml(a.name) + '</div>' +
                        '<div class="ap-prof-meta">' + escHtml(a.email) + ' &bull; <span class="ap-role-tag">' + escHtml(a.role) + '</span></div>' +
                    '</div>' +
                    '<button class="ap-remove-btn" data-cpid="' + a.cp_id + '" title="Remove">✕</button>' +
                '</div>';
            }).join('');
            assignedList.querySelectorAll('.ap-remove-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (confirm('Remove this professor from the course?')) {
                        removeCpId.value = this.dataset.cpid;
                        removeForm.submit();
                    }
                });
            });
        }

        /* Build checkbox list — only show unassigned profs */
        var checkList  = document.getElementById('apCheckList');
        var noAvail    = document.getElementById('apNoAvail');
        var assignBtn  = document.getElementById('apAssignBtn');
        var unassigned = allProfs.filter(function(p){ return assignedIds.indexOf(parseInt(p.id)) === -1; });

        if (unassigned.length === 0) {
            checkList.innerHTML = '';
            noAvail.style.display = '';
            assignBtn.style.display = 'none';
        } else {
            noAvail.style.display = 'none';
            assignBtn.style.display = '';
            checkList.innerHTML = unassigned.map(function(p) {
                var meta = p.role + (p.company ? ' &bull; ' + escHtml(p.company) : '');
                return '<label class="ap-check-row">' +
                    '<input type="checkbox" name="professor_ids[]" value="' + p.id + '" class="ap-checkbox">' +
                    '<div class="ap-prof-avatar">' + getInitials(p.name) + '</div>' +
                    '<div class="ap-prof-info">' +
                        '<div class="ap-prof-name">' + escHtml(p.name) + '</div>' +
                        '<div class="ap-prof-meta">' + escHtml(p.email) + ' &bull; <span class="ap-role-tag">' + meta + '</span></div>' +
                    '</div>' +
                '</label>';
            }).join('');
        }

        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* Open buttons */
    document.querySelectorAll('.ap-manage-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            openModal(this.dataset.courseId, this.dataset.courseTitle, this.dataset.courseCode);
        });
    });

    /* Close */
    document.getElementById('apCloseBtn').addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });
})();
</script>

<style>
/* ── Professors badge on card ── */
.ap-badge {
  display: inline-block;
  background: rgba(26,39,68,.12);
  color: var(--navy);
  border: 1px solid rgba(26,39,68,.18);
  border-radius: var(--r-full);
  font-size: .72rem;
  font-weight: 700;
  padding: 2px 9px;
}

/* ── Modal overlay ── */
.ap-modal-overlay {
  position: fixed; inset: 0; z-index: 1000;
  background: rgba(10,14,30,.55);
  backdrop-filter: blur(4px);
  align-items: center; justify-content: center;
}
.ap-modal {
  background: #fff;
  border-radius: var(--r-xl);
  box-shadow: 0 24px 64px rgba(0,0,0,.2);
  width: 100%; max-width: 520px;
  max-height: 88vh; overflow-y: auto;
  padding: var(--sp-6);
  animation: fadeUp .25s both;
}
.ap-modal-header {
  display: flex; align-items: flex-start;
  justify-content: space-between; gap: var(--sp-3);
  margin-bottom: var(--sp-5);
}
.ap-modal-title {
  font-family: var(--font-display);
  font-size: 1.15rem; font-weight: 700; color: var(--ink);
  line-height: 1.2;
}
.ap-modal-sub {
  font-size: .78rem; color: var(--ink-40); margin-top: 3px;
}
.ap-close-btn {
  background: var(--canvas); border: 1px solid var(--border);
  border-radius: var(--r-full); width: 32px; height: 32px;
  display: grid; place-items: center; cursor: pointer;
  font-size: .8rem; color: var(--ink-40); flex-shrink: 0;
  transition: background var(--t-fast);
}
.ap-close-btn:hover { background: var(--border); color: var(--ink); }

/* ── Section labels ── */
.ap-section-label {
  font-size: .7rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .1em; color: var(--ink-40); margin-bottom: var(--sp-2);
}

/* ── Assigned list ── */
.ap-assigned-list {
  display: flex; flex-direction: column; gap: var(--sp-2);
  min-height: 48px;
}
.ap-empty-msg {
  font-size: .82rem; color: var(--ink-40);
  padding: var(--sp-3) 0; font-style: italic;
}
.ap-assigned-row {
  display: flex; align-items: center; gap: var(--sp-3);
  padding: var(--sp-3) var(--sp-4);
  background: var(--canvas-lt); border: 1px solid var(--border);
  border-radius: var(--r-md);
}
.ap-prof-avatar {
  width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg, var(--navy), var(--navy-lt));
  display: grid; place-items: center;
  font-size: .72rem; font-weight: 700; color: var(--gold);
}
.ap-prof-info { flex: 1; min-width: 0; }
.ap-prof-name { font-size: .88rem; font-weight: 600; color: var(--ink); }
.ap-prof-meta { font-size: .72rem; color: var(--ink-40); margin-top: 1px; }
.ap-role-tag {
  display: inline-block; font-size: .65rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: .05em;
  background: rgba(26,39,68,.1); color: var(--navy);
  border-radius: var(--r-full); padding: 1px 6px;
}
.ap-remove-btn {
  background: none; border: 1px solid var(--border); border-radius: var(--r-full);
  width: 26px; height: 26px; display: grid; place-items: center;
  cursor: pointer; font-size: .7rem; color: var(--ink-40); flex-shrink: 0;
  transition: background var(--t-fast), color var(--t-fast), border-color var(--t-fast);
}
.ap-remove-btn:hover { background: var(--danger); border-color: var(--danger); color: #fff; }

/* ── Checklist ── */
.ap-checklist {
  display: flex; flex-direction: column; gap: 6px;
  max-height: 220px; overflow-y: auto;
  padding-right: 4px;
}
.ap-checklist::-webkit-scrollbar { width: 4px; }
.ap-checklist::-webkit-scrollbar-thumb { background: var(--border-md); border-radius: 99px; }
.ap-check-row {
  display: flex; align-items: center; gap: var(--sp-3);
  padding: var(--sp-2) var(--sp-3);
  border: 1.5px solid var(--border);
  border-radius: var(--r-md);
  cursor: pointer;
  transition: border-color var(--t-fast), background var(--t-fast);
  user-select: none;
}
.ap-check-row:hover { background: var(--canvas-lt); border-color: var(--border-md); }
.ap-check-row:has(.ap-checkbox:checked) {
  border-color: var(--navy);
  background: rgba(26,39,68,.06);
}
.ap-checkbox {
  width: 16px; height: 16px; flex-shrink: 0;
  accent-color: var(--navy); cursor: pointer;
}
</style>

<?php endif; ?>

<?php page_footer(); ?>
