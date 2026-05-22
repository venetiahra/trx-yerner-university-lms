<?php
require 'config.php';
require 'partials.php';
require_admin();

if (isset($_GET['delete'])) {
    $conn->prepare('DELETE FROM announcements WHERE id=?')->execute([(int)$_GET['delete']]);
    redirect('announcements.php');
}

$edit = null;
if (isset($_GET['id'])) {
    $s = $conn->prepare('SELECT * FROM announcements WHERE id=?');
    $s->execute([(int)$_GET['id']]);
    $edit = $s->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content']);
    if (!empty($_POST['id'])) {
        $conn->prepare('UPDATE announcements SET content=? WHERE id=?')->execute([$content, (int)$_POST['id']]);
    } else {
        $conn->prepare('INSERT INTO announcements(content) VALUES(?)')->execute([$content]);
    }
    redirect('announcements.php');
}

$rows = $conn->query('SELECT * FROM announcements ORDER BY id DESC')->fetchAll();
page_header('Announcements', 'announcements');
?>

<section class="panel">
    <a class="btn" href="announcements.php?new=1">+ Add Announcement</a>
</section>

<?php if (isset($_GET['new']) || $edit): ?>
<section class="panel">
    <form method="post">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="form-grid">
            <label style="grid-column:1/-1;">
                Content
                <textarea name="content" rows="4"><?= e($edit['content'] ?? '') ?></textarea>
            </label>
        </div>
        <br>
        <button class="btn">Save</button>
    </form>
</section>
<?php endif; ?>

<?php if (empty($rows)): ?>
<section class="panel">
    <div class="empty-state">
        <div style="font-size:3rem;">📢</div>
        <p>No announcements yet.</p>
    </div>
</section>
<?php else: ?>
<div class="card-grid">
<?php foreach ($rows as $r): ?>
<div class="record-card">
    <div class="record-card-header">
        <div class="record-card-avatar" style="font-size:1.3rem;background:linear-gradient(135deg,var(--gold),var(--gold-dk));color:var(--ink);">
            📢
        </div>
        <div>
            <div class="record-card-title">Announcement #<?= e($r['id']) ?></div>
            <div class="record-card-sub"><?= e($r['created_at'] ?? '') ?></div>
        </div>
    </div>
    <div class="record-card-body">
        <div class="record-card-field" style="grid-column:1/-1;">
            <label>Content</label>
            <span><?= e($r['content']) ?></span>
        </div>
    </div>
    <div class="record-card-actions">
        <a class="btn light" href="announcements.php?id=<?= $r['id'] ?>">Edit</a>
        <a class="btn danger" href="announcements.php?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this announcement?')">Delete</a>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php page_footer(); ?>
