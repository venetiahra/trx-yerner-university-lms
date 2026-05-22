<?php
require 'config.php';
require 'partials.php';
page_header('Dashboard', 'dashboard');

// ── Stat counts ──────────────────────────────────────
$stat = [];
foreach (['courses','lessons','activities','students','users','professors'] as $t) {
    $stat[$t] = (int)$conn->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
}
$stat['submissions']       = (int)$conn->query("SELECT COUNT(*) FROM submissions")->fetchColumn();
$stat['enrollments']       = (int)$conn->query("SELECT COUNT(*) FROM enrollments WHERE status='enrolled'")->fetchColumn();
$stat['pending_approvals'] = (int)$conn->query("SELECT COUNT(*) FROM users WHERE status='pending'")->fetchColumn();
$stat['ungraded']          = (int)$conn->query("
    SELECT COUNT(*) FROM submissions s
    LEFT JOIN grades g ON g.student_id = s.student_id AND g.activity_id = s.activity_id
    WHERE g.id IS NULL
")->fetchColumn();

// ── Recent Submissions (latest 6) ────────────────────
$recent_subs = $conn->query("
    SELECT s.submitted_at, st.full_name student_name, a.title act_title,
           c.code course_code,
           CASE WHEN g.id IS NOT NULL THEN 'Graded' ELSE 'Pending' END AS grade_status
    FROM submissions s
    JOIN students st   ON st.id = s.student_id
    JOIN activities a  ON a.id  = s.activity_id
    JOIN courses c     ON c.id  = a.course_id
    LEFT JOIN grades g ON g.student_id = s.student_id AND g.activity_id = s.activity_id
    ORDER BY s.submitted_at DESC
    LIMIT 6
")->fetchAll();

// ── Pending Approvals (latest 5) ─────────────────────
$pending_users = $conn->query("
    SELECT id, name, email, role, created_at
    FROM users WHERE status = 'pending'
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();

// ── Recent Enrollments (latest 5) ────────────────────
$recent_enrollments = $conn->query("
    SELECT st.full_name, c.title course_title, c.code, e.enrolled_at
    FROM enrollments e
    JOIN students st ON st.id = e.student_id
    JOIN courses c   ON c.id  = e.course_id
    ORDER BY e.enrolled_at DESC
    LIMIT 5
")->fetchAll();
?>

<section class="hero" style="margin-bottom:28px;">
  <h1 style="font-size:1.8rem;margin-bottom:8px;"><?= e($APP_NAME) ?></h1>
  <p>Welcome back. Here's a live overview of your university portal.</p>
</section>

<!-- ── STAT CARDS ── -->
<div class="dash-stats">

  <a href="courses.php" class="dash-stat-card">
    <div class="dsc-icon">📖</div>
    <div class="dsc-label">Courses</div>
    <div class="dsc-value"><?= $stat['courses'] ?></div>
    <div class="dsc-sub">Total</div>
  </a>

  <a href="lessons.php" class="dash-stat-card">
    <div class="dsc-icon">📄</div>
    <div class="dsc-label">Lessons</div>
    <div class="dsc-value"><?= $stat['lessons'] ?></div>
    <div class="dsc-sub">Total</div>
  </a>

  <a href="activities.php" class="dash-stat-card">
    <div class="dsc-icon">⚡</div>
    <div class="dsc-label">Activities</div>
    <div class="dsc-value"><?= $stat['activities'] ?></div>
    <div class="dsc-sub">Total</div>
  </a>

  <a href="students.php" class="dash-stat-card">
    <div class="dsc-icon">🎓</div>
    <div class="dsc-label">Students</div>
    <div class="dsc-value"><?= $stat['students'] ?></div>
    <div class="dsc-sub">Total enrolled: <?= $stat['enrollments'] ?></div>
  </a>

  <a href="users.php" class="dash-stat-card">
    <div class="dsc-icon">👤</div>
    <div class="dsc-label">Users</div>
    <div class="dsc-value"><?= $stat['users'] ?></div>
    <div class="dsc-sub">Professors: <?= $stat['professors'] ?></div>
  </a>

  <a href="submissions.php" class="dash-stat-card <?= $stat['ungraded'] > 0 ? 'dsc-alert' : '' ?>">
    <div class="dsc-icon">📥</div>
    <div class="dsc-label">Submissions</div>
    <div class="dsc-value"><?= $stat['submissions'] ?></div>
    <div class="dsc-sub"><?= $stat['ungraded'] ?> ungraded</div>
  </a>

  <a href="pending_approvals.php" class="dash-stat-card <?= $stat['pending_approvals'] > 0 ? 'dsc-alert' : '' ?>">
    <div class="dsc-icon">⏳</div>
    <div class="dsc-label">Pending Approvals</div>
    <div class="dsc-value"><?= $stat['pending_approvals'] ?></div>
    <div class="dsc-sub">Awaiting review</div>
  </a>

</div>

<!-- ── BOTTOM SECTIONS ── -->
<div class="dash-sections">

  <!-- Recent Submissions -->
  <div class="dash-section">
    <div class="ds-header">
      <span class="ds-title">Recent Submissions</span>
      <a href="submissions.php" class="ds-link">See all →</a>
    </div>
    <?php if (empty($recent_subs)): ?>
      <div class="ds-empty">No submissions yet.</div>
    <?php else: ?>
      <table class="ds-table">
        <thead><tr><th>Student</th><th>Activity</th><th>Course</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($recent_subs as $r): ?>
          <tr>
            <td><?= e($r['student_name']) ?></td>
            <td><?= e($r['act_title']) ?></td>
            <td><span class="ds-badge"><?= e($r['course_code']) ?></span></td>
            <td><span class="ds-status <?= $r['grade_status'] === 'Graded' ? 'ds-graded' : 'ds-pending' ?>"><?= $r['grade_status'] ?></span></td>
            <td><?= date('M j, g:i a', strtotime($r['submitted_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <!-- Right column -->
  <div class="dash-col-right">

    <!-- Pending Approvals -->
    <div class="dash-section">
      <div class="ds-header">
        <span class="ds-title">Pending Approvals</span>
        <a href="pending_approvals.php" class="ds-link">See all →</a>
      </div>
      <?php if (empty($pending_users)): ?>
        <div class="ds-empty">No pending approvals. ✅</div>
      <?php else: ?>
        <?php foreach ($pending_users as $u): ?>
        <div class="ds-user-row">
          <div class="ds-avatar"><?= strtoupper(mb_substr($u['name'],0,1)) ?></div>
          <div class="ds-user-info">
            <div class="ds-user-name"><?= e($u['name']) ?></div>
            <div class="ds-user-meta"><?= e($u['email']) ?> · <?= ucfirst($u['role']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Recent Enrollments -->
    <div class="dash-section">
      <div class="ds-header">
        <span class="ds-title">Recent Enrollments</span>
      </div>
      <?php if (empty($recent_enrollments)): ?>
        <div class="ds-empty">No enrollments yet.</div>
      <?php else: ?>
        <?php foreach ($recent_enrollments as $e): ?>
        <div class="ds-enroll-row">
          <div class="ds-avatar"><?= strtoupper(mb_substr($e['full_name'],0,1)) ?></div>
          <div class="ds-user-info">
            <div class="ds-user-name"><?= e($e['full_name']) ?></div>
            <div class="ds-user-meta"><span class="ds-badge"><?= e($e['code']) ?></span> <?= e($e['course_title']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php page_footer(); ?>
