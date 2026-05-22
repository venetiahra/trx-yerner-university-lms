<?php
require 'config.php';
require 'partials.php';
require 'mail_helper.php';
require_admin();

if (isset($_GET['approve'])) {
    $uid = (int)$_GET['approve'];
    $s   = $conn->prepare('SELECT * FROM users WHERE id=?');
    $s->execute([$uid]);
    $u = $s->fetch();
    if ($u) {
        $conn->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$uid]);
        send_system_email($conn, $uid, $u['email'], $u['name'], 'Your account has been approved', account_approved_email($APP_NAME, $u['name']), 'account_approved');
        flash('success', 'Account approved and email prepared/sent.');
    }
    redirect('pending_approvals.php');
}

$rows = $conn->query("SELECT * FROM users WHERE status='pending' ORDER BY created_at DESC")->fetchAll();

// Unique roles and schools for filter dropdowns
$roles_in_use   = array_unique(array_column($rows, 'role'));
$schools_in_use = array_unique(array_filter(array_column($rows, 'company')));
sort($roles_in_use);
sort($schools_in_use);

page_header('Pending Approvals', 'pending_approvals');
?>

<section class="hero">
    <h1>Pending Account Approvals</h1>
    <p>Review newly registered accounts.</p>
</section>

<?php if (empty($rows)): ?>
<section class="panel">
    <div class="empty-state">
        <div style="font-size:3rem;">✅</div>
        <p>No pending approvals. All caught up!</p>
    </div>
</section>
<?php else: ?>

<!-- SEARCH + FILTER BAR -->
<div class="search-filter-bar">
    <input type="text" id="sfSearch" class="sf-search" placeholder="Search by name or email…">
    <select id="sfRole" data-filter-attr="role">
        <option value="">All Roles</option>
        <?php foreach ($roles_in_use as $role): ?>
        <option value="<?= e(strtolower($role)) ?>"><?= e(ucfirst($role)) ?></option>
        <?php endforeach; ?>
    </select>
    <select id="sfSchool" data-filter-attr="school">
        <option value="">All Schools</option>
        <?php foreach ($schools_in_use as $school): ?>
        <option value="<?= e(strtolower($school)) ?>"><?= e($school) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="sf-reset" id="sfReset">Reset</button>
    <span class="sf-count" id="sfCount"></span>
</div>

<div class="card-grid" id="cardGrid">
<?php foreach ($rows as $r): ?>
<div class="record-card <?= !empty($r['company']) ? 'school-'.strtolower($r['company']) : '' ?>"
     data-search="<?= e(strtolower($r['name'] . ' ' . $r['email'] . ' ' . ($r['company'] ?? ''))) ?>"
     data-role="<?= e(strtolower($r['role'])) ?>"
     data-school="<?= e(strtolower($r['company'] ?? '')) ?>">
    <div class="record-card-header">
        <div class="record-card-avatar">
            <?= strtoupper(substr($r['name'], 0, 1)) ?>
        </div>
        <div>
            <div class="record-card-title"><?= e($r['name']) ?></div>
            <div class="record-card-sub"><?= e($r['email']) ?></div>
        </div>
    </div>
    <div class="record-card-body">
        <div class="record-card-field">
            <label>Role</label>
            <span><span class="tag"><?= e($r['role']) ?></span></span>
        </div>
        <div class="record-card-field">
            <label>School</label>
            <span><?= e($r['company'] ?? '—') ?></span>
        </div>
        <div class="record-card-field">
            <label>Registered</label>
            <span><?= e($r['created_at']) ?></span>
        </div>
        <div class="record-card-field">
            <label>Status</label>
            <span><span class="badge badge-pending">Pending</span></span>
        </div>
    </div>
    <div class="record-card-actions">
        <a class="btn" style="flex:1;" href="pending_approvals.php?approve=<?= $r['id'] ?>">
            ✓ Approve & Send Email
        </a>
    </div>
</div>
<?php endforeach; ?>
<div class="sf-no-results" id="sfNoResults">😕 No pending approvals match your search.</div>
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

        count.textContent = vis + ' of ' + cards.length + ' approval' + (cards.length !== 1 ? 's' : '');
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
