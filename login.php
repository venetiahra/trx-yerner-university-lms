<?php
require 'config.php';

if (!empty($_SESSION['user_id'])) {
    redirect(role_home());
}

// Redirect direct visits to index modal
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    redirect('index.php?modal=login');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $pass  = $_POST['password'] ?? '';
    $stmt  = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    if ($u && password_verify($pass, $u['password'])) {
        if ($u['status'] === 'pending') {
            $error = '⏳ Your account is still pending approval. Please wait for the admin.';
        } elseif ($u['status'] === 'blocked') {
            $error = '🔒 Your account has been locked by the administrator.';
        } elseif ($u['status'] === 'suspended') {
            $error = '⚠️ Your account is suspended. Please contact admin.';
        } else {
            $_SESSION['user_id']   = $u['id'];
            $_SESSION['user_name'] = $u['name'];
            $_SESSION['user_role'] = $u['role'];
            redirect(role_home());
        }
    } else {
        $error = '❌ Invalid email or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign In | <?= e($APP_NAME) ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<section class="login">
  <div class="login-card">

    <!-- Art Panel -->
    <div class="login-art">
      <div class="brand">
        <div class="logo">TRX</div>
        <span><?= e($APP_NAME) ?></span>
      </div>

      <div class="login-art-body">
        <h1>University learning,<br>made premium.</h1>
        <p>
          A secure, unified portal for students, faculty,
          and administrators to manage academic life.
        </p>
      </div>

      <div class="login-art-footer">
        © <?= date('Y') ?> <?= e($APP_NAME) ?>. All rights reserved.
      </div>
    </div>

    <!-- Form Panel -->
    <form class="login-form" method="post" autocomplete="on">

      <div>
        <h2>Welcome back</h2>
        <p class="small" style="margin-top:6px;color:var(--ink-40);font-size:.82rem;">
          Sign in to access your portal
        </p>
      </div>

      <!-- Dev credentials hint -->
      <div class="small">
        <strong style="color:var(--ink-80);">Test accounts</strong><br>
        Owner: owner@trx.test / owner123<br>
        Admin: admin@trx.test / admin123<br>
        Student: student@trx.test / student123
      </div>

      <?php if ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <label>
        Email address
        <input name="email" type="email" placeholder="you@university.edu" required autocomplete="email">
      </label>

      <label>
        Password
        <input name="password" type="password" placeholder="••••••••" required autocomplete="current-password">
      </label>

      <button class="btn" type="submit" style="width:100%;padding:14px;font-size:.95rem;margin-top:4px;background:var(--navy);">
        Enter Portal →
      </button>

      <div style="text-align:center;font-size:.82rem;color:var(--ink-40);">
        Don't have an account?
        <a href="register.php" style="color:var(--accent);font-weight:700;">Register here</a>
      </div>

    </form>
  </div>
</section>

<script src="assets/js/app.js"></script>
</body>
</html>
