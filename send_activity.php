<?php
require 'config.php';
require 'partials.php';
require 'academic_options.php';
require 'mail_config.php';
require_admin();

// ── Auto-add pdf_path column if missing ──
try {
    $conn->exec("ALTER TABLE activities ADD COLUMN pdf_path VARCHAR(255) NULL");
} catch (PDOException $e) { /* already exists */ }

// ── Ensure upload folder exists ──
$upload_dir = __DIR__ . '/uploads/activities/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

// ── Ensure sent_activity_emails table exists ──
$conn->exec("CREATE TABLE IF NOT EXISTS sent_activity_emails(
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    student_id INT NOT NULL,
    recipient_email VARCHAR(120) NOT NULL,
    subject VARCHAR(180) NOT NULL,
    status ENUM('preview','sent','failed') DEFAULT 'preview',
    error_message TEXT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    FOREIGN KEY(student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$courses = $conn->query('SELECT id,code,title FROM courses')->fetchAll();

// ════════════════════════════════
// DELETE ACTIVITY
// ════════════════════════════════
if (isset($_GET['delete'])) {
    $del = $conn->prepare('SELECT pdf_path FROM activities WHERE id=?');
    $del->execute([(int)$_GET['delete']]);
    $del_row = $del->fetch();
    if (!empty($del_row['pdf_path']) && file_exists(__DIR__ . '/' . $del_row['pdf_path'])) {
        unlink(__DIR__ . '/' . $del_row['pdf_path']);
    }
    $conn->prepare('DELETE FROM activities WHERE id=?')->execute([(int)$_GET['delete']]);
    redirect('send_activity.php');
}

// ════════════════════════════════
// LOAD EDIT DATA
// ════════════════════════════════
$edit = null;
if (isset($_GET['id'])) {
    $s = $conn->prepare('SELECT * FROM activities WHERE id=?');
    $s->execute([(int)$_GET['id']]);
    $edit = $s->fetch();
}

// ════════════════════════════════
// SAVE ACTIVITY (CREATE / UPDATE)
// ════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_activity'])) {
    $course_id    = (int)$_POST['course_id'];
    $title        = trim($_POST['title']);
    $instructions = trim($_POST['instructions']);
    $due_date     = trim($_POST['due_date']);
    $points       = (int)($_POST['points'] ?? 100);

    $pdf_path = $edit['pdf_path'] ?? null;

    if (!empty($_FILES['pdf_file']['name'])) {
        $file = $_FILES['pdf_file'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext === 'pdf' && $file['size'] <= 20 * 1024 * 1024) {
            if (!empty($edit['pdf_path']) && file_exists(__DIR__ . '/' . $edit['pdf_path'])) {
                unlink(__DIR__ . '/' . $edit['pdf_path']);
            }
            $filename = 'act_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $pdf_path = 'uploads/activities/' . $filename;
            }
        } else {
            flash('error', 'PDF only, max 20 MB.');
            redirect('send_activity.php' . (!empty($_POST['id']) ? '?id=' . (int)$_POST['id'] : '?new=1'));
        }
    }

    if (!empty($_POST['remove_pdf']) && !empty($edit['pdf_path'])) {
        if (file_exists(__DIR__ . '/' . $edit['pdf_path'])) unlink(__DIR__ . '/' . $edit['pdf_path']);
        $pdf_path = null;
    }

    if (!empty($_POST['id'])) {
        $conn->prepare('UPDATE activities SET course_id=?,title=?,description=?,due_date=?,max_score=?,pdf_path=? WHERE id=?')
             ->execute([$course_id, $title, $instructions, $due_date, $points, $pdf_path, (int)$_POST['id']]);
        flash('success', 'Activity updated.');
    } else {
        $conn->prepare('INSERT INTO activities(course_id,title,description,due_date,max_score,pdf_path) VALUES(?,?,?,?,?,?)')
             ->execute([$course_id, $title, $instructions, $due_date, $points, $pdf_path]);
        flash('success', 'Activity created.');
    }
    redirect('send_activity.php');
}

// ════════════════════════════════
// SEND TO STUDENTS
// ════════════════════════════════
$program = $_POST['program'] ?? '';
$preview = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_mode'])) {
    if ($_POST['send_mode'] === 'load') {
        $actid_for_load = (int)$_POST['activity_id'];
        $s = $conn->prepare('
            SELECT DISTINCT s.*
            FROM students s
            JOIN enrollments e ON e.student_id = s.id
            JOIN activities a ON a.course_id = e.course_id
            WHERE a.id = ?
            ORDER BY s.full_name ASC
        ');
        $s->execute([$actid_for_load]);
        $preview = $s->fetchAll();
    } elseif ($_POST['send_mode'] === 'send') {
        $ids   = $_POST['student_ids'] ?? [];
        $actid = (int)$_POST['activity_id'];
        $a     = $conn->prepare('SELECT a.*, c.code FROM activities a JOIN courses c ON c.id=a.course_id WHERE a.id=?');
        $a->execute([$actid]);
        $act = $a->fetch();
        $prev = 0;
        foreach ($ids as $sid) {
            $s = $conn->prepare('SELECT * FROM students WHERE id=?');
            $s->execute([(int)$sid]);
            $stu = $s->fetch();
            $sub = 'New Activity: ' . $act['title'];
            $conn->prepare('INSERT INTO sent_activity_emails(activity_id,student_id,recipient_email,subject,status) VALUES(?,?,?,?,?)')
                 ->execute([$actid, $stu['id'], $stu['email'], $sub, 'preview']);
            $prev++;
        }
        flash('success', "Processed. Preview logged: $prev");
        redirect('send_activity.php');
    }
}

// ════════════════════════════════
// FETCH ALL ACTIVITIES
// ════════════════════════════════
$rows = $conn->query(
    'SELECT activities.*, courses.code course_code, courses.title course_title, courses.program
     FROM activities
     JOIN courses ON courses.id = activities.course_id
     ORDER BY activities.id DESC'
)->fetchAll();

$courses_in_use = [];
foreach ($rows as $r) {
    $key = $r['course_code'];
    if (!isset($courses_in_use[$key]))
        $courses_in_use[$key] = $r['course_code'] . ' – ' . $r['course_title'];
}
ksort($courses_in_use);

$due_buckets = [
    'overdue'   => 'Overdue',
    'this_week' => 'Due This Week',
    'next_week' => 'Due Next Week',
    'later'     => 'Due Later',
    'no_date'   => 'No Due Date',
];

// Fetch all acts for send dropdown
$acts = $conn->query('SELECT a.*, c.code, c.program FROM activities a JOIN courses c ON c.id=a.course_id')->fetchAll();

// Fetch logs
$logs = $conn->query(
    'SELECT l.*, a.title activity_title, s.full_name
     FROM sent_activity_emails l
     JOIN activities a ON a.id = l.activity_id
     JOIN students s ON s.id = l.student_id
     ORDER BY l.sent_at DESC LIMIT 50'
)->fetchAll();

// Pre-select send activity if ?send=id
$send_preselect = (int)($_GET['send'] ?? 0);

page_header('Activities', 'send_activity');

$err = flash('error');
$ok  = flash('success');
?>

<?php if ($err): ?>
<div class="flash flash-error">⚠️ <?= e($err) ?></div>
<?php endif; ?>
<?php if ($ok): ?>
<div class="flash flash-ok">✅ <?= e($ok) ?></div>
<?php endif; ?>

<!-- ══════════════════════════════════════════
     SECTION 1 — ADD / EDIT ACTIVITY FORM
════════════════════════════════════════════ -->
<section class="panel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-4);">
        <h2 style="margin:0;"><?= $edit ? '✏️ Edit Activity' : '➕ Add Activity' ?></h2>
        <?php if (!$edit): ?>
            <a class="btn" href="send_activity.php?new=1">+ New Activity</a>
        <?php else: ?>
            <a class="btn light" href="send_activity.php">← Cancel Edit</a>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['new']) || $edit): ?>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="save_activity" value="1">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="form-grid">
            <label>Course
                <select name="course_id">
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (($edit['course_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                        <?= e($c['code'] . ' - ' . $c['title']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Title
                <input name="title" value="<?= e($edit['title'] ?? '') ?>" required>
            </label>
            <label>Due Date
                <input type="datetime-local" name="due_date" value="<?= e($edit['due_date'] ?? '') ?>">
            </label>
            <label>Points
                <input type="number" name="points" value="<?= e($edit['max_score'] ?? 100) ?>">
            </label>
            <label style="grid-column:1/-1;">Instructions
                <textarea name="instructions"><?= e($edit['description'] ?? '') ?></textarea>
            </label>
            <label style="grid-column:1/-1;">
                📄 Attach PDF <span style="font-weight:400;font-size:.82rem;color:var(--ink-40);">(optional · PDF only · max 20 MB)</span>
                <input type="file" name="pdf_file" accept="application/pdf" style="margin-top:6px;">
                <?php if (!empty($edit['pdf_path'])): ?>
                <div style="margin-top:8px;display:flex;align-items:center;gap:12px;">
                    <a href="<?= e($edit['pdf_path']) ?>" target="_blank" style="font-size:.85rem;">📄 View current PDF</a>
                    <label style="font-size:.82rem;color:#c00;cursor:pointer;font-weight:400;">
                        <input type="checkbox" name="remove_pdf" value="1"> Remove PDF
                    </label>
                </div>
                <?php endif; ?>
            </label>
        </div>
        <br>
        <button class="btn" name="save_activity"><?= $edit ? 'Update Activity' : 'Create Activity' ?></button>
    </form>
    <?php else: ?>
    <p style="color:var(--ink-40);font-size:.9rem;">Click <strong>+ New Activity</strong> to create one, or click <strong>Edit</strong> on any card below.</p>
    <?php endif; ?>
</section>

<!-- ══════════════════════════════════════════
     SECTION 2 — ACTIVITY CARDS
════════════════════════════════════════════ -->
<?php if (empty($rows)): ?>
<section class="panel">
    <div class="empty-state">
        <div style="font-size:3rem;">📝</div>
        <p>No activities yet. Create one above!</p>
    </div>
</section>
<?php else: ?>

<div class="search-filter-bar">
    <input type="text" id="sfSearch" class="sf-search" placeholder="Search by title…">
    <select id="sfCourse" data-filter-attr="course">
        <option value="">All Courses</option>
        <?php foreach ($courses_in_use as $code => $label): ?>
        <option value="<?= e(strtolower($code)) ?>"><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>
    <select id="sfDue" data-filter-attr="due">
        <option value="">All Due Dates</option>
        <?php foreach ($due_buckets as $k => $v): ?>
        <option value="<?= $k ?>"><?= $v ?></option>
        <?php endforeach; ?>
    </select>
    <select id="sfPdf" data-filter-attr="haspdf">
        <option value="">All</option>
        <option value="yes">📄 Has PDF</option>
        <option value="no">No PDF</option>
    </select>
    <button class="sf-reset" id="sfReset">Reset</button>
    <span class="sf-count" id="sfCount"></span>
</div>

<div class="card-grid" id="cardGrid">
<?php
$now      = new DateTime();
$week_end = (clone $now)->modify('+7 days');
$next_end = (clone $now)->modify('+14 days');

foreach ($rows as $r):
    $due_bucket = 'no_date';
    if (!empty($r['due_date'])) {
        try {
            $due = new DateTime($r['due_date']);
            if ($due < $now)           $due_bucket = 'overdue';
            elseif ($due <= $week_end) $due_bucket = 'this_week';
            elseif ($due <= $next_end) $due_bucket = 'next_week';
            else                       $due_bucket = 'later';
        } catch (Exception $e) { $due_bucket = 'no_date'; }
    }
    $has_pdf = !empty($r['pdf_path']) ? 'yes' : 'no';
?>
<div class="record-card <?= school_card_class($r['program'] ?? '') ?>"
     data-search="<?= e(strtolower($r['title'] . ' ' . $r['course_code'] . ' ' . $r['course_title'])) ?>"
     data-course="<?= e(strtolower($r['course_code'])) ?>"
     data-due="<?= $due_bucket ?>"
     data-haspdf="<?= $has_pdf ?>">
    <div class="record-card-header">
        <?php $sch = strtolower(school_for_program($r['program'] ?? '')); ?>
        <div class="record-card-avatar school-logo">
            <img src="assets/images/logo-<?= e($sch ?: 'seca') ?>.png" alt="<?= e(strtoupper($sch)) ?>">
        </div>
        <div>
            <div class="record-card-title">
                <?= e($r['title']) ?>
                <?php if (!empty($r['pdf_path'])): ?>
                <span class="pdf-badge">📄 PDF</span>
                <?php endif; ?>
            </div>
            <div class="record-card-sub"><span class="tag"><?= e($r['course_code']) ?></span></div>
        </div>
    </div>
    <div class="record-card-body">
        <div class="record-card-field">
            <label>Due Date</label>
            <span><?= e($r['due_date'] ?? 'No due date') ?></span>
        </div>
        <div class="record-card-field">
            <label>Points</label>
            <span><?= e($r['max_score'] ?? 100) ?> pts</span>
        </div>
        <?php if (!empty($r['description'])): ?>
        <div class="record-card-field" style="grid-column:1/-1;">
            <label>Instructions</label>
            <span><?= e(mb_strimwidth($r['description'], 0, 100, '...')) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($r['pdf_path'])): ?>
        <div class="record-card-field" style="grid-column:1/-1;">
            <label>PDF</label>
            <a href="<?= e($r['pdf_path']) ?>" target="_blank" class="pdf-link">📄 View / Download</a>
        </div>
        <?php endif; ?>
    </div>
    <div class="record-card-actions">
        <a class="btn light"   href="send_activity.php?id=<?= $r['id'] ?>">Edit</a>
        <a class="btn"         href="send_activity.php?send=<?= $r['id'] ?>#send-section">Send →</a>
        <a class="btn danger"  href="send_activity.php?delete=<?= $r['id'] ?>"
           onclick="return confirm('Delete this activity?')">Delete</a>
    </div>
</div>
<?php endforeach; ?>
<div class="sf-no-results" id="sfNoResults">😕 No activities match your search.</div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════
     SECTION 3 — SEND TO STUDENTS
════════════════════════════════════════════ -->
<section class="panel" id="send-section">
    <h2 style="margin-bottom:var(--sp-4);">📨 Send Activity to Students</h2>
    <form method="post">
        <input type="hidden" name="send_mode" value="load">
        <div class="form-grid">
            <label>Program
                <select name="program"><?= program_options_html($program) ?></select>
            </label>
            <label>Activity
                <select name="activity_id">
                    <?php foreach ($acts as $a): ?>
                    <option value="<?= $a['id'] ?>"
                        <?= ($send_preselect && $send_preselect == $a['id']) ? 'selected' : (isset($_POST['activity_id']) && $_POST['activity_id'] == $a['id'] ? 'selected' : '') ?>>
                        <?= e($a['code'] . ' - ' . $a['title']) ?>
                        <?php if (!empty($a['pdf_path'])): ?>📄<?php endif; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <br>
        <button class="btn">Load Students</button>
    </form>
</section>

<?php if ($preview): ?>
<section class="panel">
    <h3 style="margin-bottom:var(--sp-3);">Select Students to Notify</h3>
    <form method="post">
        <input type="hidden" name="send_mode" value="send">
        <input type="hidden" name="activity_id" value="<?= e($_POST['activity_id']) ?>">
        <div class="checklist">
            <?php foreach ($preview as $s): ?>
            <label>
                <input type="checkbox" name="student_ids[]" value="<?= $s['id'] ?>" checked>
                <?= e($s['full_name'] . ' · ' . $s['email']) ?>
            </label>
            <?php endforeach; ?>
        </div>
        <br>
        <button class="btn">Send / Preview Log</button>
    </form>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════
     SECTION 4 — SEND LOGS
════════════════════════════════════════════ -->
<section class="panel">
    <h2 style="margin-bottom:var(--sp-4);">
        Send Logs
        <span class="pill" style="margin-left:var(--sp-3);"><?= count($logs) ?></span>
    </h2>

    <?php if (empty($logs)): ?>
    <div class="empty-state">
        <div style="font-size:2rem;">📋</div>
        <p>No activity emails logged yet.</p>
    </div>
    <?php else: ?>
    <div class="search-filter-bar" style="margin-bottom:var(--sp-4);">
        <input type="text" id="logSearch" class="sf-search" placeholder="Search by student or activity…">
        <select id="logStatus" data-filter-attr="status">
            <option value="">All Statuses</option>
            <option value="preview">Preview</option>
            <option value="sent">Sent</option>
            <option value="failed">Failed</option>
        </select>
        <button class="sf-reset" id="logReset">Reset</button>
        <span class="sf-count" id="logCount"></span>
    </div>
    <div class="table-wrap">
        <table id="logsTable">
            <thead>
                <tr>
                    <th>Student</th><th>Activity</th><th>Email</th><th>Status</th><th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $l): ?>
                <tr data-search="<?= e(strtolower($l['full_name'] . ' ' . $l['activity_title'] . ' ' . $l['recipient_email'])) ?>"
                    data-status="<?= e(strtolower($l['status'])) ?>">
                    <td><?= e($l['full_name']) ?></td>
                    <td><?= e($l['activity_title']) ?></td>
                    <td><?= e($l['recipient_email']) ?></td>
                    <td><span class="badge badge-<?= e($l['status']) ?>"><?= e($l['status']) ?></span></td>
                    <td><?= e($l['sent_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="sf-no-results" id="logNoResults" style="display:none;text-align:center;padding:var(--sp-8);color:var(--ink-40);">
        😕 No logs match.
    </div>
    <?php endif; ?>
</section>

<style>
.flash { padding: 10px 18px; border-radius: 8px; margin-bottom: 14px; font-weight: 500; }
.flash-error { background: #fee2e2; color: #991b1b; }
.flash-ok    { background: #dcfce7; color: #166534; }
.pdf-badge {
    display: inline-block; font-size: .72rem;
    background: #e8f4fd; color: #1a6eb5;
    border: 1px solid #b3d4f0; border-radius: 4px;
    padding: 1px 6px; margin-left: 6px; vertical-align: middle; font-weight: 600;
}
.pdf-link { color: #1a6eb5; font-size: .85rem; text-decoration: none; }
.pdf-link:hover { text-decoration: underline; }
</style>

<script>
// ── Activity cards filter ──
(function () {
    var search  = document.getElementById('sfSearch');
    var selects = document.querySelectorAll('[data-filter-attr]');
    var cards   = document.querySelectorAll('#cardGrid .record-card');
    var count   = document.getElementById('sfCount');
    var noRes   = document.getElementById('sfNoResults');
    var reset   = document.getElementById('sfReset');
    if (!search) return;

    function run() {
        var q = search.value.toLowerCase().trim();
        var filters = {};
        selects.forEach(function(s){ filters[s.dataset.filterAttr] = s.value.toLowerCase(); });
        var vis = 0;
        cards.forEach(function(c) {
            var show = !q || (c.dataset.search||'').includes(q);
            if (show) for (var a in filters)
                if (filters[a] && (c.dataset[a]||'') !== filters[a]) { show=false; break; }
            c.style.display = show ? '' : 'none';
            if (show) vis++;
        });
        count.textContent = vis + ' of ' + cards.length + ' record' + (cards.length!==1?'s':'');
        noRes.classList.toggle('visible', vis===0);
    }
    search.addEventListener('input', run);
    selects.forEach(function(s){ s.addEventListener('change', run); });
    reset.addEventListener('click', function(){ search.value=''; selects.forEach(function(s){s.value='';}); run(); });
    run();
})();

// ── Logs filter ──
(function () {
    var search = document.getElementById('logSearch');
    var statusSel = document.getElementById('logStatus');
    var rows = document.querySelectorAll('#logsTable tbody tr');
    var count = document.getElementById('logCount');
    var noRes = document.getElementById('logNoResults');
    var reset = document.getElementById('logReset');
    if (!search) return;
    var total = rows.length;
    function run() {
        var q = search.value.toLowerCase().trim();
        var st = statusSel ? statusSel.value.toLowerCase() : '';
        var vis = 0;
        rows.forEach(function(tr) {
            var show = (!q||(tr.dataset.search||'').includes(q)) && (!st||(tr.dataset.status||'')===st);
            tr.style.display = show ? '' : 'none';
            if (show) vis++;
        });
        if (count) count.textContent = vis + ' of ' + total + ' log' + (total!==1?'s':'');
        if (noRes) noRes.style.display = (vis===0&&(q||st)) ? 'block' : 'none';
    }
    search.addEventListener('input', run);
    if (statusSel) statusSel.addEventListener('change', run);
    if (reset) reset.addEventListener('click', function(){ search.value=''; if(statusSel)statusSel.value=''; run(); });
    run();
})();

// ── Auto-scroll to send section if ?send= param ──
<?php if ($send_preselect): ?>
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('send-section');
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
});
<?php endif; ?>
</script>

<?php page_footer(); ?>
