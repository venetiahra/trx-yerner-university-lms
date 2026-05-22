<?php
require 'config.php';
require 'partials.php';
require 'student_common.php';

$stu = require_student_record($conn);

/* ── School theme ── */
$program_to_school = [
    'BSIT' => 'SECA', 'BSCS' => 'SECA', 'BSCompEngr' => 'SECA', 'BSCivilEng' => 'SECA', 'BSArchi' => 'SECA',
    'Psychology' => 'SASE', 'Education' => 'SASE',
    'Accountancy' => 'SBMA', 'Tourism' => 'SBMA',
    'ABM' => 'SHS', 'STEM' => 'SHS', 'HUMSS' => 'SHS',
];
$school = $program_to_school[$stu['program'] ?? ''] ?? 'SECA';
$school_themes = [
    'SECA' => ['color'=>'#1a2744','light'=>'#2a4a8a','rgb'=>'26,39,68',  'img'=>'assets/images/card-seca.png','logo'=>'assets/images/logo-seca.png','label'=>'Engineering, Computing & Arts'],
    'SASE' => ['color'=>'#0d5a3a','light'=>'#1aa37a','rgb'=>'13,90,58',  'img'=>'assets/images/card-sase.png','logo'=>'assets/images/logo-sase.png','label'=>'Arts, Science & Education'],
    'SBMA' => ['color'=>'#7a5a00','light'=>'#c9a227','rgb'=>'122,90,0',  'img'=>'assets/images/card-sbma.png','logo'=>'assets/images/logo-sbma.png','label'=>'Business, Management & Accountancy'],
    'SHS'  => ['color'=>'#3a1a6a','light'=>'#7b3fbf','rgb'=>'58,26,106', 'img'=>'assets/images/card-shs.png', 'logo'=>'assets/images/logo-shs.png', 'label'=>'Senior High School'],
];
$theme = $school_themes[$school];

/* ── Courses ── */
$q_courses = $conn->prepare('
    SELECT c.* FROM enrollments e
    JOIN courses c ON c.id = e.course_id
    WHERE e.student_id = ?
    ORDER BY c.code ASC
');
$q_courses->execute([$stu['id']]);
$courses = $q_courses->fetchAll();

/* ── Recent grades ── */
$q_grades = $conn->prepare('
    SELECT
        c.code   AS course_code,
        a.title  AS activity_title,
        g.score,
        a.max_score
    FROM grades g
    JOIN activities a ON a.id = g.activity_id
    JOIN courses  c ON c.id = a.course_id
    WHERE g.student_id = ?
    ORDER BY g.created_at DESC
    LIMIT 4
');
$q_grades->execute([$stu['id']]);
$grades = $q_grades->fetchAll();

/* ── Professors ── */
$q_profs = $conn->prepare('
    SELECT DISTINCT
        u.name AS full_name,
        u.email,
        c.code
    FROM enrollments e
    JOIN courses c ON c.id = e.course_id
    JOIN course_professors cp ON cp.course_id = c.id
    JOIN professors p ON p.id = cp.professor_id
    JOIN users u ON u.id = p.user_id
    WHERE e.student_id = ?
');
$q_profs->execute([$stu['id']]);
$profs = $q_profs->fetchAll();

/* ── Counts ── */
$total_courses    = count($courses);
$total_profs      = count($profs);
$total_activities = $conn->prepare('SELECT COUNT(*) c FROM grades WHERE student_id = ?');
$total_activities->execute([$stu['id']]);
$total_activities = (int) $total_activities->fetch()['c'];

$avg_score_q = $conn->prepare('
    SELECT AVG(g.score / a.max_score * 100) avg
    FROM grades g
    JOIN activities a ON a.id = g.activity_id
    WHERE g.student_id = ?
      AND g.score IS NOT NULL
      AND a.max_score > 0
');
$avg_score_q->execute([$stu['id']]);
$avg_score = round((float)($avg_score_q->fetch()['avg'] ?? 0));

/* ── Profile photo ── */
$photo_q = $conn->prepare('SELECT profile_pic FROM users WHERE id = ?');
$photo_q->execute([$_SESSION['user_id']]);
$photo = $photo_q->fetch()['profile_pic'] ?? null;

/* ── Initials for avatar fallback ── */
$name_parts = explode(' ', trim($stu['full_name']));
$initials   = strtoupper(substr($name_parts[0], 0, 1) . substr(end($name_parts), 0, 1));

icloud_header('My Portal');
?>

<style>
  :root {
    --school-color: <?= $theme['color'] ?>;
    --school-light: <?= $theme['light'] ?>;
    --school-rgb:   <?= $theme['rgb'] ?>;
  }
  .ic-widget-link { color: var(--school-light) !important; }
  .ic-status-active { color: var(--school-light) !important; }
  .ic-app-active span { color: var(--school-light) !important; }
  .ic-app-icon.ic-icon-gold { background: linear-gradient(135deg, var(--school-color), var(--school-light)) !important; }
  .ic-profile-chip { background: rgba(var(--school-rgb), 0.15); color: var(--school-light); border: 1px solid rgba(var(--school-rgb), 0.3); }
</style>

<?php render_school_banner($conn); ?>

<?php render_left_quick_access('student_dashboard'); ?>
?>

<div class="ic-body">

  <!-- ── PROFILE CARD ── -->
  <div class="ic-profile-card" style="animation-delay:.05s">
    <div class="ic-avatar-wrap">
      <?php if ($photo): ?>
        <img src="uploads/<?= e($photo) ?>" alt="Profile photo" class="ic-avatar-img">
      <?php else: ?>
        <div class="ic-avatar-initials"><?= e($initials) ?></div>
      <?php endif; ?>
    </div>
    <div class="ic-profile-name"><?= e($stu['full_name']) ?></div>
    <div class="ic-profile-email"><?= e($stu['email'] ?? '') ?></div>
    <div class="ic-profile-chip">Student Portal</div>
    <div class="ic-profile-divider"></div>
    <div class="ic-profile-meta">
      <div class="ic-meta-row">
        <span>Student No.</span>
        <strong><?= e($stu['student_no'] ?? '—') ?></strong>
      </div>
      <div class="ic-meta-row">
        <span>Program</span>
        <strong><?= e($stu['program'] ?? '—') ?></strong>
      </div>
      <div class="ic-meta-row">
        <span>Year level</span>
        <strong><?= e($stu['year_level'] ?? '—') ?></strong>
      </div>
      <div class="ic-meta-row">
        <span>Status</span>
        <strong class="ic-status-active">Active</strong>
      </div>
    </div>
    <a href="profile.php" class="btn ic-profile-btn">Edit profile</a>
  </div>

  <!-- ── RIGHT COLUMN ── -->
  <div class="ic-right-col">

    <!-- COURSES WIDGET -->
    <div class="ic-widget" style="animation-delay:.1s">
      <div class="ic-widget-header">
        <div class="ic-widget-icon ic-icon-gold">
          <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="#fff" stroke-width="1.5"/><path d="M5 6h6M5 9h4" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
        </div>
        <div>
          <div class="ic-widget-title">My courses</div>
          <div class="ic-widget-sub"><?= $total_courses ?> enrolled this semester</div>
        </div>
        <a href="student_courses.php" class="ic-widget-link">See all →</a>
      </div>
      <!-- stat chips -->
      <div class="ic-stat-row">
        <div class="ic-stat-chip">
          <div class="ic-stat-val"><?= $total_courses ?></div>
          <div class="ic-stat-lbl">Courses</div>
        </div>
        <div class="ic-stat-chip">
          <div class="ic-stat-val"><?= $avg_score ?>%</div>
          <div class="ic-stat-lbl">Avg score</div>
        </div>
        <div class="ic-stat-chip">
          <div class="ic-stat-val"><?= $total_activities ?></div>
          <div class="ic-stat-lbl">Activities</div>
        </div>
      </div>
      <!-- course rows -->
      <div class="ic-widget-body">
        <?php if ($courses): ?>
          <?php foreach (array_slice($courses, 0, 4) as $c): ?>
          <a href="view_course.php?id=<?= $c['id'] ?>" class="ic-course-row ic-course-row-link">
            <div class="ic-course-left">
              <div class="ic-course-dot ic-dot-active"></div>
              <div>
                <div class="ic-course-name"><?= e($c['title']) ?></div>
                <div class="ic-course-code"><?= e($c['code']) ?></div>
              </div>
            </div>
            <span class="ic-tag ic-tag-blue">Active →</span>
          </a>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="ic-empty">No courses enrolled yet.</p>
        <?php endif; ?>
      </div>
      <?php if (count($courses) > 4): ?>
      <div class="ic-more-link"><a href="student_courses.php">···</a></div>
      <?php endif; ?>
    </div>

    <!-- GRADES + PROFESSORS (2-col) -->
    <div class="ic-two-col">

      <!-- GRADES WIDGET -->
      <div class="ic-widget" style="animation-delay:.15s">
        <div class="ic-widget-header">
          <div class="ic-widget-icon ic-icon-teal">
            <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="#fff" stroke-width="1.5"/><path d="M5.5 8l1.5 1.5L10 6" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div>
            <div class="ic-widget-title">My grades</div>
            <div class="ic-widget-sub">Recent scores</div>
          </div>
          <a href="student_grades.php" class="ic-widget-link">See all →</a>
        </div>
        <div class="ic-widget-body">
          <?php if ($grades): ?>
            <?php foreach ($grades as $g):
              $pct = $g['max_score'] > 0 ? (($g['score'] ?? 0) / $g['max_score'] * 100) : 0;
              $cls = $pct >= 85 ? 'ic-pill-hi' : ($pct >= 70 ? 'ic-pill-mid' : 'ic-pill-lo');
            ?>
            <div class="ic-grade-row">
              <div>
                <div class="ic-grade-title"><?= e($g['activity_title']) ?></div>
                <div class="ic-grade-sub"><?= e($g['course_code']) ?></div>
              </div>
              <span class="ic-grade-pill <?= $cls ?>">
                <?= e($g['score'] ?? '—') ?>/<?= e($g['max_score']) ?>
              </span>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="ic-empty">No grades yet.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- PROFESSORS WIDGET -->
      <div class="ic-widget" style="animation-delay:.2s">
        <div class="ic-widget-header">
          <div class="ic-widget-icon ic-icon-navy">
            <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="4.5" r="2.5" stroke="#fff" stroke-width="1.5"/><path d="M2 14c0-2.761 2.686-4.5 6-4.5s6 1.739 6 4.5" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
          </div>
          <div>
            <div class="ic-widget-title">My professors</div>
            <div class="ic-widget-sub"><?= $total_profs ?> assigned to you</div>
          </div>
          <a href="student_professors.php" class="ic-widget-link">See all →</a>
        </div>
        <div class="ic-widget-body">
          <?php if ($profs): ?>
            <?php foreach (array_slice($profs, 0, 4) as $p):
              $parts = explode(' ', trim($p['full_name']));
              $ini   = strtoupper(substr($parts[0],0,1) . substr(end($parts),0,1));
            ?>
            <div class="ic-course-row">
              <div class="ic-course-left">
                <div class="ic-prof-avatar"><?= e($ini) ?></div>
                <div>
                  <div class="ic-course-name"><?= e($p['full_name']) ?></div>
                  <div class="ic-course-code"><?= e($p['code']) ?></div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="ic-empty">No professors assigned yet.</p>
          <?php endif; ?>
        </div>
      </div>

    </div><!-- /ic-two-col -->


  </div><!-- /ic-right-col -->
</div><!-- /ic-body -->


<style>
.ic-course-row-link {
  display: flex;
  text-decoration: none;
  color: inherit;
  transition: background 0.15s;
}
.ic-course-row-link:hover {
  background: rgba(255,255,255,0.06);
  border-radius: 8px;
}
</style>

<?php icloud_footer(); ?>
