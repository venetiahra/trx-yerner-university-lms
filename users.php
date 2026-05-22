<?php
require 'config.php';
require 'partials.php';
require 'academic_options.php';
require 'mail_helper.php';
require_admin();

if (isset($_GET['action']) && isset($_GET['id'])) {
    $uid    = (int)$_GET['id'];
    $action = $_GET['action'];
    $stmt   = $conn->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$uid]);
    $u = $stmt->fetch();
    if ($u) {
        switch ($action) {
            case 'approve':
                $status = 'active';
                send_system_email($conn, $uid, $u['email'], $u['name'], 'Your account has been approved', account_approved_email($APP_NAME, $u['name']), 'account_approved');
                break;
            case 'suspend':
                $status = 'suspended';
                send_system_email($conn, $uid, $u['email'], $u['name'], 'Your account has been suspended', account_suspended_email($APP_NAME, $u['name']), 'account_suspended');
                break;
            case 'block':
                $status = 'blocked';
                send_system_email($conn, $uid, $u['email'], $u['name'], 'Your account has been locked', account_blocked_email($APP_NAME, $u['name']), 'account_blocked');
                break;
            case 'reactivate':
                $status = 'active';
                send_system_email($conn, $uid, $u['email'], $u['name'], 'Your account has been reactivated', account_reactivated_email($APP_NAME, $u['name']), 'account_reactivated');
                break;
            default: redirect('users.php');
        }
        $conn->prepare("UPDATE users SET status=? WHERE id=?")->execute([$status, $uid]);
        flash('success', 'User status updated!');
    }
    redirect('users.php');
}

$id   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$edit = null;
if ($id) {
    $s = $conn->prepare('SELECT * FROM users WHERE id=?');
    $s->execute([$id]);
    $edit = $s->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company    = $_POST['company'];
    $new_status = $_POST['status'];
    $post_name  = trim($_POST['name']);
    $post_email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $data = [
        $post_name,
        $post_email,
        $_POST['role'],
        $company,
        trim($_POST['phone']),
        $new_status
    ];
    if (!empty($_POST['id'])) {
        $uid = (int)$_POST['id'];

        $old = $conn->prepare('SELECT status, email, name FROM users WHERE id=?');
        $old->execute([$uid]);
        $old_status = $old->fetchColumn();

        if ($_POST['password']) {
            $data[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $data[] = $uid;
            $conn->prepare('UPDATE users SET name=?,email=?,role=?,company=?,phone=?,status=?,password=? WHERE id=?')->execute($data);
        } else {
            $data[] = $uid;
            $conn->prepare('UPDATE users SET name=?,email=?,role=?,company=?,phone=?,status=? WHERE id=?')->execute($data);
        }

        // Send email only if status actually changed
        if ($post_email && $old_status !== $new_status) {
            $email_map = [
                'active'    => $old_status === 'pending'
                                ? ['Your account has been approved',    account_approved_email($APP_NAME, $post_name),    'account_approved']
                                : ['Your account has been reactivated', account_reactivated_email($APP_NAME, $post_name), 'account_reactivated'],
                'suspended' => ['Your account has been suspended',  account_suspended_email($APP_NAME, $post_name),  'account_suspended'],
                'blocked'   => ['Your account has been locked',     account_blocked_email($APP_NAME, $post_name),    'account_blocked'],
            ];
            if (isset($email_map[$new_status])) {
                [$subject, $html, $type] = $email_map[$new_status];
                send_system_email($conn, $uid, $post_email, $post_name, $subject, $html, $type);
            }
        }
    } else {
        $data[] = password_hash($_POST['password'] ?: 'member123', PASSWORD_DEFAULT);
        $conn->prepare('INSERT INTO users(name,email,role,company,phone,status,password) VALUES(?,?,?,?,?,?,?)')->execute($data);
    }
    redirect('users.php');
}

$rows = $conn->query('SELECT * FROM users ORDER BY FIELD(status,"pending","active","suspended","blocked"), role, name')->fetchAll();
ensure_account_email_logs($conn);
$logs = $conn->query('SELECT * FROM account_email_logs ORDER BY created_at DESC LIMIT 10')->fetchAll();

// Unique roles and statuses for filter dropdowns
$roles_in_use    = array_unique(array_column($rows, 'role'));
$statuses_in_use = array_unique(array_column($rows, 'status'));
sort($roles_in_use);
sort($statuses_in_use);

page_header('Users & Approvals', 'users');
?>

<section class="hero">
    <h1>Users & Account Approvals</h1>
</section>

<section class="panel">
    <a class="btn" href="users.php?new=1">+ Add User</a>
</section>

<?php if (isset($_GET['new']) || $edit): ?>
<section class="panel">
    <form method="post">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="form-grid">
            <label>Name<input name="name" value="<?= e($edit['name'] ?? '') ?>"></label>
            <label>Email<input type="email" name="email" value="<?= e($edit['email'] ?? '') ?>"></label>
            <label>Role
                <select name="role">
                    <option value="member">Member</option>
                    <option value="student">Student</option>
                    <option value="admin">Admin</option>
                    <option value="owner">Owner</option>
                </select>
            </label>
            <label>Organization
                <select name="company"><?= school_options_html($edit['company'] ?? '') ?></select>
            </label>
            <label>Status
                <select name="status">
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="blocked">Blocked</option>
                </select>
            </label>
            <label>Phone<input name="phone" value="<?= e($edit['phone'] ?? '') ?>"></label>
            <label>Password<input type="password" name="password"></label>
        </div>
        <br>
        <button class="btn">Save</button>
    </form>
</section>
<?php endif; ?>

<!-- USERS SECTION -->
<div style="margin-bottom:var(--sp-3);font-weight:700;font-size:.85rem;color:var(--ink-40);text-transform:uppercase;letter-spacing:.08em;">
    Accounts (<?= count($rows) ?>)
</div>

<?php if (empty($rows)): ?>
<section class="panel">
    <div class="empty-state">
        <div style="font-size:3rem;">👤</div>
        <p>No users found.</p>
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
    <select id="sfStatus" data-filter-attr="status">
        <option value="">All Statuses</option>
        <?php foreach ($statuses_in_use as $status): ?>
        <option value="<?= e(strtolower($status)) ?>"><?= e(ucfirst($status)) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="sf-reset" id="sfReset">Reset</button>
    <span class="sf-count" id="sfCount"></span>
</div>

<div class="card-grid" id="cardGrid">
<?php foreach ($rows as $r): ?>
<div class="record-card <?= !empty($r['company']) ? 'school-'.strtolower($r['company']) : '' ?>"
     data-search="<?= e(strtolower($r['name'] . ' ' . $r['email'] . ' ' . ($r['company'] ?? '') . ' ' . $r['phone'])) ?>"
     data-role="<?= e(strtolower($r['role'])) ?>"
     data-status="<?= e(strtolower($r['status'])) ?>">
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
            <label>Status</label>
            <span><span class="badge badge-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></span>
        </div>
        <div class="record-card-field">
            <label>School</label>
            <span><?= e($r['company'] ?? '—') ?></span>
        </div>
        <div class="record-card-field">
            <label>Phone</label>
            <span><?= e($r['phone'] ?? '—') ?></span>
        </div>
    </div>
    <div class="record-card-actions" style="flex-wrap:wrap;">
        <?php if ($r['status'] === 'pending'): ?>
            <a class="btn" style="flex:1;" href="users.php?action=approve&id=<?= $r['id'] ?>">Approve</a>
        <?php endif; ?>
        <?php if ($r['status'] === 'active'): ?>
            <a class="btn light" href="users.php?action=suspend&id=<?= $r['id'] ?>">Suspend</a>
            <a class="btn danger" href="users.php?action=block&id=<?= $r['id'] ?>">Lock</a>
        <?php endif; ?>
        <?php if ($r['status'] === 'suspended' || $r['status'] === 'blocked'): ?>
            <a class="btn" style="flex:1;" href="users.php?action=reactivate&id=<?= $r['id'] ?>">Reactivate</a>
        <?php endif; ?>
        <a class="btn light" href="users.php?id=<?= $r['id'] ?>">Edit</a>
    </div>
</div>
<?php endforeach; ?>
<div class="sf-no-results" id="sfNoResults">😕 No users match your search.</div>
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

        count.textContent = vis + ' of ' + cards.length + ' user' + (cards.length !== 1 ? 's' : '');
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

<!-- EMAIL LOGS -->
<div style="margin-top:var(--sp-8);margin-bottom:var(--sp-3);font-weight:700;font-size:.85rem;color:var(--ink-40);text-transform:uppercase;letter-spacing:.08em;">
    Recent Email Logs
</div>
<?php if (empty($logs)): ?>
<section class="panel">
    <div class="empty-state">
        <div style="font-size:2rem;">📧</div>
        <p>No email logs yet.</p>
    </div>
</section>
<?php else: ?>
<div class="card-grid">
<?php foreach ($logs as $l): ?>
<div class="record-card">
    <div class="record-card-header">
        <div class="record-card-avatar" style="font-size:.75rem;">📧</div>
        <div>
            <div class="record-card-title"><?= e($l['recipient_email'] ?? $l['recipient'] ?? '—') ?></div>
            <div class="record-card-sub"><?= e($l['email_type'] ?? $l['type'] ?? '—') ?></div>
        </div>
    </div>
    <div class="record-card-body">
        <div class="record-card-field">
            <label>Status</label>
            <span><span class="badge badge-<?= e($l['status']) ?>"><?= e($l['status']) ?></span></span>
        </div>
        <div class="record-card-field">
            <label>Date</label>
            <span><?= e($l['created_at']) ?></span>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php page_footer(); ?>
