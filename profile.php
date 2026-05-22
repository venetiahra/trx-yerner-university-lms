<?php
require 'config.php';
require 'partials.php';
require_login();

$uid = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

if (!$user) { session_destroy(); redirect('login.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone   = trim($_POST['phone'] ?? '');
    $company = $_POST['company'] ?? ($user['company'] ?? '');

    if ($name && $email) {
        $conn->prepare("UPDATE users SET name=?, email=?, phone=?, company=? WHERE id=?")
             ->execute([$name, $email, $phone, $company, $uid]);
        $_SESSION['user_name'] = $name;
    }

    if (!empty($_FILES['profile_pic']['name'])) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $allowed   = ['jpg','jpeg','png','gif','webp'];
        $ext       = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $newFileName = "profile_" . $uid . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadDir . $newFileName)) {
                $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?")->execute([$newFileName, $uid]);
            }
        }
    }

    flash('success', 'Profile updated successfully!');
    redirect('profile.php');
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

$role = $_SESSION['user_role'] ?? '';

/* Admin keeps the old layout */
if ($role === 'owner' || $role === 'admin') {
    page_header('My Profile', 'profile');
    ?>
    <section class="hero" style="margin-bottom:24px;">
      <h1 style="font-size:1.8rem;margin-bottom:8px;">My Profile</h1>
      <p>Manage your account details and profile photo.</p>
    </section>
    <section class="panel" style="max-width:740px;">
      <div style="display:flex;align-items:center;gap:20px;margin-bottom:28px;padding-bottom:24px;border-bottom:1px solid var(--border);">
        <?php if (!empty($user['profile_pic'])): ?>
          <img src="uploads/<?= e($user['profile_pic']) ?>" class="avatar">
        <?php else: ?>
          <div class="avatar-placeholder"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
        <?php endif; ?>
        <div>
          <h2 style="font-family:var(--font-display);font-size:1.3rem;margin-bottom:4px;"><?= e($user['name']) ?></h2>
          <p class="small"><?= e($user['email']) ?></p>
          <div style="margin-top:8px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <span class="badge badge-<?= e($user['status'] ?? 'active') ?>"><?= e($user['status'] ?? 'active') ?></span>
            <span class="pill" style="font-size:.72rem;"><?= ucfirst(e($user['role'])) ?></span>
            <?php if (!empty($user['company'])): ?>
            <span class="pill" style="font-size:.72rem;"><?= e($user['company']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <form method="post" enctype="multipart/form-data">
        <div class="form-grid">
          <label>Full Name<input name="name" value="<?= e($user['name']) ?>" required></label>
          <label>Email<input type="email" name="email" value="<?= e($user['email']) ?>" required></label>
          <label>Phone<input name="phone" value="<?= e($user['phone'] ?? '') ?>" placeholder="+63 9XX XXX XXXX"></label>
          <label>School / Organization
            <select name="company">
              <option value="SECA" <?= ($user['company'] ?? '') === 'SECA' ? 'selected' : '' ?>>SECA</option>
              <option value="SASE" <?= ($user['company'] ?? '') === 'SASE' ? 'selected' : '' ?>>SASE</option>
              <option value="SBMA" <?= ($user['company'] ?? '') === 'SBMA' ? 'selected' : '' ?>>SBMA</option>
              <option value="SHS"  <?= ($user['company'] ?? '') === 'SHS'  ? 'selected' : '' ?>>SHS</option>
            </select>
          </label>
          <label style="grid-column:1/-1;">Profile Picture
            <input type="file" name="profile_pic" accept="image/*" style="padding:10px 14px;cursor:pointer;">
          </label>
        </div>
        <div style="margin-top:24px;">
          <button class="btn" type="submit" style="background:var(--navy);">Save Changes</button>
          <a href="javascript:history.back()" class="btn light" style="margin-left:10px;">Cancel</a>
        </div>
      </form>
    </section>
    <?php page_footer();
    return;
}

/* Student / Member — iCloud style */
icloud_header('My Profile');
render_school_banner($conn);
render_left_quick_access('profile');

$name_parts = explode(' ', trim($user['name']));
$initials   = strtoupper(substr($name_parts[0], 0, 1) . substr(end($name_parts), 0, 1));

/* Back link depends on role */
$back_href  = ($role === 'student') ? 'student_dashboard.php' : 'member_dashboard.php';
$back_label = ($role === 'student') ? 'My Home' : 'Home';
?>

<div class="ic-body-solo">

  <!-- Page header -->
  <div class="ic-page-header">
    <a href="<?= $back_href ?>" class="ic-page-back">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M7.5 2L3.5 6l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <?= $back_label ?>
    </a>
    <div class="ic-page-title-solo">My Profile</div>
  </div>

  <?php if ($m = flash('success')): ?>
  <div style="background:rgba(29,158,117,.15);border:1px solid rgba(29,158,117,.3);color:#5dcaa5;border-radius:10px;padding:12px 18px;font-size:.85rem;">✓ <?= e($m) ?></div>
  <?php endif; ?>

  <!-- Identity card -->
  <div class="ic-widget">
    <div style="display:flex;align-items:center;gap:20px;padding:var(--sp-5) var(--sp-5) var(--sp-4);">
      <?php if (!empty($user['profile_pic'])): ?>
        <img src="uploads/<?= e($user['profile_pic']) ?>" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.15);">
      <?php else: ?>
        <div class="ic-prof-avatar" style="width:64px;height:64px;font-size:1.2rem;"><?= e($initials) ?></div>
      <?php endif; ?>
      <div>
        <div style="font-size:1.1rem;font-weight:700;color:#fff;margin-bottom:4px;"><?= e($user['name']) ?></div>
        <div style="font-size:.8rem;color:rgba(255,255,255,.42);margin-bottom:10px;"><?= e($user['email']) ?></div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <span class="ic-tag" style="background:rgba(29,158,117,.2);color:#5dcaa5;">● <?= ucfirst(e($user['status'] ?? 'active')) ?></span>
          <span class="ic-tag ic-tag-blue"><?= ucfirst(e($user['role'])) ?></span>
          <?php if (!empty($user['company'])): ?>
          <span class="ic-tag" style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.6);"><?= e($user['company']) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit form -->
  <div class="ic-widget">
    <div class="ic-widget-header">
      <div class="ic-widget-icon ic-icon-coral">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="#fff" stroke-width="1.5"/><circle cx="8" cy="6" r="2.5" stroke="#fff" stroke-width="1.3"/><path d="M3.5 13c0-2.485 2.015-4 4.5-4s4.5 1.515 4.5 4" stroke="#fff" stroke-width="1.3" stroke-linecap="round"/></svg>
      </div>
      <div>
        <div class="ic-widget-title">Edit Profile</div>
        <div class="ic-widget-sub">Update your account details</div>
      </div>
    </div>

    <div class="ic-widget-body" style="padding-top:var(--sp-3);">
      <form method="post" enctype="multipart/form-data">
        <div class="ic-form-grid">
          <label class="ic-label">
            <span>Full Name</span>
            <input class="ic-input" name="name" value="<?= e($user['name']) ?>" required>
          </label>
          <label class="ic-label">
            <span>Email</span>
            <input class="ic-input" type="email" name="email" value="<?= e($user['email']) ?>" required>
          </label>
          <label class="ic-label">
            <span>Phone</span>
            <input class="ic-input" name="phone" value="<?= e($user['phone'] ?? '') ?>" placeholder="+63 9XX XXX XXXX">
          </label>
          <label class="ic-label">
            <span>School / Organization</span>
            <select class="ic-input" name="company">
              <option value="SECA" <?= ($user['company'] ?? '') === 'SECA' ? 'selected' : '' ?>>SECA</option>
              <option value="SASE" <?= ($user['company'] ?? '') === 'SASE' ? 'selected' : '' ?>>SASE</option>
              <option value="SBMA" <?= ($user['company'] ?? '') === 'SBMA' ? 'selected' : '' ?>>SBMA</option>
              <option value="SHS"  <?= ($user['company'] ?? '') === 'SHS'  ? 'selected' : '' ?>>SHS</option>
            </select>
          </label>
          <label class="ic-label" style="grid-column:1/-1;">
            <span>Profile Picture</span>
            <input class="ic-input" type="file" name="profile_pic" accept="image/*" style="cursor:pointer;">
          </label>
        </div>

        <div style="display:flex;gap:12px;margin-top:var(--sp-5);">
          <button type="submit" style="padding:10px 24px;background:linear-gradient(135deg,var(--gold),var(--gold-dk));color:#fff;font-weight:700;border:none;border-radius:var(--r-full);cursor:pointer;font-size:.88rem;">Save Changes</button>
          <a href="javascript:history.back()" style="padding:10px 20px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.65);font-weight:600;border-radius:var(--r-full);font-size:.88rem;text-decoration:none;display:inline-flex;align-items:center;">Cancel</a>
        </div>
      </form>
    </div>
  </div>


</div>
<?php icloud_footer(); ?>
