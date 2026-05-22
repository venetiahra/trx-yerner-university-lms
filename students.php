<?php
require 'config.php';
require 'partials.php';
require 'academic_options.php';
require_admin();

$id            = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$edit          = null;
$courseOptions = $conn->query('SELECT id,code,title FROM courses ORDER BY code')->fetchAll();

// ── Fetch linkable users (members/students not yet linked to any student record) ──
// Include currently-linked user so the dropdown shows it when editing
$linkable_users = $conn->query(
    "SELECT u.id, u.name, u.email, u.role
     FROM users u
     WHERE u.role IN ('member','student')
       AND u.status = 'active'
       AND (u.student_id IS NULL OR u.student_id = 0)
     ORDER BY u.name ASC"
)->fetchAll();

if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    // Unlink user before deleting student record
    $conn->prepare('UPDATE users SET student_id = NULL WHERE student_id = ?')->execute([$del_id]);
    $conn->prepare('DELETE FROM students WHERE id=?')->execute([$del_id]);
    redirect('students.php');
}

if ($id) {
    $s = $conn->prepare('SELECT * FROM students WHERE id=?');
    $s->execute([$id]);
    $edit = $s->fetch();

    // If this student is already linked to a user, add that user to dropdown options
    if ($edit && !empty($edit['user_id'])) {
        $linked_user_q = $conn->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
        $linked_user_q->execute([$edit['user_id']]);
        $linked_user = $linked_user_q->fetch();
        if ($linked_user) {
            // Prepend linked user to list so it shows in dropdown even if already linked
            array_unshift($linkable_users, $linked_user);
        }
    }
}

// Fetch currently enrolled course IDs for the student being edited
$enrolled_course_ids = [];
if ($edit) {
    $eq = $conn->prepare('SELECT course_id FROM enrollments WHERE student_id=? ORDER BY course_id');
    $eq->execute([$edit['id']]);
    $enrolled_course_ids = array_column($eq->fetchAll(), 'course_id');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program = $_POST['program'];
    if (!in_array($program, allowed_programs(), true)) {
        flash('success', 'Invalid program.');
        redirect('students.php');
    }
    $data = [
        trim($_POST['student_no']),
        trim($_POST['full_name']),
        filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
        $program,
        trim($_POST['year_level'])
    ];

    $link_user_id = filter_input(INPUT_POST, 'link_user_id', FILTER_VALIDATE_INT) ?: null;

    if (!empty($_POST['id'])) {
        $student_id = (int)$_POST['id'];

        // Unlink previously linked user (if any) before re-linking
        $prev = $conn->prepare('SELECT user_id FROM students WHERE id = ?');
        $prev->execute([$student_id]);
        $prev_user_id = $prev->fetchColumn();
        if ($prev_user_id && $prev_user_id != $link_user_id) {
            $conn->prepare('UPDATE users SET student_id = NULL WHERE id = ?')->execute([$prev_user_id]);
        }

        $data[] = $link_user_id;
        $data[] = $student_id;
        $conn->prepare('UPDATE students SET student_no=?,full_name=?,email=?,program=?,year_level=?,user_id=? WHERE id=?')->execute($data);
    } else {
        $data[] = $link_user_id;
        $conn->prepare('INSERT INTO students(student_no,full_name,email,program,year_level,user_id) VALUES(?,?,?,?,?,?)')->execute($data);
        $student_id = (int)$conn->lastInsertId();
    }

    // ── Link user account both ways ──
    if ($link_user_id) {
        $conn->prepare('UPDATE users SET student_id = ? WHERE id = ?')->execute([$student_id, $link_user_id]);
    }

    // Sync enrollments
    $new_ids = array_unique(array_filter(array_map('intval', $_POST['course_ids'] ?? [])));
    $conn->prepare('DELETE FROM enrollments WHERE student_id=?')->execute([$student_id]);
    foreach ($new_ids as $cid) {
        $conn->prepare('INSERT IGNORE INTO enrollments(student_id,course_id,status) VALUES(?,?,?)')->execute([$student_id, $cid, 'enrolled']);
    }

    redirect('students.php');
}

$rows = $conn->query(
    'SELECT students.*,
            u.name   AS linked_user_name,
            u.email  AS linked_user_email,
            u.role   AS linked_user_role,
            GROUP_CONCAT(c.code ORDER BY c.code SEPARATOR ", ") AS enrolled_courses
     FROM students
     LEFT JOIN users u ON u.id = students.user_id
     LEFT JOIN enrollments e ON e.student_id = students.id
     LEFT JOIN courses c ON c.id = e.course_id
     GROUP BY students.id
     ORDER BY students.program, students.full_name'
)->fetchAll();

// Unique programs and year levels for filters
$programs_in_use  = array_unique(array_column($rows, 'program'));
$year_levels      = array_unique(array_filter(array_column($rows, 'year_level')));
sort($programs_in_use);
sort($year_levels);

page_header('Students', 'students');
?>

<section class="panel">
    <a class="btn" href="students.php?new=1">+ Register Student</a>
</section>

<?php if (isset($_GET['new']) || $edit): ?>
<section class="panel">
    <form method="post">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="form-grid">
            <label>Student No.<input name="student_no" value="<?= e($edit['student_no'] ?? '') ?>"></label>
            <label>Full Name<input name="full_name" value="<?= e($edit['full_name'] ?? '') ?>"></label>
            <label>Email<input name="email" type="email" value="<?= e($edit['email'] ?? '') ?>"></label>
            <label>Program<select name="program"><?= program_options_html($edit['program'] ?? '') ?></select></label>
            <label>Year Level<input name="year_level" value="<?= e($edit['year_level'] ?? '') ?>"></label>
        </div>

        <!-- ── Link User Account ── -->
        <div style="margin-top:var(--sp-4);">
            <div style="font-size:.85rem;font-weight:600;margin-bottom:var(--sp-2);color:var(--ink-60);">
                Link User Account
                <span style="font-weight:400;color:var(--ink-40);margin-left:6px;">
                    — connects this student record to a registered member/student login
                </span>
            </div>
            <select name="link_user_id" style="width:100%;max-width:480px;">
                <option value="">— No linked account —</option>
                <?php foreach ($linkable_users as $u): ?>
                <option value="<?= $u['id'] ?>"
                    <?= (!empty($edit['user_id']) && $edit['user_id'] == $u['id']) ? 'selected' : '' ?>>
                    <?= e($u['name']) ?> — <?= e($u['email']) ?>
                    (<?= e($u['role']) ?>)
                </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($edit['user_id'])): ?>
            <div style="margin-top:6px;font-size:.8rem;color:var(--ink-50);">
                ✅ Currently linked to a user account. Changing this will unlink the old one.
            </div>
            <?php endif; ?>
        </div>

        <!-- Enrolled Courses — add/remove rows -->
        <div style="margin-top:var(--sp-4);">
            <div style="font-size:.85rem;font-weight:600;margin-bottom:var(--sp-2);color:var(--ink-60);">Enrolled Courses</div>
            <div id="courseRows">
                <?php
                $init_courses = !empty($enrolled_course_ids) ? $enrolled_course_ids : [''];
                foreach ($init_courses as $ecid): ?>
                <div class="course-row" style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
                    <select name="course_ids[]" style="flex:1;">
                        <option value="">— Select course —</option>
                        <?php foreach ($courseOptions as $co): ?>
                        <option value="<?= $co['id'] ?>" <?= (!empty($ecid) && $co['id'] == $ecid) ? 'selected' : '' ?>>
                            <?= e($co['code'] . ' — ' . $co['title']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn danger" onclick="removeCourseRow(this)" style="padding:6px 14px;flex-shrink:0;">✕</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn light" onclick="addCourseRow()" style="margin-top:4px;">+ Add Course</button>
        </div>

        <br>
        <button class="btn">Save</button>
    </form>
</section>

<script>
var courseOptionsHtml = <?php
    $opts = '<option value="">— Select course —</option>';
    foreach ($courseOptions as $co) {
        $opts .= '<option value="' . $co['id'] . '">' . htmlspecialchars($co['code'] . ' — ' . $co['title'], ENT_QUOTES) . '</option>';
    }
    echo json_encode($opts);
?>;

function addCourseRow() {
    var row = document.createElement('div');
    row.className = 'course-row';
    row.style.cssText = 'display:flex;gap:8px;align-items:center;margin-bottom:8px;';
    row.innerHTML = '<select name="course_ids[]" style="flex:1;">' + courseOptionsHtml + '</select>'
        + '<button type="button" class="btn danger" onclick="removeCourseRow(this)" style="padding:6px 14px;flex-shrink:0;">✕</button>';
    document.getElementById('courseRows').appendChild(row);
}

function removeCourseRow(btn) {
    var rows = document.querySelectorAll('#courseRows .course-row');
    if (rows.length > 1) {
        btn.closest('.course-row').remove();
    } else {
        btn.closest('.course-row').querySelector('select').value = '';
    }
}
</script>
<?php endif; ?>

<?php if (empty($rows)): ?>
<section class="panel">
    <div class="empty-state">
        <div style="font-size:3rem;">🎓</div>
        <p>No students registered yet.</p>
    </div>
</section>
<?php else: ?>

<!-- SEARCH + FILTER BAR -->
<div class="search-filter-bar">
    <input type="text" id="sfSearch" class="sf-search" placeholder="Search by name or student no.…">
    <select id="sfProgram" data-filter-attr="program">
        <option value="">All Programs</option>
        <?php foreach ($programs_in_use as $p): ?>
        <option value="<?= e(strtolower($p)) ?>"><?= e($p) ?></option>
        <?php endforeach; ?>
    </select>
    <select id="sfYear" data-filter-attr="year">
        <option value="">All Year Levels</option>
        <?php foreach ($year_levels as $y): ?>
        <option value="<?= e(strtolower($y)) ?>"><?= e($y) ?></option>
        <?php endforeach; ?>
    </select>
    <select id="sfLinked" data-filter-attr="linked">
        <option value="">All Accounts</option>
        <option value="linked">Linked</option>
        <option value="unlinked">Unlinked</option>
    </select>
    <button class="sf-reset" id="sfReset">Reset</button>
    <span class="sf-count" id="sfCount"></span>
</div>

<div class="card-grid" id="cardGrid">
<?php foreach ($rows as $r): ?>
<div class="record-card <?= school_card_class($r['program']) ?>"
     data-search="<?= e(strtolower($r['full_name'] . ' ' . $r['student_no'] . ' ' . $r['email'])) ?>"
     data-program="<?= e(strtolower($r['program'])) ?>"
     data-year="<?= e(strtolower($r['year_level'])) ?>"
     data-linked="<?= !empty($r['user_id']) ? 'linked' : 'unlinked' ?>">
    <div class="record-card-header">
        <?php $sch = strtolower(school_for_program($r['program'])); ?>
        <div class="record-card-avatar school-logo">
            <img src="assets/images/logo-<?= e($sch) ?>.png" alt="<?= e(strtoupper($sch)) ?>">
        </div>
        <div>
            <div class="record-card-title"><?= e($r['full_name']) ?></div>
            <div class="record-card-sub"><?= e($r['student_no']) ?></div>
        </div>
    </div>
    <div class="record-card-body">
        <div class="record-card-field">
            <label>Email</label>
            <span><?= e($r['email']) ?></span>
        </div>
        <div class="record-card-field">
            <label>School</label>
            <span><?= e(school_for_program($r['program'])) ?></span>
        </div>
        <div class="record-card-field">
            <label>Program</label>
            <span><span class="tag"><?= e($r['program']) ?></span></span>
        </div>
        <div class="record-card-field">
            <label>Courses</label>
            <span><?= e($r['enrolled_courses'] ?? 'None') ?></span>
        </div>
        <div class="record-card-field">
            <label>Year Level</label>
            <span><?= e($r['year_level']) ?></span>
        </div>
        <div class="record-card-field">
            <label>User Account</label>
            <span>
                <?php if (!empty($r['user_id'])): ?>
                    <span class="tag" style="background:var(--clr-success,#1a7a4a);color:#fff;">
                        ✅ <?= e($r['linked_user_name']) ?>
                    </span>
                <?php else: ?>
                    <span style="color:rgba(255,255,255,.5);font-size:.82rem;">No linked account</span>
                <?php endif; ?>
            </span>
        </div>
    </div>
    <div class="record-card-actions">
        <a class="btn light" href="students.php?id=<?= $r['id'] ?>">Edit</a>
        <a class="btn danger" href="students.php?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this student?')">Delete</a>
    </div>
</div>
<?php endforeach; ?>
<div class="sf-no-results" id="sfNoResults">😕 No students match your search.</div>
</div>

<script>
(function () {
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
                    if (filters[attr] && (c.dataset[attr] || '') !== filters[attr]) {
                        show = false; break;
                    }
                }
            }
            c.style.display = show ? '' : 'none';
            if (show) vis++;
        });

        count.textContent = vis + ' of ' + cards.length + ' student' + (cards.length !== 1 ? 's' : '');
        noRes.classList.toggle('visible', vis === 0);
    }

    search.addEventListener('input', run);
    selects.forEach(function (s) { s.addEventListener('change', run); });
    reset.addEventListener('click', function () {
        search.value = '';
        selects.forEach(function (s) { s.value = ''; });
        run();
    });
    run();
})();
</script>
<?php endif; ?>

<?php page_footer(); ?>
