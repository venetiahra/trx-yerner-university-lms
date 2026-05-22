<?php
require 'config.php';
$loggedIn = !empty($_SESSION['user_id']);
$home     = $loggedIn ? role_home() : 'login.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($APP_NAME) ?> | Student Portal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="icon" type="image/png" href="assets/images/favicon.png">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --gold:    #c9a227;
      --gold-dk: #9a7a18;
      --gold-lt: rgba(201,162,39,.1);
      --navy:    #1a2744;
      --navy-2:  #253560;
      --ink:     #0d1117;
      --ink-2:   #3a3a4a;
      --ink-3:   #6b6b7a;
      --border:  rgba(0,0,0,.09);
      --bg:      #f5f3ee;
      --white:   #ffffff;
      --green:   #1a7a4a;
      --r:       14px;
    }

    html { scroll-behavior: smooth; }
    body {
      font-family: 'DM Sans', -apple-system, sans-serif;
      background: url('assets/images/bg.png') center/cover fixed no-repeat;
      color: var(--ink);
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased;
    }

    /* ── NAV ── */
    nav {
      position: sticky; top: 0; z-index: 100;
      background: rgba(245,243,238,.88);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--border);
    }
    .nav-inner {
      max-width: 1200px; margin: 0 auto;
      padding: 0 40px;
      height: 64px;
      display: flex; align-items: center; justify-content: space-between;
    }
    .nav-logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
    .logo-mark {
      width: 36px; height: 36px; border-radius: 10px;
      background: linear-gradient(135deg, var(--navy), var(--navy-2));
      display: grid; place-items: center;
      font-size: .72rem; font-weight: 700; color: var(--gold);
      letter-spacing: .04em;
      box-shadow: 0 2px 8px rgba(26,39,68,.25);
    }
    .nav-name {
      font-family: 'Playfair Display', Georgia, serif;
      font-size: 1rem; font-weight: 700; color: var(--navy); line-height: 1.1;
    }
    .nav-name small {
      display: block; font-family: 'DM Sans', sans-serif;
      font-size: .65rem; font-weight: 400; color: var(--ink-3);
      letter-spacing: .04em; text-transform: uppercase;
    }
    .nav-links { display: flex; align-items: center; gap: 8px; }
    .nav-link {
      padding: 8px 16px; border-radius: 8px;
      font-size: .85rem; font-weight: 500; color: var(--ink-2);
      text-decoration: none; transition: background .15s, color .15s;
      cursor: pointer; background: none; border: none; font-family: inherit;
    }
    .nav-link:hover { background: rgba(0,0,0,.05); color: var(--ink); }
    .nav-btn {
      padding: 9px 20px; border-radius: 8px;
      background: var(--navy); color: #fff;
      font-size: .85rem; font-weight: 600;
      text-decoration: none; transition: opacity .15s, transform .15s;
      cursor: pointer; border: none; font-family: inherit;
    }
    .nav-btn:hover { opacity: .85; transform: translateY(-1px); }

    /* ── HERO ── */
    .hero {
      max-width: 680px; margin: 60px auto 80px;
      padding: 72px 48px;
      text-align: center;
      background: rgba(13, 17, 23, 0.48);
      backdrop-filter: blur(20px) saturate(1.5);
      -webkit-backdrop-filter: blur(20px) saturate(1.5);
      border: 1px solid rgba(201, 162, 39, 0.22);
      border-radius: 24px;
      box-shadow: 0 8px 48px rgba(0,0,0,.55), inset 0 1px 0 rgba(255,220,100,.1);
    }
    .hero-eyebrow {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
      border-radius: 999px; padding: 6px 14px; margin-bottom: 24px;
      font-size: .72rem; font-weight: 600; color: var(--gold);
      letter-spacing: .06em; text-transform: uppercase;
      backdrop-filter: blur(6px);
    }
    .dot-live {
      width: 6px; height: 6px; border-radius: 50%;
      background: var(--green); animation: blink 2s infinite;
    }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
    .hero h1 {
      font-family: 'Playfair Display', Georgia, serif;
      font-size: clamp(2.8rem, 4.5vw, 4rem);
      font-weight: 800; color: #fff;
      line-height: 1.08; letter-spacing: -.02em; margin-bottom: 20px;
    }
    .hero h1 em { font-style: italic; color: var(--gold); }
    .hero-sub {
      font-size: 1.05rem; color: rgba(255,255,255,.7); line-height: 1.7;
      margin-bottom: 36px; max-width: 520px; font-weight: 300;
      margin-left: auto; margin-right: auto;
    }
    .hero-actions { display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; }
    .btn-primary {
      padding: 14px 28px; border-radius: var(--r);
      background: var(--navy); color: #fff;
      font-size: .92rem; font-weight: 600; text-decoration: none;
      transition: transform .15s, box-shadow .15s;
      box-shadow: 0 4px 20px rgba(26,39,68,.3);
      cursor: pointer; border: none; font-family: inherit;
    }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(26,39,68,.4); }
    .btn-outline {
      padding: 14px 28px; border-radius: var(--r);
      background: rgba(255,255,255,.08); color: rgba(255,255,255,.85);
      border: 1.5px solid rgba(255,255,255,.2);
      font-size: .92rem; font-weight: 600; text-decoration: none;
      transition: border-color .15s, background .15s;
      cursor: pointer; font-family: inherit;
    }
    .btn-outline:hover { border-color: rgba(255,255,255,.45); background: rgba(255,255,255,.14); }

    /* ── SHARED ── */
    .section { max-width: 1200px; margin: 0 auto; padding: 80px 40px; }
    .section-label {
      font-size: .72rem; font-weight: 600; color: var(--gold-dk);
      letter-spacing: .1em; text-transform: uppercase; margin-bottom: 12px;
    }
    .section-title {
      font-family: 'Playfair Display', serif;
      font-size: clamp(1.8rem, 2.5vw, 2.4rem);
      font-weight: 800; color: var(--navy);
      letter-spacing: -.02em; line-height: 1.1; margin-bottom: 16px;
    }
    .section-sub {
      font-size: .95rem; color: var(--ink-3); line-height: 1.7;
      max-width: 480px; font-weight: 300;
    }

    /* ── SCHOOLS ── */
    .schools-wrap { background: transparent; }
    .schools-inner {
      max-width: 1200px; margin: 0 auto; padding: 80px 40px;
      display: grid; grid-template-columns: 1fr 2fr; gap: 60px; align-items: start;
    }
    .schools-inner .section-label { color: var(--gold); }
    .schools-inner .section-title { color: #fff; }
    .schools-inner .section-sub   { color: rgba(255,255,255,.82); }
    .schools-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .school-card {
      position: relative; overflow: hidden;
      border: 1px solid rgba(255,255,255,.18);
      border-radius: 16px; padding: 24px;
      background: rgba(13, 17, 23, 0.42);
      backdrop-filter: blur(16px) saturate(1.4);
      -webkit-backdrop-filter: blur(16px) saturate(1.4);
      box-shadow: 0 4px 24px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,220,100,.08);
      transition: border-color .2s, box-shadow .2s;
    }
    .school-card-bg {
      position: absolute; inset: 0;
      background-size: cover; background-position: center;
      opacity: 0.28;
      transition: opacity .3s;
      border-radius: 16px;
    }
    .school-card:hover .school-card-bg { opacity: 0.42; }
    .school-card:hover { border-color: rgba(201,162,39,.45); box-shadow: 0 8px 36px rgba(0,0,0,.5); }
    .school-card > *:not(.school-card-bg) { position: relative; z-index: 1; }
    .school-badge {
      display: inline-flex; align-items: center; justify-content: center;
      width: 44px; height: 44px; border-radius: 12px;
      font-size: .75rem; font-weight: 800; color: #fff; margin-bottom: 16px;
    }
    .sb-navy   { background: linear-gradient(135deg, #1a2744, #2a4a8a); }
    .sb-teal   { background: linear-gradient(135deg, #0d5a3a, #1aa37a); }
    .sb-gold   { background: linear-gradient(135deg, #7a5a00, #c9a227); }
    .sb-purple { background: linear-gradient(135deg, #3a1a6a, #7b3fbf); }
    .school-code      { font-size: .92rem; font-weight: 700; color: #fff; margin-bottom: 4px; }
    .school-full-name { font-size: .75rem; color: rgba(255,255,255,.65); line-height: 1.4; margin-bottom: 12px; font-weight: 300; }
    .school-tags      { display: flex; flex-wrap: wrap; gap: 5px; }
    .s-tag {
      font-size: .62rem; font-weight: 500; padding: 3px 8px; border-radius: 999px;
      background: rgba(255,255,255,.12); color: rgba(255,255,255,.85); border: 1px solid rgba(255,255,255,.15);
    }

    /* ── TIMELINE ── */
    .timeline-wrap { padding: 80px 0; }
    .timeline-inner {
      max-width: 1200px; margin: 0 auto; padding: 0 40px;
      display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: start;
    }
    .timeline-inner .section-label { color: var(--gold); }
    .timeline-inner .section-title { color: #fff; }
    .journey { position: relative; padding-left: 20px; margin-top: 32px; }
    .journey-line {
      position: absolute; left: 9px; top: 10px; bottom: 10px; width: 2px;
      background: linear-gradient(to bottom, var(--gold), rgba(201,162,39,.06));
    }
    .j-step { display: flex; align-items: flex-start; gap: 20px; padding-bottom: 28px; }
    .j-step:last-child { padding-bottom: 0; }
    .j-dot {
      width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0;
      margin-top: 2px; position: relative; z-index: 1;
      display: grid; place-items: center;
    }
    .j-dot.done {
      background: linear-gradient(135deg, var(--gold), var(--gold-dk));
      box-shadow: 0 0 0 4px rgba(201,162,39,.15);
    }
    .j-dot.pending { background: rgba(255,255,255,.12); border: 2px solid rgba(255,255,255,.3); }
    .j-check { font-size: .55rem; color: #0d1117; font-weight: 900; }
    .j-body   { flex: 1; }
    .j-title  { font-size: .92rem; font-weight: 600; color: #fff; margin-bottom: 3px; }
    .j-desc   { font-size: .8rem; color: rgba(255,255,255,.82); line-height: 1.5; font-weight: 300; }

    .cta-block {
      background: rgba(255,255,255,.08);
      backdrop-filter: blur(16px);
      border: 1px solid rgba(255,255,255,.15);
      border-radius: 20px; padding: 40px; align-self: center;
      position: relative; overflow: hidden;
    }
    .cta-block::before {
      content: ''; position: absolute; width: 240px; height: 240px; border-radius: 50%;
      background: radial-gradient(circle, rgba(201,162,39,.18), transparent 65%);
      bottom: -60px; right: -60px;
    }
    .cta-block h3 {
      font-family: 'Playfair Display', serif;
      font-size: 1.6rem; font-weight: 700; color: #fff;
      margin-bottom: 12px; letter-spacing: -.02em;
    }
    .cta-block p {
      font-size: .85rem; color: rgba(255,255,255,.5); line-height: 1.7;
      margin-bottom: 28px; font-weight: 300;
    }
    .cta-btns { display: flex; gap: 10px; flex-wrap: wrap; }
    .cbtn-gold {
      padding: 12px 22px; border-radius: 10px;
      background: var(--gold); color: #0d1117;
      font-size: .85rem; font-weight: 700; text-decoration: none;
      transition: opacity .15s; cursor: pointer; border: none; font-family: inherit;
    }
    .cbtn-gold:hover { opacity: .85; }
    .cbtn-ghost {
      padding: 12px 22px; border-radius: 10px;
      background: rgba(255,255,255,.08); color: rgba(255,255,255,.75);
      border: 1px solid rgba(255,255,255,.12);
      font-size: .85rem; font-weight: 600; text-decoration: none;
      transition: background .15s; cursor: pointer; font-family: inherit;
    }
    .cbtn-ghost:hover { background: rgba(255,255,255,.14); }

    /* ── FOOTER ── */
    .footer-wrap { border-top: 1px solid rgba(255,255,255,.12); }
    footer {
      max-width: 1200px; margin: 0 auto; padding: 28px 40px;
      display: flex; align-items: center; justify-content: space-between;
    }
    .footer-logo {
      font-family: 'Playfair Display', serif;
      font-size: .9rem; font-weight: 700; color: rgba(255,255,255,.85);
    }
    .footer-copy { font-size: .75rem; color: rgba(255,255,255,.55); font-weight: 300; }
    .footer-links { display: flex; gap: 20px; }
    .footer-links a { font-size: .75rem; color: rgba(255,255,255,.55); text-decoration: none; cursor: pointer; }
    .footer-links a:hover { color: var(--gold); }

    /* ── MODAL OVERLAY ── */
    .modal-overlay {
      display: none;
      position: fixed; inset: 0; z-index: 999;
      background: rgba(13,17,23,.45);
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .modal-overlay.active { display: flex; }

    /* ── MODAL CARD ── */
    .modal-card {
      background: #fff;
      border-radius: 20px;
      width: 100%;
      max-width: 420px;
      padding: 36px 32px 32px;
      box-shadow: 0 24px 80px rgba(0,0,0,.22);
      position: relative;
      animation: modal-in .2s ease;
    }
    @keyframes modal-in {
      from { opacity:0; transform: translateY(10px) scale(.98); }
      to   { opacity:1; transform: translateY(0) scale(1); }
    }
    .modal-close {
      position: absolute; top: 16px; right: 16px;
      width: 30px; height: 30px; border-radius: 50%;
      background: rgba(0,0,0,.06); border: none; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem; color: var(--ink-3);
      transition: background .15s;
    }
    .modal-close:hover { background: rgba(0,0,0,.12); }

    /* modal logo */
    .modal-logo {
      display: flex; align-items: center; gap: 10px; margin-bottom: 24px;
    }
    .modal-logo-mark {
      width: 32px; height: 32px; border-radius: 9px;
      background: linear-gradient(135deg, var(--navy), var(--navy-2));
      display: grid; place-items: center;
      font-size: .65rem; font-weight: 700; color: var(--gold); letter-spacing: .04em;
    }
    .modal-logo span {
      font-family: 'Playfair Display', serif;
      font-size: .88rem; font-weight: 700; color: var(--navy);
    }

    /* modal headings */
    .modal-card h2 {
      font-family: 'Playfair Display', serif;
      font-size: 1.5rem; font-weight: 800; color: var(--navy);
      margin-bottom: 4px;
    }
    .modal-card .modal-sub {
      font-size: .82rem; color: var(--ink-3); margin-bottom: 24px;
    }

    /* modal form */
    .modal-field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }
    .modal-field label {
      font-size: .72rem; font-weight: 600; text-transform: uppercase;
      letter-spacing: .06em; color: var(--ink-3);
    }
    .modal-field input,
    .modal-field select {
      padding: 10px 14px;
      border: 1.5px solid rgba(0,0,0,.12);
      border-radius: 10px;
      font-size: .88rem; font-family: inherit; color: var(--ink);
      background: #fff;
      outline: none;
      transition: border-color .15s;
      width: 100%;
    }
    .modal-field input:focus,
    .modal-field select:focus { border-color: var(--navy); }

    .modal-submit {
      width: 100%; padding: 12px;
      background: var(--navy); color: #fff;
      border: none; border-radius: 10px;
      font-size: .92rem; font-weight: 600; font-family: inherit;
      cursor: pointer; margin-top: 6px;
      transition: opacity .15s, transform .15s;
    }
    .modal-submit:hover { opacity: .88; transform: translateY(-1px); }

    .modal-switch {
      text-align: center; margin-top: 16px;
      font-size: .82rem; color: var(--ink-3);
    }
    .modal-switch a {
      color: var(--navy); font-weight: 700; cursor: pointer; text-decoration: none;
    }
    .modal-switch a:hover { text-decoration: underline; }

    .modal-alert {
      padding: 10px 14px; border-radius: 8px; font-size: .82rem;
      margin-bottom: 14px;
    }
    .modal-alert.error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .modal-alert.success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

    /* ── RESPONSIVE ── */
    @media (max-width: 960px) {
      .schools-inner { grid-template-columns: 1fr; }
      .timeline-inner { grid-template-columns: 1fr; }
      .nav-inner { padding: 0 20px; }
    }
    @media (max-width: 600px) {
      .schools-grid { grid-template-columns: 1fr; }
      .nav-inner, .hero, .section { padding-left: 20px; padding-right: 20px; }
      .schools-inner, .timeline-inner { padding: 60px 20px; }
      .hero { padding: 60px 20px; }
      footer { flex-direction: column; gap: 14px; text-align: center; padding: 24px 20px; }
      .modal-card { padding: 28px 20px 24px; }
    }
  </style>
</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-inner">
    <a class="nav-logo" href="index.php">
      <img src="assets/images/logo-horizontal.png" alt="TRX-Yerner University" style="height:36px;width:auto;object-fit:contain;">
    </a>
    <div class="nav-links">
      <?php if ($loggedIn): ?>
        <a class="nav-link" href="<?= e($home) ?>">Dashboard</a>
        <a class="nav-btn" href="logout.php">Sign Out</a>
      <?php else: ?>
        <button class="nav-link" onclick="openModal('login')">Sign In</button>
        <button class="nav-btn"  onclick="openModal('register')">Register</button>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- HERO -->
<div class="hero">
  <div>
    <div class="hero-eyebrow">
      <span class="dot-live"></span>
      Official Portal · <?= date('Y') ?>–<?= date('Y')+1 ?>
    </div>
    <h1>Your <em>university,</em><br>all in one place.</h1>
    <p class="hero-sub">Courses, grades, activities, and announcements — all in one secure student portal built for the TRX-Yerner community.</p>
    <div class="hero-actions">
      <button class="btn-primary" onclick="openModal('register')">Create Account</button>
      <button class="btn-outline" onclick="openModal('login')">Sign In</button>
    </div>
  </div>
</div>

<!-- SCHOOLS -->
<div class="schools-wrap">
  <div class="schools-inner">
    <div>
      <div class="section-label">Schools &amp; Programs</div>
      <h2 class="section-title">Four schools,<br>one campus.</h2>
      <p class="section-sub">TRX-Yerner University offers programs across engineering, arts, business, and senior high school.</p>
    </div>
    <div class="schools-grid">
      <?php
      $schools = [
        ['SECA','sb-navy',  'Engineering, Computing & Arts',      ['BSIT','BSCS','BSCompEngr','BSCivilEng','BSArchi'], 'assets/images/card-seca.png'],
        ['SASE','sb-teal',  'Arts, Science & Education',          ['Psychology','Education'],                          'assets/images/card-sase.png'],
        ['SBMA','sb-gold',  'Business, Management & Accountancy', ['Accountancy','Tourism'],                           'assets/images/card-sbma.png'],
        ['SHS', 'sb-purple','Senior High School',                 ['ABM','STEM','HUMSS'],                              'assets/images/card-shs.png'],
      ];
      foreach ($schools as [$code,$cls,$name,$programs,$img]):
      ?>
      <div class="school-card">
        <div class="school-card-bg" style="background-image:url('<?= e($img) ?>')"></div>
        <div class="school-badge <?= $cls ?>"><?= e($code) ?></div>
        <div class="school-code"><?= e($code) ?></div>
        <div class="school-full-name"><?= e($name) ?></div>
        <div class="school-tags">
          <?php foreach ($programs as $p): ?><span class="s-tag"><?= e($p) ?></span><?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- HOW IT WORKS + CTA -->
<div class="timeline-wrap">
  <div class="timeline-inner">
    <div>
      <div class="section-label">Getting Started</div>
      <h2 class="section-title">Up and running<br>in five steps.</h2>
      <div class="journey">
        <div class="journey-line"></div>
        <?php
        $steps = [
          ['Create Account', 'Fill out the registration form with your details.',    true],
          ['Await Approval', 'An admin reviews and approves your account.',          true],
          ['Receive Email',  "You'll get an email notification when approved.",      true],
          ['Sign In',        'Log in to access your personalized portal.',           false],
          ['Use the Portal', 'View courses, grades, activities & announcements.',    false],
        ];
        foreach ($steps as [$t,$d,$done]):
        ?>
        <div class="j-step">
          <div class="j-dot <?= $done ? 'done' : 'pending' ?>">
            <?php if ($done): ?><span class="j-check">✓</span><?php endif; ?>
          </div>
          <div class="j-body">
            <div class="j-title"><?= e($t) ?></div>
            <div class="j-desc"><?= e($d) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="cta-block">
      <h3>Ready to get started?</h3>
      <p>Register your student account today. Admin approval is required before your first login to keep the community secure and verified.</p>
      <div class="cta-btns">
        <button class="cbtn-gold" onclick="openModal('register')">Register Now</button>
        <button class="cbtn-ghost" onclick="openModal('login')">Sign In</button>
      </div>
    </div>
  </div>
</div>

<!-- FOOTER -->
<div class="footer-wrap">
  <footer>
    <div class="footer-logo">TRX-Yerner University</div>
    <div class="footer-copy">© <?= date('Y') ?> TRX-Yerner University. All rights reserved.</div>
    <div class="footer-links">
      <a onclick="openModal('register')">Register</a>
      <a onclick="openModal('login')">Sign In</a>
    </div>
  </footer>
</div>

<!-- ══════════════ SIGN IN MODAL ══════════════ -->
<div class="modal-overlay" id="modal-login" onclick="overlayClick(event,'login')">
  <div class="modal-card">
    <button class="modal-close" onclick="closeModal('login')">✕</button>
    <div class="modal-logo">
      <img src="assets/images/logo-horizontal.png" alt="TRX-Yerner University" style="height:40px;width:auto;object-fit:contain;filter:brightness(0) invert(1);">
    </div>
    <h2>Welcome back</h2>
    <p class="modal-sub">Sign in to access your portal</p>

    <div id="login-error" class="modal-alert error" style="display:none;"></div>

    <form method="post" action="login.php">
      <div class="modal-field">
        <label>Email address</label>
        <input name="email" type="email" placeholder="you@university.edu" required autocomplete="email">
      </div>
      <div class="modal-field">
        <label>Password</label>
        <input name="password" type="password" placeholder="••••••••" required autocomplete="current-password">
      </div>
      <button type="submit" class="modal-submit">Enter Portal →</button>
    </form>

    <div class="modal-switch">
      Don't have an account?
      <a onclick="switchModal('login','register')">Register here</a>
    </div>
  </div>
</div>

<!-- ══════════════ REGISTER MODAL ══════════════ -->
<div class="modal-overlay" id="modal-register" onclick="overlayClick(event,'register')">
  <div class="modal-card" style="max-width:460px;max-height:90vh;overflow-y:auto;">
    <button class="modal-close" onclick="closeModal('register')">✕</button>
    <div class="modal-logo">
      <img src="assets/images/logo-horizontal.png" alt="TRX-Yerner University" style="height:40px;width:auto;object-fit:contain;filter:brightness(0) invert(1);">
    </div>
    <h2>Create Account</h2>
    <p class="modal-sub">Fill in your details to get started</p>

    <form method="post" action="register.php">
      <div class="modal-field">
        <label>Account Type</label>
        <select name="account_type" id="reg-account-type" onchange="toggleStudentFields()">
          <option value="member">Member / Client</option>
          <option value="student">Student</option>
        </select>
      </div>
      <div class="modal-field">
        <label>Full Name</label>
        <input name="name" required placeholder="Juan dela Cruz">
      </div>
      <div class="modal-field">
        <label>Email</label>
        <input type="email" name="email" required placeholder="you@email.com">
      </div>
      <div class="modal-field">
        <label>School / Organization</label>
        <select name="company" id="reg-school" onchange="updatePrograms()">
          <option value="SECA">SECA – Engineering, Computing &amp; Arts</option>
          <option value="SASE">SASE – Arts, Science &amp; Education</option>
          <option value="SBMA">SBMA – Business, Management &amp; Accountancy</option>
          <option value="SHS">SHS – Senior High School</option>
        </select>
      </div>

      <!-- Student-only fields -->
      <div id="student-fields">
        <div class="modal-field">
          <label>Program</label>
          <select name="program" id="reg-program"></select>
        </div>
        <div class="modal-field">
          <label>Year Level</label>
          <input name="year_level" placeholder="1" type="number" min="1" max="6">
        </div>
      </div>

      <div class="modal-field">
        <label>Phone (optional)</label>
        <input name="phone" placeholder="+63 9XX XXX XXXX">
      </div>
      <div class="modal-field">
        <label>Password</label>
        <input type="password" name="password" required placeholder="Min 6 characters">
      </div>
      <div class="modal-field">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required placeholder="Repeat password">
      </div>

      <button type="submit" class="modal-submit">Create Account</button>
    </form>

    <div class="modal-switch">
      Already have an account?
      <a onclick="switchModal('register','login')">Sign in here</a>
    </div>
  </div>
</div>

<script>
  /* ── Modal controls ── */
  function openModal(name) {
    document.getElementById('modal-' + name).classList.add('active');
    document.body.style.overflow = 'hidden';
  }
  function closeModal(name) {
    document.getElementById('modal-' + name).classList.remove('active');
    document.body.style.overflow = '';
  }
  function switchModal(from, to) {
    closeModal(from);
    setTimeout(() => openModal(to), 150);
  }
  function overlayClick(e, name) {
    if (e.target === document.getElementById('modal-' + name)) closeModal(name);
  }
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      closeModal('login');
      closeModal('register');
      document.body.style.overflow = '';
    }
  });

  /* ── Program options per school ── */
  const programs = {
    SECA: ['BSIT','BSCS','BSCompEngr','BSCivilEng','BSArchi'],
    SASE: ['Psychology','Education'],
    SBMA: ['Accountancy','Tourism'],
    SHS:  ['ABM','STEM','HUMSS'],
  };
  function updatePrograms() {
    const school  = document.getElementById('reg-school').value;
    const sel     = document.getElementById('reg-program');
    sel.innerHTML = '';
    (programs[school] || []).forEach(p => {
      const o = document.createElement('option');
      o.value = o.textContent = p;
      sel.appendChild(o);
    });
  }
  function toggleStudentFields() {
    const isStudent = document.getElementById('reg-account-type').value === 'student';
    document.getElementById('student-fields').style.display = isStudent ? '' : 'none';
  }
  updatePrograms();
  toggleStudentFields();

  /* ── Auto-open modal from URL param ── */
  const urlModal = new URLSearchParams(window.location.search).get('modal');
  if (urlModal === 'login' || urlModal === 'register') openModal(urlModal);

  /* ── Scroll animations ── */
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.style.opacity = '1';
        e.target.style.transform = 'translateY(0)';
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.school-card, .j-step').forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = `opacity .5s ${i * 0.06}s ease, transform .5s ${i * 0.06}s ease`;
    observer.observe(el);
  });
</script>
<script src="assets/js/app.js"></script>
</body>
</html>
