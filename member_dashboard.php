<?php
require 'config.php';
require 'partials.php';
require_member();

$uid = (int)($_SESSION['user_id'] ?? 0);

// ── Assigned courses ──
$q = $conn->prepare('
    SELECT c.* FROM course_professors cp
    JOIN courses c ON c.id = cp.course_id
    WHERE cp.professor_id = ?
    ORDER BY c.code ASC
');
$q->execute([$uid]);
$my_courses = $q->fetchAll();

$pending_count = 0;
$student_count = 0;

if ($my_courses) {
    $course_ids = implode(',', array_column($my_courses, 'id'));

    $pq = $conn->query("
        SELECT COUNT(*) c FROM submissions sub
        JOIN activities a ON a.id = sub.activity_id
        LEFT JOIN grades g ON g.student_id = sub.student_id AND g.activity_id = sub.activity_id
        WHERE a.course_id IN ($course_ids) AND (g.score IS NULL OR g.id IS NULL)
    ");
    $pending_count = (int)($pq->fetch()['c'] ?? 0);

    $sq = $conn->query("
        SELECT COUNT(DISTINCT e.student_id) c FROM enrollments e
        WHERE e.course_id IN ($course_ids) AND e.status = 'enrolled'
    ");
    $student_count = (int)($sq->fetch()['c'] ?? 0);
}

// ── Recent submissions (latest 4) ──
$recent_subs = [];
if ($my_courses) {
    $course_ids = implode(',', array_column($my_courses, 'id'));
    $rs = $conn->query("
        SELECT
            sub.id sub_id, sub.submitted_at,
            s.full_name student_name,
            a.title activity_title, a.max_score, a.course_id,
            c.code course_code,
            g.score
        FROM submissions sub
        JOIN students s ON s.id = sub.student_id
        JOIN activities a ON a.id = sub.activity_id
        JOIN courses c ON c.id = a.course_id
        LEFT JOIN grades g ON g.student_id = sub.student_id AND g.activity_id = sub.activity_id
        WHERE a.course_id IN ($course_ids)
        ORDER BY sub.submitted_at DESC
        LIMIT 4
    ");
    $recent_subs = $rs->fetchAll();
}

// ── My students (preview, 5 max) ──
$my_students = [];
if ($my_courses) {
    $course_ids = implode(',', array_column($my_courses, 'id'));
    $stq = $conn->query("
        SELECT DISTINCT
            s.id, s.full_name, s.student_no, s.program, s.year_level,
            GROUP_CONCAT(c.code ORDER BY c.code SEPARATOR ', ') AS course_codes
        FROM enrollments e
        JOIN students s ON s.id = e.student_id
        JOIN courses c ON c.id = e.course_id
        WHERE e.course_id IN ($course_ids) AND e.status = 'enrolled'
        GROUP BY s.id
        ORDER BY s.full_name ASC
        LIMIT 5
    ");
    $my_students = $stq->fetchAll();
}

icloud_header('Professor Portal');
render_school_banner($conn);
render_left_quick_access('member_dashboard');
?>

<div class="ic-body-solo">

  <!-- ── WELCOME BANNER ── -->
  <div class="ic-widget" style="overflow:hidden;">
    <div style="background:linear-gradient(135deg,var(--navy-lt),#0d1b2a);padding:var(--sp-7) var(--sp-6);border-radius:var(--r-xl);">
      <div style="font-size:1.5rem;font-weight:800;color:#fff;margin-bottom:var(--sp-2);">
        <?php
          $uname = $_SESSION['user_name'] ?? 'Professor';
          $fname = explode(' ', trim($uname))[0];
          echo 'Welcome back, ' . e($fname) . '!';
        ?>
      </div>
      <div style="font-size:.9rem;color:rgba(255,255,255,.45);">Here's an overview of your courses and student submissions.</div>
    </div>
  </div>

  <!-- ── STATS ROW ── -->
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:.75rem;">
    <div class="ic-widget" style="text-align:center;padding:var(--sp-5) var(--sp-4);">
      <div style="font-size:2rem;font-weight:800;color:#fff;line-height:1;"><?= count($my_courses) ?></div>
      <div style="font-size:.7rem;color:rgba(255,255,255,.45);margin-top:4px;text-transform:uppercase;letter-spacing:.06em;">My Courses</div>
    </div>
    <div class="ic-widget" style="text-align:center;padding:var(--sp-5) var(--sp-4);">
      <div style="font-size:2rem;font-weight:800;color:<?= $pending_count > 0 ? '#f59e0b' : '#fff' ?>;line-height:1;"><?= $pending_count ?></div>
      <div style="font-size:.7rem;color:rgba(255,255,255,.45);margin-top:4px;text-transform:uppercase;letter-spacing:.06em;">Pending Grades</div>
    </div>
    <div class="ic-widget" style="text-align:center;padding:var(--sp-5) var(--sp-4);">
      <div style="font-size:2rem;font-weight:800;color:#fff;line-height:1;"><?= $student_count ?></div>
      <div style="font-size:.7rem;color:rgba(255,255,255,.45);margin-top:4px;text-transform:uppercase;letter-spacing:.06em;">My Students</div>
    </div>
  </div>

  <!-- ── MY COURSES + RECENT SUBMISSIONS ── -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">

    <!-- My Courses -->
    <div class="ic-widget">
      <div class="ic-widget-header">
        <div class="ic-widget-icon ic-icon-blue">
          <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="#fff" stroke-width="1.5"/><path d="M5 6h6M5 9h4" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
        </div>
        <div>
          <div class="ic-widget-title">My Courses</div>
          <div class="ic-widget-sub"><?= count($my_courses) ?> assigned to you</div>
        </div>
        <a href="member_courses.php" class="ic-widget-link" style="margin-left:auto;font-size:.75rem;">See all →</a>
      </div>
      <div class="ic-widget-body" style="padding-top:var(--sp-2);">
        <?php if ($my_courses): ?>
          <?php foreach (array_slice($my_courses, 0, 4) as $c): ?>
          <a href="member_view_course.php?id=<?= $c['id'] ?>" class="ic-course-row-link">
            <div class="ic-course-row" style="flex:1;">
              <div class="ic-course-left">
                <div class="ic-course-dot ic-dot-active"></div>
                <div>
                  <div class="ic-course-name"><?= e($c['title']) ?></div>
                  <div class="ic-course-code"><?= e($c['code']) ?></div>
                </div>
              </div>
              <span class="ic-tag ic-tag-green">Active</span>
            </div>
          </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="ic-empty-state">
            <p style="font-size:.82rem;color:rgba(255,255,255,.35);">No courses assigned yet.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Recent Submissions -->
    <div class="ic-widget">
      <div class="ic-widget-header">
        <div class="ic-widget-icon" style="background:linear-gradient(135deg,#16a34a,#15803d);">
          <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><path d="M3 2h7l3 3v9a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1z" stroke="#fff" stroke-width="1.5"/><path d="M8 6v5M6 9l2 2 2-2" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div>
          <div class="ic-widget-title">Recent Submissions</div>
          <div class="ic-widget-sub">Latest student work</div>
        </div>
        <a href="member_submissions.php" class="ic-widget-link" style="margin-left:auto;font-size:.75rem;">See all →</a>
      </div>
      <div class="ic-widget-body" style="padding-top:var(--sp-2);">
        <?php if ($recent_subs): ?>
          <?php foreach ($recent_subs as $sub): ?>
          <a href="member_view_course.php?id=<?= $sub['course_id'] ?>" class="ic-course-row-link">
            <div style="padding:.55rem 0;border-bottom:1px solid rgba(255,255,255,.06);display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
              <div style="min-width:0;">
                <div style="font-size:.8rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($sub['student_name']) ?></div>
                <div style="font-size:.7rem;color:rgba(255,255,255,.4);"><?= e($sub['activity_title']) ?> · <?= e($sub['course_code']) ?></div>
              </div>
              <?php if ($sub['score'] !== null): ?>
                <span class="ic-tag ic-tag-green" style="flex-shrink:0;"><?= number_format($sub['score'], 2) ?>/<?= $sub['max_score'] ?></span>
              <?php else: ?>
                <span class="ic-tag" style="background:rgba(245,158,11,.18);color:#f59e0b;flex-shrink:0;">Ungraded</span>
              <?php endif; ?>
            </div>
          </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="ic-empty-state">
            <p style="font-size:.82rem;color:rgba(255,255,255,.35);">No submissions yet.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- ── MY STUDENTS WIDGET ── -->
  <div class="ic-widget" style="margin-bottom:.75rem;">
    <div class="ic-widget-header">
      <div class="ic-widget-icon ic-icon-navy">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><circle cx="5" cy="5" r="2.2" stroke="#fff" stroke-width="1.4"/><circle cx="11" cy="5" r="2.2" stroke="#fff" stroke-width="1.4"/><path d="M1 13c0-2.209 1.79-3.5 4-3.5s4 1.291 4 3.5" stroke="#fff" stroke-width="1.4" stroke-linecap="round"/><path d="M11 9.5c1.5 0 4 .8 4 3.5" stroke="#fff" stroke-width="1.4" stroke-linecap="round"/></svg>
      </div>
      <div>
        <div class="ic-widget-title">My Students</div>
        <div class="ic-widget-sub"><?= $student_count ?> enrolled across your courses</div>
      </div>
      <a href="member_students.php" class="ic-widget-link" style="margin-left:auto;font-size:.75rem;">See all →</a>
    </div>
    <div class="ic-widget-body" style="padding-top:var(--sp-2);">
      <?php if ($my_students): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.5rem;">
          <?php foreach ($my_students as $stu):
            $parts = explode(' ', trim($stu['full_name']));
            $ini   = strtoupper(substr($parts[0],0,1) . substr(end($parts),0,1));
          ?>
          <div style="display:flex;align-items:center;gap:.65rem;padding:.55rem .6rem;border-radius:10px;background:rgba(255,255,255,.04);">
            <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#1e3a5f,#2a5298);display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;color:#fff;flex-shrink:0;">
              <?= e($ini) ?>
            </div>
            <div style="min-width:0;">
              <div style="font-size:.8rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($stu['full_name']) ?></div>
              <div style="font-size:.7rem;color:rgba(255,255,255,.4);"><?= e($stu['course_codes']) ?><?= $stu['program'] ? ' · ' . e($stu['program']) : '' ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php if ($student_count > 5): ?>
          <div style="text-align:center;margin-top:.75rem;">
            <a href="member_students.php" style="font-size:.78rem;color:rgba(255,255,255,.4);text-decoration:none;">
              +<?= $student_count - 5 ?> more students — See all →
            </a>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="ic-empty-state">
          <p style="font-size:.82rem;color:rgba(255,255,255,.35);">No students enrolled in your courses yet.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<style>
.ic-course-row-link { display:block;text-decoration:none;color:inherit;border-radius:10px;transition:background 0.15s; }
.ic-course-row-link:hover { background:rgba(255,255,255,0.06); }
.ic-course-row-link .ic-course-row { width:100%; }
.ic-app { position: relative; }
</style>

<?php icloud_footer(); ?>
