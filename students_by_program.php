<?php
require 'config.php';
require 'partials.php';
require 'academic_options.php';
require_admin();

$program  = $_GET['program'] ?? '';
$programs = allowed_programs();

if ($program) {
    $s = $conn->prepare('SELECT * FROM students WHERE program=? ORDER BY full_name');
    $s->execute([$program]);
    $rows = $s->fetchAll();
} else {
    $rows = $conn->query('SELECT * FROM students ORDER BY program, full_name')->fetchAll();
}

$g = [];
foreach ($rows as $r) $g[$r['program']][] = $r;

page_header('Students by Program', 'students_by_program');
?>

<section class="hero">
    <h1>Student List Per Program</h1>
</section>

<!-- SEARCH + FILTER BAR -->
<div class="search-filter-bar">
    <input type="text" id="sfSearch" class="sf-search" placeholder="Search by name or student no.…">
    <form style="display:contents;">
        <select name="program" id="sfProgram" onchange="this.form.submit()">
            <option value="">All Programs</option>
            <?php foreach ($programs as $p): ?>
            <option value="<?= e($p) ?>" <?= $program === $p ? 'selected' : '' ?>><?= e($p) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" style="display:none;"></button>
    </form>
    <button class="sf-reset" id="sfReset">Reset Search</button>
    <span class="sf-count" id="sfCount"></span>
</div>

<?php if (empty($g)): ?>
<section class="panel">
    <div class="empty-state">
        <div style="font-size:3rem;">🎓</div>
        <p>No students found<?= $program ? ' for ' . e($program) : '' ?>.</p>
    </div>
</section>
<?php else: ?>

<?php foreach ($g as $p => $items): ?>
<?php $sch = strtolower(school_for_program($p)); ?>
<div class="school-section <?= e($sch) ?>" data-group="<?= e(strtolower($p)) ?>">
    <div class="school-section-header">
        <img src="assets/images/logo-<?= e($sch) ?>.png" alt="<?= e(strtoupper($sch)) ?>">
        <h2><?= e(strtoupper($sch)) ?> &middot; <?= e($p) ?></h2>
        <span class="pill" id="pill-<?= e($p) ?>"><?= count($items) ?></span>
    </div>
    <div class="table-inner">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $s): ?>
                <tr data-search="<?= e(strtolower($s['full_name'] . ' ' . $s['student_no'] . ' ' . $s['email'])) ?>">
                    <td><?= e($s['student_no']) ?></td>
                    <td style="font-weight:600;"><?= e($s['full_name']) ?></td>
                    <td style="color:var(--ink-80);"><?= e($s['email']) ?></td>
                    <td><?= $s['year_level'] > 0 ? 'Year ' . e($s['year_level']) : '<span style="color:var(--ink-40);font-size:.8rem;">—</span>' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>

<div class="sf-no-results" id="sfNoResults">😕 No students match your search.</div>

<script>
(function () {
    var search = document.getElementById('sfSearch');
    var count  = document.getElementById('sfCount');
    var noRes  = document.getElementById('sfNoResults');
    var reset  = document.getElementById('sfReset');
    var rows   = document.querySelectorAll('tbody tr[data-search]');

    // total across all tables
    var total = rows.length;

    function run() {
        var q = search.value.toLowerCase().trim();
        var vis = 0;
        var groupVis = {};

        rows.forEach(function (tr) {
            var text = tr.dataset.search || '';
            var show = !q || text.includes(q);
            tr.style.display = show ? '' : 'none';
            if (show) {
                vis++;
                // track visible per program group
                var section = tr.closest('section');
                if (section) {
                    var g = section.dataset.group;
                    groupVis[g] = (groupVis[g] || 0) + 1;
                }
            }
        });

        // update pill counts and hide empty sections
        document.querySelectorAll('.school-section[data-group]').forEach(function (sec) {
            var g   = sec.dataset.group;
            var cnt = groupVis[g] || 0;
            sec.style.display = cnt === 0 && q ? 'none' : '';
            // find the pill by program label
            var pills = sec.querySelectorAll('.pill');
            pills.forEach(function (pill) { pill.textContent = cnt || '0'; });
        });

        count.textContent = vis + ' of ' + total + ' student' + (total !== 1 ? 's' : '');
        noRes.classList.toggle('visible', vis === 0 && q !== '');
    }

    search.addEventListener('input', run);
    reset.addEventListener('click', function () {
        search.value = '';
        run();
    });

    // init counts
    count.textContent = total + ' of ' + total + ' students';
})();
</script>
<?php endif; ?>

<?php page_footer(); ?>
