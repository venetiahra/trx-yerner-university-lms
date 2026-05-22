<?php
require 'config.php';
require 'partials.php';
require 'academic_options.php';
require_admin();

// ── Ensure submissions table exists ──
$conn->exec("CREATE TABLE IF NOT EXISTS submissions (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    student_id       INT NOT NULL,
    activity_id      INT NOT NULL,
    file_path        VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) DEFAULT NULL,
    submitted_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_sub (student_id, activity_id),
    FOREIGN KEY (student_id)  REFERENCES students(id)  ON DELETE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── Save grade ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_grade'])) {
    $student_id  = (int)$_POST['student_id'];
    $activity_id = (int)$_POST['activity_id'];
    $score       = $_POST['score'] !== '' ? (float)$_POST['score'] : null;
    $remarks     = trim($_POST['remarks'] ?? '');
    $graded_by   = (int)$_SESSION['user_id'];

    // Upsert into grades
    $check = $conn->prepare('SELECT id FROM grades WHERE student_id=? AND activity_id=?');
    $check->execute([$student_id, $activity_id]);
    if ($check->fetch()) {
        $conn->prepare('
            UPDATE grades
            SET score=?, remarks=?, graded_by=?, graded_at=NOW(), updated_at=NOW()
            WHERE student_id=? AND activity_id=?
        ')->execute([$score, $remarks, $graded_by, $student_id, $activity_id]);
    } else {
        $conn->prepare('
            INSERT INTO grades (student_id, activity_id, score, remarks, graded_by, graded_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ')->execute([$student_id, $activity_id, $score, $remarks, $graded_by]);
    }

    flash('success', 'Grade saved.');

    // ── Notify student ──
    require_once __DIR__ . '/notif_helper.php';
    $act_q = $conn->prepare('
        SELECT a.title, a.max_score, c.code
        FROM activities a JOIN courses c ON c.id = a.course_id
        WHERE a.id = ?
    ');
    $act_q->execute([$activity_id]);
    $act_row = $act_q->fetch();

    $stu_q = $conn->prepare('SELECT u.id FROM users u WHERE u.student_id = ?');
    $stu_q->execute([$student_id]);
    $stu_uid = (int)$stu_q->fetchColumn();

    if ($act_row && $score !== null && $stu_uid) {
        notif_push($conn, $stu_uid, 'grade_posted',
            'Grade posted: ' . $act_row['title'],
            'You scored ' . $score . ' / ' . $act_row['max_score'] . ' in ' . $act_row['code'],
            'student_grades.php'
        );
    }

    $redir = 'submissions.php';
    if (!empty($_POST['filter_course']))   $redir .= '?course_id='   . (int)$_POST['filter_course'];
    if (!empty($_POST['filter_activity'])) $redir .= (!str_contains($redir,'?') ? '?' : '&') . 'activity_id=' . (int)$_POST['filter_activity'];
    redirect($redir);
}

// ── Filters ──
$filter_course   = (int)($_GET['course_id']   ?? 0);
$filter_activity = (int)($_GET['activity_id'] ?? 0);
$filter_status   = $_GET['status'] ?? '';   // 'graded' | 'ungraded' | ''

// ── Fetch submissions ──
$sql = '
    SELECT
        sub.id            sub_id,
        sub.student_id,
        sub.activity_id,
        sub.file_path,
        sub.original_filename,
        sub.submitted_at,
        s.full_name       student_name,
        s.student_no,
        a.title           activity_title,
        a.max_score,
        a.type            activity_type,
        c.id              course_id,
        c.code            course_code,
        c.title           course_title,
        c.program         course_program,
        g.score,
        g.remarks,
        g.graded_at
    FROM submissions sub
    JOIN students  s ON s.id = sub.student_id
    JOIN activities a ON a.id = sub.activity_id
    JOIN courses    c ON c.id = a.course_id
    LEFT JOIN grades g ON g.student_id = sub.student_id AND g.activity_id = sub.activity_id
    WHERE 1=1
';
$params = [];

if ($filter_course) {
    $sql .= ' AND c.id = ?';
    $params[] = $filter_course;
}
if ($filter_activity) {
    $sql .= ' AND a.id = ?';
    $params[] = $filter_activity;
}
if ($filter_status === 'graded') {
    $sql .= ' AND g.score IS NOT NULL';
} elseif ($filter_status === 'ungraded') {
    $sql .= ' AND (g.score IS NULL OR g.id IS NULL)';
}

$sql .= ' ORDER BY sub.submitted_at DESC';

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$submissions = $stmt->fetchAll();

// ── Dropdowns for filters ──
$courses    = $conn->query('SELECT id, code, title FROM courses ORDER BY code')->fetchAll();
$activities = $conn->query('
    SELECT a.id, a.title, c.code
    FROM activities a
    JOIN courses c ON c.id = a.course_id
    ORDER BY c.code, a.title
')->fetchAll();

page_header('Submissions', 'submissions');

$err = flash('error');
$ok  = flash('success');
?>

<?php if ($err): ?><div class="flash flash-error">⚠️ <?= e($err) ?></div><?php endif; ?>
<?php if ($ok):  ?><div class="flash flash-ok">✅ <?= e($ok) ?></div><?php endif; ?>

<section class="panel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-4);">
        <h2 style="margin:0;">📥 Student Submissions</h2>
        <span class="pill"><?= count($submissions) ?> submission<?= count($submissions) !== 1 ? 's' : '' ?></span>
    </div>

    <!-- Filters -->
    <form method="get" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:var(--sp-4);">
        <select name="course_id" onchange="this.form.submit()" style="flex:1;min-width:160px;">
            <option value="">All Courses</option>
            <?php foreach ($courses as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $filter_course == $c['id'] ? 'selected' : '' ?>>
                <?= e($c['code'] . ' — ' . $c['title']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <select name="activity_id" onchange="this.form.submit()" style="flex:1;min-width:160px;">
            <option value="">All Activities</option>
            <?php foreach ($activities as $a): ?>
            <option value="<?= $a['id'] ?>" <?= $filter_activity == $a['id'] ? 'selected' : '' ?>>
                <?= e($a['code'] . ' — ' . $a['title']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <select name="status" onchange="this.form.submit()" style="flex:0 0 140px;">
            <option value="">All Statuses</option>
            <option value="ungraded" <?= $filter_status === 'ungraded' ? 'selected' : '' ?>>⏳ Ungraded</option>
            <option value="graded"   <?= $filter_status === 'graded'   ? 'selected' : '' ?>>✅ Graded</option>
        </select>
        <a href="submissions.php" class="btn light">Reset</a>
    </form>

    <?php if (empty($submissions)): ?>
    <div class="empty-state">
        <div style="font-size:3rem;">📭</div>
        <p>No submissions yet.</p>
    </div>
    <?php else: ?>
    <div class="table-wrap">
        <table id="subTable">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Activity</th>
                    <th>Course</th>
                    <th>Submitted</th>
                    <th>File</th>
                    <th style="min-width:100px;">Score</th>
                    <th style="min-width:200px;">Remarks</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($submissions as $sub): ?>
            <?php
                $is_graded  = $sub['score'] !== null;
                $sub_school = 'school-' . strtolower(school_for_program($sub['course_program'] ?? ''));
            ?>
            <tr class="sub-row <?= e($sub_school) ?> <?= $is_graded ? 'row-graded' : 'row-ungraded' ?>">
                <td>
                    <div style="font-weight:600;"><?= e($sub['student_name']) ?></div>
                    <div style="font-size:.78rem;color:var(--ink-40);"><?= e($sub['student_no']) ?></div>
                </td>
                <td>
                    <div><?= e($sub['activity_title']) ?></div>
                    <div style="font-size:.78rem;color:var(--ink-40);">Max: <?= e($sub['max_score']) ?> pts</div>
                </td>
                <td><span class="tag"><?= e($sub['course_code']) ?></span></td>
                <td style="font-size:.82rem;white-space:nowrap;"><?= date('M d, Y', strtotime($sub['submitted_at'])) ?><br>
                    <span style="color:var(--ink-40);"><?= date('h:i A', strtotime($sub['submitted_at'])) ?></span>
                </td>
                <td>
                    <a href="<?= e($sub['file_path']) ?>" target="_blank" class="btn light" style="font-size:.78rem;padding:4px 10px;">
                        📎 <?= e(mb_strimwidth($sub['original_filename'] ?? 'View', 0, 20, '…')) ?>
                    </a>
                </td>
                <td>
                    <form method="post" class="grade-form">
                        <input type="hidden" name="save_grade"      value="1">
                        <input type="hidden" name="student_id"      value="<?= $sub['student_id'] ?>">
                        <input type="hidden" name="activity_id"     value="<?= $sub['activity_id'] ?>">
                        <input type="hidden" name="filter_course"   value="<?= $filter_course ?>">
                        <input type="hidden" name="filter_activity" value="<?= $filter_activity ?>">
                        <input type="number" name="score"
                               value="<?= $sub['score'] !== null ? e($sub['score']) : '' ?>"
                               min="0" max="<?= e($sub['max_score']) ?>"
                               step="0.01" placeholder="—"
                               style="width:75px;padding:5px 8px;border-radius:6px;border:1px solid var(--border);background:var(--surface);color:var(--ink);">
                        <div style="font-size:.72rem;color:var(--ink-40);margin-top:2px;">/ <?= e($sub['max_score']) ?></div>
                </td>
                <td>
                        <textarea name="remarks" rows="2"
                                  placeholder="Feedback (optional)…"
                                  style="width:100%;padding:5px 8px;border-radius:6px;border:1px solid var(--border);background:var(--surface);color:var(--ink);font-size:.82rem;resize:vertical;"><?= e($sub['remarks'] ?? '') ?></textarea>
                </td>
                <td>
                    <?php if ($is_graded): ?>
                        <span class="badge badge-sent" style="white-space:nowrap;">✅ Graded</span>
                        <div style="font-size:.72rem;color:var(--ink-40);margin-top:3px;">
                            <?= date('M d', strtotime($sub['graded_at'])) ?>
                        </div>
                    <?php else: ?>
                        <span class="badge" style="background:#fef3c7;color:#92400e;white-space:nowrap;">⏳ Pending</span>
                    <?php endif; ?>
                </td>
                <td>
                        <button type="submit" class="btn" style="white-space:nowrap;">
                            <?= $is_graded ? 'Update' : 'Grade' ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>

<style>
.flash { padding: 10px 18px; border-radius: 8px; margin-bottom: 14px; font-weight: 500; }
.flash-error { background: #fee2e2; color: #991b1b; }
.flash-ok    { background: #dcfce7; color: #166534; }
.grade-form  { display: contents; }
.row-graded  { opacity: .85; }
</style>

<?php page_footer(); ?>
