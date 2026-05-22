<?php
function brand_name() {
    global $APP_NAME;
    return $APP_NAME;
}

/* ── INLINE SVG ICON HELPER (offline safe) ── */
function nav_icon($name) {
    $icons = [
        'dashboard'     => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1.5" fill="currentColor" opacity=".9"/><rect x="9" y="1" width="6" height="6" rx="1.5" fill="currentColor" opacity=".6"/><rect x="1" y="9" width="6" height="6" rx="1.5" fill="currentColor" opacity=".6"/><rect x="9" y="9" width="6" height="6" rx="1.5" fill="currentColor" opacity=".9"/></svg>',
        'approvals'     => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M5 8l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'courses'       => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
        'lessons'       => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M3 2h7l3 3v9a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1z" stroke="currentColor" stroke-width="1.5"/><path d="M10 2v3h3M5 7h6M5 10h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
        'activities'    => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M1 8h3l2-5 3 10 2-5 1 2h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'submissions'   => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M3 2h7l3 3v9a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1z" stroke="currentColor" stroke-width="1.5"/><path d="M8 6v5M6 9l2 2 2-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'students'      => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.5"/><path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
        'programs'      => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M8 1l2.5 5H14l-4 3 1.5 5L8 11.5 4.5 14 6 9 2 6h3.5L8 1z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>',
        'send'          => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M13.5 2.5L1.5 7l5 1.5M13.5 2.5L9 14l-2.5-5.5M13.5 2.5L6.5 8.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'users'         => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="5.5" cy="5" r="2.5" stroke="currentColor" stroke-width="1.5"/><path d="M1 14c0-2.761 2.015-4 4.5-4s4.5 1.239 4.5 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="11.5" cy="5" r="2" stroke="currentColor" stroke-width="1.5" opacity=".6"/><path d="M14 12c0-1.5-.9-3-2.5-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".6"/></svg>',
        'profile'       => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><circle cx="8" cy="6" r="2.5" stroke="currentColor" stroke-width="1.3"/><path d="M3.5 13c0-2.485 2.015-4 4.5-4s4.5 1.515 4.5 4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>',
        'logout'        => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M10 11l3-3-3-3M13 8H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'home'          => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M2 7l6-5 6 5v7a1 1 0 01-1 1H3a1 1 0 01-1-1V7z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M6 14V9h4v5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'grades'        => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M5.5 8l1.5 1.5L10 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'professors'    => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="4.5" r="2.5" stroke="currentColor" stroke-width="1.5"/><path d="M2 14c0-2.761 2.686-4.5 6-4.5s6 1.739 6 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M11 8.5l1.5 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
        'announcements' => '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M2 10V6l10-4v12L2 10z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M2 10v3l2-1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    ];
    return $icons[$name] ?? '<svg width="14" height="14" viewBox="0 0 16 16"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>';
}

/* ── SHARED <head> ── */
function _head($title) {
    global $APP_NAME;
    echo '<!doctype html><html lang="en"><head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . e($title) . ' | ' . e($APP_NAME) . '</title>';
    echo '<link rel="stylesheet" href="assets/css/style.css">';
    echo '<link rel="icon" type="image/png" href="assets/images/favicon.png">';
    echo '</head><body>';
}

/* ── ADMIN SIDEBAR HELPER ── */
function _render_sidebar($links, $active, $nav_label = 'Main Menu') {
    // Detect if grouped (numeric array with 'items' key)
    $is_grouped = isset($links[0]) && is_array($links[0]) && isset($links[0]['items']);
    ?>
    <aside class="sidebar">
      <div class="brand">
        <img src="assets/images/logo-horizontal.png" alt="TRX-Yerner University"
             style="height:38px;width:auto;object-fit:contain;filter:brightness(0) invert(1);">
      </div>
      <nav class="nav">
        <?php if ($is_grouped): ?>
          <?php foreach ($links as $group): ?>
            <div class="nav-group-label"><?= e($group['label']) ?></div>
            <?php foreach ($group['items'] as $k => [$label, $icon]): ?>
            <a class="<?= $active === $k ? 'active' : '' ?>" href="<?= $k ?>.php">
              <span class="nav-icon"><?= nav_icon($icon) ?></span>
              <?= e($label) ?>
            </a>
            <?php endforeach; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <?php foreach ($links as $k => [$label, $icon]): ?>
          <a class="<?= $active === $k ? 'active' : '' ?>" href="<?= $k ?>.php">
            <span class="nav-icon"><?= nav_icon($icon) ?></span>
            <?= e($label) ?>
          </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </nav>
      <div class="nav-bottom">
      </div>
    </aside>
    <?php
}

/* ── ADMIN HEADER ── */
function page_header($title, $active = 'dashboard') {
    require_admin();
    _head($title);
    $links = [
        [
            'label' => 'Overview',
            'items' => [
                'dashboard' => ['Dashboard', 'dashboard'],
            ],
        ],
        [
            'label' => 'Academic',
            'items' => [
                'courses'    => ['Courses',    'courses'],
                'lessons'    => ['Lessons',    'lessons'],
                'activities' => ['Activities', 'activities'],
            ],
        ],
        [
            'label' => 'Students',
            'items' => [
                'students'            => ['Students',   'students'],
                'students_by_program' => ['By Program', 'programs'],
            ],
        ],
        [
            'label' => 'Submissions',
            'items' => [
                'submissions' => ['Submissions', 'submissions'],
            ],
        ],
        [
            'label' => 'System',
            'items' => [
                'pending_approvals' => ['Pending Approvals', 'approvals'],
                'users'             => ['Users',              'users'],
            ],
        ],
    ];
    ?>
    <?php
    global $conn;
    $uid         = (int)($_SESSION['user_id'] ?? 0);
    $unread      = 0;
    $st = $conn->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $st->execute([$uid]);
    $unread = (int)$st->fetchColumn();
    ?>
    <div class="layout">
      <?php _render_sidebar($links, $active, 'Main Menu'); ?>
      <main class="main">
        <div class="page-header">
          <div>
            <div class="page-title"><?= e($title) ?></div>
          </div>
          <div class="ph-actions">

            <!-- Bell -->
            <div class="notif-wrap" id="notifWrap">
              <button class="ph-icon-btn notif-bell-btn" id="notifBtn" title="Notifications">
                <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                  <path d="M8 1.5A4.5 4.5 0 003.5 6v2.5L2 10h12l-1.5-1.5V6A4.5 4.5 0 008 1.5z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                  <path d="M6.5 11.5a1.5 1.5 0 003 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <?php if ($unread > 0): ?>
                <span class="notif-badge"><?= min($unread, 99) ?></span>
                <?php endif; ?>
              </button>
              <div class="notif-dropdown" id="notifDropdown" hidden>
                <div class="notif-hdr">
                  <span>Notifications</span>
                  <button class="notif-mark-all" id="notifMarkAll">Mark all read</button>
                </div>
                <div class="notif-list" id="notifList">
                  <div class="notif-empty">Loading…</div>
                </div>
              </div>
            </div>

            <!-- Profile -->
            <a href="profile.php" class="ph-icon-btn" title="Profile"><?= nav_icon('profile') ?></a>

            <!-- Logout -->
            <a href="logout.php" class="ph-icon-btn" title="Logout"><?= nav_icon('logout') ?></a>

          </div>
        </div>
        <?php if ($m = flash('success')): ?>
        <div class="alert alert-success">✓ <?= e($m) ?></div>
        <?php endif; ?>
    <?php
}

/* ── STUDENT HEADER ── */
function student_header($title, $active = 'student_dashboard') {
    require_login();
    _head($title);
    $links = [
        'student_dashboard'  => ['My Home',     'home'],
        'student_courses'    => ['My Courses',  'courses'],
        'student_grades'     => ['My Grades',   'grades'],
        'student_professors' => ['Professors',  'professors'],
    ];
    ?>
    <div class="layout">
      <?php _render_sidebar($links, $active, 'Student Portal'); ?>
      <main class="main">
        <div class="page-header">
          <div>
            <div class="page-title"><?= e($title) ?></div>
          </div>
        </div>
        <?php if ($m = flash('success')): ?>
        <div class="alert alert-success">✓ <?= e($m) ?></div>
        <?php endif; ?>
    <?php
}

/* ── MEMBER HEADER ── */
function member_header($title, $active = 'member_dashboard') {
    require_login();
    _head($title);
    $links = [
        'member_dashboard' => ['Home',    'home'],
        'member_courses'   => ['Courses', 'courses'],
    ];
    ?>
    <div class="layout">
      <?php _render_sidebar($links, $active, 'Member Area'); ?>
      <main class="main">
        <div class="page-header">
          <div class="page-title"><?= e($title) ?></div>
        </div>
    <?php
}

/* ── FOOTER ── */
function page_footer() {
    echo '<script src="assets/js/app.js"></script>';
    echo '<script src="assets/js/notifications.js"></script>';
    echo '</main></div></body></html>';
}

/* ── ICLOUD-STYLE HEADER (student portal, no sidebar) ── */
function icloud_header($title) {
    global $APP_NAME;
    require_login();

    $uid  = (int)($_SESSION['user_id'] ?? 0);
    $role = $_SESSION['user_role'] ?? 'student';

    echo '<!doctype html><html lang="en"><head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . e($title) . ' | ' . e($APP_NAME) . '</title>';
    echo '<link rel="stylesheet" href="assets/css/style.css">';
    echo '<link rel="icon" type="image/png" href="assets/images/favicon.png">';
    echo '<style>
/* ═══════════════════════════════════════════
   GLOBAL INPUT / SELECT / TEXTAREA FIX
   Applies to ALL pages: admin, member, student
═══════════════════════════════════════════ */

/* Base state — Deep Navy */
input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):not([type="range"]):not([type="submit"]):not([type="button"]):not([type="reset"]),
select,
textarea {
  background-color: #0f1e35 !important;
  color: #ffffff !important;
  border: 1px solid rgba(255,255,255,.18) !important;
  border-radius: 8px !important;
  padding: .42rem .7rem !important;
  font-size: .82rem !important;
  outline: none !important;
  transition: background-color .2s, border-color .2s, box-shadow .2s !important;
  -webkit-text-fill-color: #ffffff !important;
}

/* Placeholder text */
input::placeholder,
textarea::placeholder {
  color: rgba(255,255,255,.38) !important;
  opacity: 1 !important;
}

/* Hover state — Slate Blue */
input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):not([type="range"]):not([type="submit"]):not([type="button"]):not([type="reset"]):hover,
select:hover,
textarea:hover {
  background-color: #1e2a3a !important;
  border-color: rgba(255,255,255,.3) !important;
}

/* Focus / Active state — Frosted Blue glow */
input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):not([type="range"]):not([type="submit"]):not([type="button"]):not([type="reset"]):focus,
select:focus,
textarea:focus {
  background-color: rgba(15,30,60,.92) !important;
  border-color: rgba(96,165,250,.7) !important;
  box-shadow: 0 0 0 3px rgba(59,130,246,.2) !important;
  -webkit-text-fill-color: #ffffff !important;
}

/* Select dropdown options */
select option {
  background-color: #0f1e35 !important;
  color: #ffffff !important;
}

/* Autofill fix (Chrome/Edge make it white) */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
input:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0 999px #0f1e35 inset !important;
  -webkit-text-fill-color: #ffffff !important;
  caret-color: #ffffff !important;
}

/* File input button */
input[type="file"]::file-selector-button {
  background: rgba(255,255,255,.1) !important;
  border: 1px solid rgba(255,255,255,.2) !important;
  color: #fff !important;
  border-radius: 6px !important;
  padding: .3rem .65rem !important;
  font-size: .75rem !important;
  cursor: pointer !important;
  margin-right: .5rem !important;
  transition: background .15s !important;
}
input[type="file"]::file-selector-button:hover {
  background: rgba(255,255,255,.18) !important;
}

/* Number input spinner */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  filter: invert(1) opacity(.4);
}
</style>';
    echo '</head><body class="ic-page" style="
      background: linear-gradient(rgba(10,14,30,0.72), rgba(10,14,30,0.82)),
                  url(assets/images/student-bg.png) center/cover no-repeat fixed;
      min-height: 100vh;
    ">';

    echo '<nav class="ic-topnav">';
    echo '  <div class="ic-topnav-left">';
    echo '    <div class="ic-topnav-logo">';
    echo '      <img src="assets/images/logo-horizontal.png" alt="TRX-Yerner University" style="height:34px;width:auto;object-fit:contain;filter:brightness(0) invert(1);">';
    echo '    </div>';
    echo '  </div>';
    echo '  <div class="ic-topnav-right">';

    // Bell
    global $conn;
    $uid_n = (int)($_SESSION['user_id'] ?? 0);
    $st_n  = $conn->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $st_n->execute([$uid_n]);
    $unread_n = (int)$st_n->fetchColumn();
    $badge_n  = $unread_n > 0 ? '<span class="notif-badge">' . min($unread_n, 99) . '</span>' : '';
    echo '    <div class="notif-wrap" id="notifWrap">';
    echo '      <button class="ic-topnav-btn notif-bell-btn" id="notifBtn" title="Notifications" style="position:relative;">';
    echo '        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1.5A4.5 4.5 0 003.5 6v2.5L2 10h12l-1.5-1.5V6A4.5 4.5 0 008 1.5z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M6.5 11.5a1.5 1.5 0 003 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
    echo        $badge_n;
    echo '      </button>';
    echo '      <div class="notif-dropdown" id="notifDropdown" hidden>';
    echo '        <div class="notif-hdr"><span>Notifications</span><button class="notif-mark-all" id="notifMarkAll">Mark all read</button></div>';
    echo '        <div class="notif-list" id="notifList"><div class="notif-empty">Loading…</div></div>';
    echo '      </div>';
    echo '    </div>';

    echo '    <a href="profile.php" class="ic-topnav-btn" title="Profile">' . nav_icon('profile') . '</a>';
    echo '    <a href="logout.php"  class="ic-topnav-btn" title="Logout">'  . nav_icon('logout')  . '</a>';
    echo '  </div>';
    echo '</nav>';
}

/* ── ICLOUD-STYLE FOOTER ── */
function icloud_footer() {
    echo '<script src="assets/js/app.js"></script>';
    echo '<script src="assets/js/notifications.js"></script>';
    echo '</body></html>';
}

/* ── LEFT QUICK ACCESS SIDEBAR ── */
function render_left_quick_access($active = '') {
    $role = $_SESSION['user_role'] ?? 'student';

    $svg_home    = '<svg width="20" height="20" viewBox="0 0 16 16" fill="none"><path d="M2 7l6-5 6 5v7a1 1 0 01-1 1H3a1 1 0 01-1-1V7z" stroke="#fff" stroke-width="1.5" stroke-linejoin="round"/><path d="M6 14V9h4v5" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>';
    $svg_courses = '<svg width="20" height="20" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="#fff" stroke-width="1.5"/><path d="M5 6h6M5 9h4" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>';
    $svg_grades  = '<svg width="20" height="20" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="#fff" stroke-width="1.5"/><path d="M5.5 8l1.5 1.5L10 6" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    $svg_profs   = '<svg width="20" height="20" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="4.5" r="2.5" stroke="#fff" stroke-width="1.5"/><path d="M2 14c0-2.761 2.686-4.5 6-4.5s6 1.739 6 4.5" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>';
    $svg_subs    = '<svg width="20" height="20" viewBox="0 0 16 16" fill="none"><path d="M3 2h7l3 3v9a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1z" stroke="#fff" stroke-width="1.5"/><path d="M8 6v5M6 9l2 2 2-2" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    $svg_send    = '<svg width="20" height="20" viewBox="0 0 16 16" fill="none"><path d="M13.5 2.5L1.5 7l5 1.5M13.5 2.5L9 14l-2.5-5.5M13.5 2.5L6.5 8.5" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    $svg_profile = '<svg width="20" height="20" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="#fff" stroke-width="1.5"/><circle cx="8" cy="6" r="2.5" stroke="#fff" stroke-width="1.3"/><path d="M3.5 13c0-2.485 2.015-4 4.5-4s4.5 1.515 4.5 4" stroke="#fff" stroke-width="1.3" stroke-linecap="round"/></svg>';

    $svg_students = '<svg width="20" height="20" viewBox="0 0 16 16" fill="none"><circle cx="5" cy="5" r="2.2" stroke="#fff" stroke-width="1.4"/><circle cx="11" cy="5" r="2.2" stroke="#fff" stroke-width="1.4"/><path d="M1 13c0-2.209 1.79-3.5 4-3.5s4 1.291 4 3.5" stroke="#fff" stroke-width="1.4" stroke-linecap="round"/><path d="M11 9.5c1.5 0 4 .8 4 3.5" stroke="#fff" stroke-width="1.4" stroke-linecap="round"/></svg>';

    if ($role === 'member') {
        $items = [
            ['href'=>'member_dashboard.php',    'key'=>'member_dashboard',    'cls'=>'ic-icon-gold',  'bg'=>'',                                         'svg'=>$svg_home,     'label'=>'Home'],
            ['href'=>'member_courses.php',       'key'=>'member_courses',      'cls'=>'ic-icon-blue',  'bg'=>'',                                         'svg'=>$svg_courses,  'label'=>'Courses'],
            ['href'=>'member_students.php',      'key'=>'member_students',     'cls'=>'ic-icon-navy',  'bg'=>'',                                         'svg'=>$svg_students, 'label'=>'Students'],
            ['href'=>'member_submissions.php',   'key'=>'member_submissions',  'cls'=>'',              'bg'=>'linear-gradient(135deg,#16a34a,#15803d)',   'svg'=>$svg_subs,     'label'=>'Submissions'],
            ['href'=>'member_send_activity.php', 'key'=>'member_send_activity','cls'=>'',              'bg'=>'linear-gradient(135deg,#7c3aed,#5b21b6)',   'svg'=>$svg_send,     'label'=>'Activities'],
            ['href'=>'profile.php',              'key'=>'profile',             'cls'=>'ic-icon-coral', 'bg'=>'',                                         'svg'=>$svg_profile,  'label'=>'Profile'],
        ];
    } else {
        $items = [
            ['href'=>'student_dashboard.php',  'key'=>'student_dashboard',  'cls'=>'ic-icon-gold',  'bg'=>'', 'svg'=>$svg_home,    'label'=>'Home'],
            ['href'=>'student_courses.php',    'key'=>'student_courses',    'cls'=>'ic-icon-blue',  'bg'=>'', 'svg'=>$svg_courses, 'label'=>'Courses'],
            ['href'=>'student_grades.php',     'key'=>'student_grades',     'cls'=>'ic-icon-teal',  'bg'=>'', 'svg'=>$svg_grades,  'label'=>'Grades'],
            ['href'=>'student_professors.php', 'key'=>'student_professors', 'cls'=>'ic-icon-navy',  'bg'=>'', 'svg'=>$svg_profs,   'label'=>'Professors'],
            ['href'=>'profile.php',            'key'=>'profile',            'cls'=>'ic-icon-coral', 'bg'=>'', 'svg'=>$svg_profile, 'label'=>'Profile'],
        ];
    }

    echo '<style>.ic-body,.ic-body-solo{padding-left:calc(80px + 1.5rem)!important;}</style>';
    echo '<div class="ic-left-sidebar">';
    echo '<div class="ic-left-sidebar-inner">';
    foreach ($items as $item) {
        $is_active  = ($active === $item['key']);
        $icon_style = $item['bg'] ? ' style="background:' . $item['bg'] . ';"' : '';
        echo '<a href="' . $item['href'] . '" class="ic-left-app' . ($is_active ? ' ic-left-app-active' : '') . '">';
        echo '<div class="ic-app-icon ' . $item['cls'] . '"' . $icon_style . '>' . $item['svg'] . '</div>';
        echo '<span>' . $item['label'] . '</span>';
        echo '</a>';
    }
    echo '</div>';
    echo '</div>';
}

/* ── SCHOOL BANNER ── */
function render_school_banner($conn) {
    $uid = (int)($_SESSION['user_id'] ?? 0);
    $q = $conn->prepare('SELECT s.program, u.name, u.company FROM users u LEFT JOIN students s ON s.id = u.student_id WHERE u.id = ?');
    $q->execute([$uid]);
    $row = $q->fetch();
    if (!$row) return;

    $program_to_school = [
        'BSIT' => 'SECA', 'BSCS' => 'SECA', 'BSCompEngr' => 'SECA', 'BSCivilEng' => 'SECA', 'BSArchi' => 'SECA',
        'Psychology' => 'SASE', 'Education' => 'SASE',
        'Accountancy' => 'SBMA', 'Tourism' => 'SBMA',
        'ABM' => 'SHS', 'STEM' => 'SHS', 'HUMSS' => 'SHS',
    ];
    $school = $program_to_school[$row['program'] ?? '']
           ?? (in_array($row['company'] ?? '', ['SECA','SASE','SBMA','SHS']) ? $row['company'] : 'SECA');
    $school_themes = [
        'SECA' => ['color'=>'#1a2744','light'=>'#2a4a8a','img'=>'assets/images/card-seca.png','logo'=>'assets/images/logo-seca.png','label'=>'Engineering, Computing & Arts'],
        'SASE' => ['color'=>'#0d5a3a','light'=>'#1aa37a','img'=>'assets/images/card-sase.png','logo'=>'assets/images/logo-sase.png','label'=>'Arts, Science & Education'],
        'SBMA' => ['color'=>'#7a5a00','light'=>'#c9a227','img'=>'assets/images/card-sbma.png','logo'=>'assets/images/logo-sbma.png','label'=>'Business, Management & Accountancy'],
        'SHS'  => ['color'=>'#3a1a6a','light'=>'#7b3fbf','img'=>'assets/images/card-shs.png', 'logo'=>'assets/images/logo-shs.png', 'label'=>'Senior High School'],
    ];
    $theme = $school_themes[$school];
    $first_name = explode(' ', trim($row['name']))[0];

    echo '<style>.ic-body-solo { padding-right: calc(160px + 1rem) !important; }</style>';
    echo '<div style="position:fixed;top:62px;right:0;bottom:0;width:160px;z-index:99;background:linear-gradient(180deg,'.$theme['color'].','.$theme['light'].');overflow:hidden;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:16px;padding:24px 12px;text-align:center;">';
    echo '<div style="position:absolute;inset:0;background:url('.$theme['img'].') center/cover no-repeat;opacity:0.18;"></div>';
    echo '<div style="position:relative;z-index:1;width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.15);backdrop-filter:blur(6px);border:2px solid rgba(255,255,255,0.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;"><img src="'.$theme['logo'].'" alt="'.e($school).'" style="width:70px;height:70px;object-fit:contain;border-radius:50%;"></div>';
    echo '<div style="position:relative;z-index:1;flex-shrink:0;"><div style="font-size:.95rem;font-weight:800;color:#fff;line-height:1.2;margin-bottom:5px;">'.e($school).'</div><div style="font-size:.62rem;color:rgba(255,255,255,0.7);line-height:1.4;">'.e($theme['label']).'</div></div>';
    echo '<div style="position:relative;z-index:1;width:60px;height:1px;background:rgba(255,255,255,0.25);flex-shrink:0;"></div>';
    echo '<div style="position:relative;z-index:1;flex-shrink:0;"><div style="font-size:.62rem;color:rgba(255,255,255,0.55);margin-bottom:3px;">Welcome back,</div><div style="font-size:.88rem;font-weight:700;color:#fff;">'.e($first_name).'</div></div>';
    echo '</div>';
}
?>
