<?php
require 'config.php';
require 'partials.php';
require_admin();

$courses = $conn->query('SELECT id,code,title FROM courses')->fetchAll();

if (isset($_GET['delete'])) {
    $conn->prepare('DELETE FROM lessons WHERE id=?')->execute([(int)$_GET['delete']]);
    redirect('lessons.php');
}

$edit = null;
if (isset($_GET['id'])) {
    $s = $conn->prepare('SELECT * FROM lessons WHERE id=?');
    $s->execute([(int)$_GET['id']]);
    $edit = $s->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id    = (int)$_POST['course_id'];
    $title        = trim($_POST['title']);
    $content      = trim($_POST['content']);
    $lesson_order = trim($_POST['lesson_order']);
    if (!empty($_POST['id'])) {
        $conn->prepare('UPDATE lessons SET course_id=?,title=?,content=?,lesson_order=? WHERE id=?')
             ->execute([$course_id, $title, $content, $lesson_order, (int)$_POST['id']]);
    } else {
        $conn->prepare('INSERT INTO lessons(course_id,title,content,lesson_order) VALUES(?,?,?,?)')
             ->execute([$course_id, $title, $content, $lesson_order]);
    }
    redirect('lessons.php');
}

$rows = $conn->query(
    'SELECT lessons.*, courses.code course_code, courses.title course_title, courses.program
     FROM lessons
     JOIN courses ON courses.id = lessons.course_id
     ORDER BY lessons.lesson_order ASC'
)->fetchAll();

// Unique courses for filter dropdown
$courses_in_use = [];
foreach ($rows as $r) {
    $key = $r['course_code'];
    if (!isset($courses_in_use[$key])) {
        $courses_in_use[$key] = $r['course_code'] . ' – ' . $r['course_title'];
    }
}
ksort($courses_in_use);

page_header('Lessons', 'lessons');
?>

<section class="panel">
    <a class="btn" href="lessons.php?new=1">+ Add</a>
</section>

<?php if (isset($_GET['new']) || $edit): ?>
<section class="panel">
    <form method="post">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="form-grid">
            <label>Course
                <select name="course_id">
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (($edit['course_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                        <?= e($c['code'] . ' - ' . $c['title']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Title<input name="title" value="<?= e($edit['title'] ?? '') ?>"></label>
            <label>Order<input name="lesson_order" type="number" value="<?= e($edit['lesson_order'] ?? '') ?>"></label>
            <label style="grid-column:1/-1;">Content<textarea name="content"><?= e($edit['content'] ?? '') ?></textarea></label>
        </div>
        <br>
        <button class="btn">Save</button>
    </form>
</section>
<?php endif; ?>

<?php if (empty($rows)): ?>
<section class="panel">
    <div class="empty-state">
        <div style="font-size:3rem;">📖</div>
        <p>No lessons added yet.</p>
    </div>
</section>
<?php else: ?>

<!-- SEARCH + FILTER BAR -->
<div class="search-filter-bar">
    <input type="text" id="sfSearch" class="sf-search" placeholder="Search by lesson title…">
    <select id="sfCourse" data-filter-attr="course">
        <option value="">All Courses</option>
        <?php foreach ($courses_in_use as $code => $label): ?>
        <option value="<?= e(strtolower($code)) ?>"><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="sf-reset" id="sfReset">Reset</button>
    <span class="sf-count" id="sfCount"></span>
</div>

<div class="card-grid" id="cardGrid">
<?php foreach ($rows as $r): ?>
<div class="record-card <?= school_card_class($r['program'] ?? '') ?>"
     data-search="<?= e(strtolower($r['title'] . ' ' . $r['course_code'] . ' ' . $r['course_title'])) ?>"
     data-course="<?= e(strtolower($r['course_code'])) ?>">
    <div class="record-card-header">
        <?php $sch = strtolower(school_for_program($r['program'] ?? '')); ?>
        <div class="record-card-avatar school-logo">
            <img src="assets/images/logo-<?= e($sch ?: 'seca') ?>.png" alt="<?= e(strtoupper($sch)) ?>">
        </div>
        <div>
            <div class="record-card-title"><?= e($r['title']) ?></div>
            <div class="record-card-sub">Order #<?= e($r['lesson_order']) ?></div>
        </div>
    </div>
    <div class="record-card-body">
        <div class="record-card-field">
            <label>Course</label>
            <span><span class="tag"><?= e($r['course_code']) ?></span></span>
        </div>
        <?php if (!empty($r['content'])): ?>
        <div class="record-card-field" style="grid-column:1/-1;">
            <label>Content Preview</label>
            <span><?= e(mb_strimwidth($r['content'], 0, 100, '...')) ?></span>
        </div>
        <?php endif; ?>
    </div>
    <div class="record-card-actions">
        <a class="btn light" href="lessons.php?id=<?= $r['id'] ?>">Edit</a>
        <a class="btn danger" href="lessons.php?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this lesson?')">Delete</a>
    </div>
</div>
<?php endforeach; ?>
<div class="sf-no-results" id="sfNoResults">😕 No lessons match your search.</div>
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

        count.textContent = vis + ' of ' + cards.length + ' record' + (cards.length !== 1 ? 's' : '');
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
