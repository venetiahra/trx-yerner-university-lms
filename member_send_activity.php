<?php
require 'config.php';
require 'partials.php';
require_member();

$uid = (int)($_SESSION['user_id'] ?? 0);

try { $conn->exec("ALTER TABLE activities ADD COLUMN pdf_path VARCHAR(255) NULL"); } catch (PDOException $e) {}
$upload_dir = __DIR__ . '/uploads/activities/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
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

$my_courses = [];
$my_course_ids = [];
$cq = $conn->prepare('
    SELECT c.id, c.code, c.title FROM course_professors cp
    JOIN courses c ON c.id = cp.course_id
    WHERE cp.professor_id = ?
    ORDER BY c.code ASC
');
$cq->execute([$uid]);
$my_courses    = $cq->fetchAll();
$my_course_ids = array_column($my_courses, 'id');

// DELETE
if (isset($_GET['delete']) && $my_course_ids) {
    $del = $conn->prepare('SELECT pdf_path, course_id FROM activities WHERE id = ?');
    $del->execute([(int)$_GET['delete']]);
    $del_row = $del->fetch();
    if ($del_row && in_array($del_row['course_id'], $my_course_ids)) {
        if (!empty($del_row['pdf_path']) && file_exists(__DIR__ . '/' . $del_row['pdf_path']))
            unlink(__DIR__ . '/' . $del_row['pdf_path']);
        $conn->prepare('DELETE FROM activities WHERE id = ?')->execute([(int)$_GET['delete']]);
        flash('success', 'Activity deleted.');
    }
    redirect('member_send_activity.php');
}

// LOAD EDIT
$edit = null;
if (isset($_GET['id'])) {
    $s = $conn->prepare('SELECT * FROM activities WHERE id = ?');
    $s->execute([(int)$_GET['id']]);
    $e = $s->fetch();
    if ($e && in_array($e['course_id'], $my_course_ids)) $edit = $e;
}

// SAVE ACTIVITY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_activity'])) {
    $course_id    = (int)$_POST['course_id'];
    $title        = trim($_POST['title']);
    $instructions = trim($_POST['instructions']);
    $due_date     = trim($_POST['due_date']);
    $points       = max(1, (int)($_POST['points'] ?? 100));
    $pdf_path     = $edit['pdf_path'] ?? null;

    if (!in_array($course_id, $my_course_ids)) {
        flash('error', 'You can only post to your own courses.');
        redirect('member_send_activity.php');
    }

    if (!empty($_FILES['pdf_file']['name'])) {
        $file = $_FILES['pdf_file'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext === 'pdf' && $file['size'] <= 20 * 1024 * 1024) {
            if (!empty($edit['pdf_path']) && file_exists(__DIR__ . '/' . $edit['pdf_path']))
                unlink(__DIR__ . '/' . $edit['pdf_path']);
            $filename = 'act_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename))
                $pdf_path = 'uploads/activities/' . $filename;
        } else {
            flash('error', 'PDF only, max 20 MB.');
            redirect('member_send_activity.php' . ($edit ? '?id=' . $edit['id'] : '?new=1'));
        }
    }

    if (!empty($_POST['remove_pdf']) && !empty($edit['pdf_path'])) {
        if (file_exists(__DIR__ . '/' . $edit['pdf_path'])) unlink(__DIR__ . '/' . $edit['pdf_path']);
        $pdf_path = null;
    }

    if (!empty($_POST['id'])) {
        $conn->prepare('UPDATE activities SET course_id=?,title=?,description=?,due_date=?,max_score=?,pdf_path=? WHERE id=?')
             ->execute([$course_id, $title, $instructions, $due_date ?: null, $points, $pdf_path, (int)$_POST['id']]);
        flash('success', 'Activity updated.');
    } else {
        $conn->prepare('INSERT INTO activities(course_id,title,description,due_date,max_score,pdf_path) VALUES(?,?,?,?,?,?)')
             ->execute([$course_id, $title, $instructions, $due_date ?: null, $points, $pdf_path]);
        flash('success', 'Activity created.');
    }
    redirect('member_send_activity.php');
}

// SEND TO STUDENTS
$preview = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_mode'])) {
    if ($_POST['send_mode'] === 'load') {
        $actid = (int)$_POST['activity_id'];
        $s = $conn->prepare('
            SELECT DISTINCT s.*
            FROM students s
            JOIN enrollments e ON e.student_id = s.id
            JOIN activities a ON a.course_id = e.course_id
            WHERE a.id = ? AND a.course_id IN (' . implode(',', $my_course_ids) . ')
            ORDER BY s.full_name ASC
        ');
        $s->execute([$actid]);
        $preview = $s->fetchAll();
    } elseif ($_POST['send_mode'] === 'send') {
        $ids   = $_POST['student_ids'] ?? [];
        $actid = (int)$_POST['activity_id'];
        $a     = $conn->prepare('SELECT a.*, c.code FROM activities a JOIN courses c ON c.id=a.course_id WHERE a.id=?');
        $a->execute([$actid]);
        $act = $a->fetch();
        $cnt = 0;
        foreach ($ids as $sid) {
            $s = $conn->prepare('SELECT * FROM students WHERE id=?');
            $s->execute([(int)$sid]);
            $stu = $s->fetch();
            if (!$stu) continue;
            $subj = 'New Activity: ' . $act['title'];
            $conn->prepare('INSERT INTO sent_activity_emails(activity_id,student_id,recipient_email,subject,status) VALUES(?,?,?,?,?)')
                 ->execute([$actid, $stu['id'], $stu['email'], $subj, 'preview']);
            $cnt++;
        }
        flash('success', "Sent to $cnt student(s).");
        redirect('member_send_activity.php');
    }
}

$rows = [];
if ($my_course_ids) {
    $ids_str = implode(',', $my_course_ids);
    $rows = $conn->query("
        SELECT a.*, c.code course_code, c.title course_title
        FROM activities a
        JOIN courses c ON c.id = a.course_id
        WHERE a.course_id IN ($ids_str)
        ORDER BY a.id DESC
    ")->fetchAll();
}

$logs = [];
if ($my_course_ids) {
    $ids_str = implode(',', $my_course_ids);
    $logs = $conn->query("
        SELECT l.*, a.title activity_title, s.full_name
        FROM sent_activity_emails l
        JOIN activities a ON a.id = l.activity_id
        JOIN students s ON s.id = l.student_id
        WHERE a.course_id IN ($ids_str)
        ORDER BY l.sent_at DESC LIMIT 50
    ")->fetchAll();
}

$send_preselect = (int)($_GET['send'] ?? 0);
$ok  = flash('success');
$err = flash('error');

icloud_header('Send Activity');
render_school_banner($conn);
render_left_quick_access('member_send_activity');
?>

<div class="ic-body-solo">

  <div class="ic-page-header">
    <a href="member_dashboard.php" class="ic-page-back">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M7.5 2L3.5 6l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Home
    </a>
    <div class="ic-page-title-solo">Activities &amp; PDF</div>
  </div>

  <?php if ($ok): ?><div class="ic-alert ic-alert-ok">✓ <?= e($ok) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="ic-alert ic-alert-err">✗ <?= e($err) ?></div><?php endif; ?>

  <?php if (!$my_course_ids): ?>
  <div class="crd-empty">No courses assigned to you. Ask an admin to assign courses first.</div>
  <?php else: ?>

  <!-- ═══════════════════════════════
       SECTION 1 — CREATE / EDIT FORM
  ════════════════════════════════ -->
  <div class="ic-widget" id="form-section">
    <div class="ic-widget-header">
      <div class="ic-widget-icon" style="background:linear-gradient(135deg,#7c3aed,#5b21b6);">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><path d="M1 8h3l2-5 3 10 2-5 1 2h3" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div>
        <div class="ic-widget-title"><?= $edit ? 'Edit Activity' : 'New Activity' ?></div>
        <div class="ic-widget-sub">Create an activity or attach a PDF for your students</div>
      </div>
      <?php if (!$edit && !isset($_GET['new'])): ?>
        <a href="member_send_activity.php?new=1" class="ms-btn ms-btn-primary" style="margin-left:auto;">+ New Activity</a>
      <?php elseif ($edit): ?>
        <a href="member_send_activity.php" class="ms-btn ms-btn-light" style="margin-left:auto;">← Cancel</a>
      <?php endif; ?>
    </div>

    <?php if (isset($_GET['new']) || $edit): ?>
    <div class="ic-widget-body" style="padding-top:var(--sp-4);">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="save_activity" value="1">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="ms-form-grid">
          <label class="ms-label">
            Course
            <select name="course_id" class="ms-input">
              <?php foreach ($my_courses as $c): ?>
              <option value="<?= $c['id'] ?>" <?= (($edit['course_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                <?= e($c['code'] . ' – ' . $c['title']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="ms-label">
            Title
            <input type="text" name="title" class="ms-input" value="<?= e($edit['title'] ?? '') ?>" required placeholder="e.g. Quiz 1 – Chapter 3">
          </label>
          <label class="ms-label">
            Due Date
            <input type="datetime-local" name="due_date" class="ms-input" value="<?= e($edit['due_date'] ?? '') ?>">
          </label>
          <label class="ms-label">
            Points
            <input type="number" name="points" class="ms-input" value="<?= e($edit['max_score'] ?? 100) ?>" min="1">
          </label>
          <label class="ms-label" style="grid-column:1/-1;">
            Instructions
            <textarea name="instructions" class="ms-input ms-textarea" placeholder="Describe what students need to do…"><?= e($edit['description'] ?? '') ?></textarea>
          </label>
          <label class="ms-label" style="grid-column:1/-1;">
            <span>📄 Attach PDF <span style="font-weight:400;font-size:.78rem;color:rgba(255,255,255,.4);">(optional · PDF only · max 20 MB)</span></span>
            <input type="file" name="pdf_file" accept="application/pdf" class="ms-file-input">
            <?php if (!empty($edit['pdf_path'])): ?>
            <div style="margin-top:8px;display:flex;align-items:center;gap:12px;">
              <a href="<?= e($edit['pdf_path']) ?>" target="_blank" style="font-size:.82rem;color:#60a5fa;">📄 View current PDF</a>
              <label style="font-size:.78rem;color:#f87171;cursor:pointer;font-weight:400;display:flex;align-items:center;gap:4px;">
                <input type="checkbox" name="remove_pdf" value="1"> Remove PDF
              </label>
            </div>
            <?php endif; ?>
          </label>
        </div>
        <div style="margin-top:var(--sp-5);">
          <button type="submit" class="ms-btn ms-btn-primary"><?= $edit ? 'Update Activity' : 'Create Activity' ?></button>
        </div>
      </form>
    </div>
    <?php else: ?>
    <div class="ic-widget-body" style="padding-top:var(--sp-3);">
      <p style="font-size:.83rem;color:rgba(255,255,255,.35);">Click <strong style="color:rgba(255,255,255,.6);">+ New Activity</strong> above to create one, or click <strong style="color:rgba(255,255,255,.6);">Edit</strong> on a card below.</p>
    </div>
    <?php endif; ?>
  </div>

  <!-- ═══════════════════════════════
       SECTION 2 — ACTIVITY CARDS
  ════════════════════════════════ -->
  <?php if ($rows): ?>
  <div class="ic-widget">
    <div class="ic-widget-header">
      <div class="ic-widget-icon ic-icon-blue">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="2" stroke="#fff" stroke-width="1.5"/><path d="M5 6h6M5 9h4" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
      </div>
      <div>
        <div class="ic-widget-title">My Activities</div>
        <div class="ic-widget-sub"><?= count($rows) ?> total</div>
      </div>
    </div>
    <div class="ic-widget-body" style="padding-top:var(--sp-3);">

      <!-- Search bar for activities -->
      <div class="crd-search-bar" style="margin-bottom:.85rem;">
        <input type="text" id="actSearch" placeholder="Search activities…" class="crd-search-input">
        <select id="actCourse" class="crd-search-select">
          <option value="">All Courses</option>
          <?php foreach ($my_courses as $mc): ?>
            <option value="<?= e(strtolower($mc['code'])) ?>"><?= e($mc['code']) ?></option>
          <?php endforeach; ?>
        </select>
        <button onclick="runActSearch()" class="crd-search-btn">
          <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="6.5" cy="6.5" r="4" stroke="#fff" stroke-width="1.5"/><path d="M10 10l3 3" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
          Search
        </button>
        <span id="actCount" class="crd-search-count"></span>
      </div>

      <div id="actGrid" class="crd-grid-act">
        <?php foreach ($rows as $r): ?>
        <div class="act-card"
             data-search="<?= e(strtolower($r['title'] . ' ' . ($r['description'] ?? ''))) ?>"
             data-course="<?= e(strtolower($r['course_code'])) ?>">
          <!-- Card top -->
          <div class="act-card-icon">
            <svg width="20" height="20" viewBox="0 0 16 16" fill="none"><path d="M1 8h3l2-5 3 10 2-5 1 2h3" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div class="act-card-body">
            <div class="act-card-title">
              <?= e($r['title']) ?>
              <?php if (!empty($r['pdf_path'])): ?>
                <span class="ms-pdf-badge">📄 PDF</span>
              <?php endif; ?>
            </div>
            <div class="act-card-meta">
              <span class="ic-tag ic-tag-green"><?= e($r['course_code']) ?></span>
              <?php if (!empty($r['due_date'])): ?>
                <span class="act-meta-text">Due: <?= date('M j, Y', strtotime($r['due_date'])) ?></span>
              <?php endif; ?>
              <span class="act-meta-text"><?= $r['max_score'] ?> pts</span>
            </div>
            <?php if (!empty($r['description'])): ?>
            <div class="act-desc"><?= e(mb_strimwidth($r['description'], 0, 90, '…')) ?></div>
            <?php endif; ?>
          </div>
          <!-- Actions -->
          <div class="act-card-actions">
            <?php if (!empty($r['pdf_path'])): ?>
              <a href="<?= e($r['pdf_path']) ?>" target="_blank" class="ms-btn ms-btn-light">📄 View PDF</a>
            <?php endif; ?>
            <a href="member_send_activity.php?id=<?= $r['id'] ?>#form-section" class="ms-btn ms-btn-light">Edit</a>
            <a href="member_send_activity.php?send=<?= $r['id'] ?>#send-section" class="ms-btn ms-btn-primary">Send →</a>
            <a href="member_send_activity.php?delete=<?= $r['id'] ?>"
               class="ms-btn ms-btn-danger"
               onclick="return confirm('Delete this activity and its PDF?')">Delete</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div id="actNoResults" class="crd-empty" style="display:none;margin-top:.5rem;">😕 No activities match your search.</div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ═══════════════════════════════
       SECTION 3 — SEND TO STUDENTS
  ════════════════════════════════ -->
  <div class="ic-widget" id="send-section">
    <div class="ic-widget-header">
      <div class="ic-widget-icon" style="background:linear-gradient(135deg,#0369a1,#0284c7);">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><path d="M13.5 2.5L1.5 7l5 1.5M13.5 2.5L9 14l-2.5-5.5M13.5 2.5L6.5 8.5" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div>
        <div class="ic-widget-title">Send Activity to Students</div>
        <div class="ic-widget-sub">Notify enrolled students via email log</div>
      </div>
    </div>
    <div class="ic-widget-body" style="padding-top:var(--sp-4);">
      <?php if (!$rows): ?>
        <p style="font-size:.82rem;color:rgba(255,255,255,.35);">Create an activity first, then you can send it.</p>
      <?php else: ?>
      <form method="POST">
        <input type="hidden" name="send_mode" value="load">
        <div style="display:flex;gap:.6rem;align-items:flex-end;flex-wrap:wrap;">
          <label class="ms-label" style="flex:1;min-width:220px;">
            Activity
            <select name="activity_id" class="ms-input">
              <?php foreach ($rows as $r): ?>
              <option value="<?= $r['id'] ?>" <?= ($send_preselect && $send_preselect == $r['id']) ? 'selected' : '' ?>>
                <?= e($r['course_code'] . ' – ' . $r['title']) ?>
                <?php if (!empty($r['pdf_path'])): ?>📄<?php endif; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </label>
          <button type="submit" class="ms-btn ms-btn-primary" style="margin-bottom:0;">Load Students</button>
        </div>
      </form>
      <?php endif; ?>

      <?php if ($preview): ?>
      <div style="margin-top:var(--sp-5);border-top:1px solid rgba(255,255,255,.08);padding-top:var(--sp-4);">
        <div style="font-size:.82rem;font-weight:600;color:rgba(255,255,255,.6);margin-bottom:var(--sp-3);">Select students to notify</div>
        <form method="POST">
          <input type="hidden" name="send_mode" value="send">
          <input type="hidden" name="activity_id" value="<?= e($_POST['activity_id']) ?>">
          <div class="ms-checklist">
            <?php foreach ($preview as $s): ?>
            <label class="ms-check-item">
              <input type="checkbox" name="student_ids[]" value="<?= $s['id'] ?>" checked>
              <span><?= e($s['full_name']) ?></span>
              <span style="color:rgba(255,255,255,.35);font-size:.72rem;"><?= e($s['email']) ?></span>
            </label>
            <?php endforeach; ?>
          </div>
          <div style="margin-top:var(--sp-4);">
            <button type="submit" class="ms-btn ms-btn-primary">Send to Selected</button>
          </div>
        </form>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ═══════════════════════════════
       SECTION 4 — SEND LOGS
  ════════════════════════════════ -->
  <?php if ($logs): ?>
  <div class="ic-widget">
    <div class="ic-widget-header">
      <div class="ic-widget-icon" style="background:rgba(255,255,255,.1);">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><path d="M3 2h7l3 3v9a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1z" stroke="#fff" stroke-width="1.5"/><path d="M5 7h6M5 10h4" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
      </div>
      <div>
        <div class="ic-widget-title">Send Logs</div>
        <div class="ic-widget-sub"><?= count($logs) ?> recent entries</div>
      </div>
    </div>
    <div class="ic-widget-body" style="padding-top:var(--sp-3);">
      <div style="overflow-x:auto;">
        <table class="ms-table">
          <thead>
            <tr><th>Student</th><th>Activity</th><th>Email</th><th>Status</th><th>Date</th></tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $l): ?>
            <tr>
              <td><?= e($l['full_name']) ?></td>
              <td><?= e($l['activity_title']) ?></td>
              <td style="color:rgba(255,255,255,.45);font-size:.75rem;"><?= e($l['recipient_email']) ?></td>
              <td><span class="ms-status ms-status-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span></td>
              <td style="font-size:.72rem;color:rgba(255,255,255,.4);"><?= date('M j, Y', strtotime($l['sent_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php endif; ?>

</div>

<style>
.ic-alert{padding:.65rem 1rem;border-radius:10px;font-size:.82rem;margin-bottom:.6rem;font-weight:600;}
.ic-alert-ok {background:rgba(74,222,128,.12);color:#4ade80;border:1px solid rgba(74,222,128,.2);}
.ic-alert-err{background:rgba(248,113,113,.12);color:#f87171;border:1px solid rgba(248,113,113,.2);}

/* Search bar */
.crd-search-bar { display:flex;align-items:center;gap:.5rem;flex-wrap:wrap; }
.crd-search-input {
  flex:1;min-width:160px;
  background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);
  color:#fff;border-radius:8px;padding:.45rem .8rem;font-size:.82rem;outline:none;
}
.crd-search-input::placeholder{color:rgba(255,255,255,.3);}
.crd-search-input:focus{border-color:rgba(255,255,255,.3);}
.crd-search-select {
  background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);
  color:#fff;border-radius:8px;padding:.45rem .7rem;font-size:.8rem;outline:none;cursor:pointer;
}
.crd-search-select option{background:#1a2030;color:#fff;}
.crd-search-btn {
  display:inline-flex;align-items:center;gap:.35rem;
  background:linear-gradient(135deg,#1e40af,#1d4ed8);
  color:#fff;border:none;border-radius:8px;
  padding:.45rem .9rem;font-size:.8rem;font-weight:700;
  cursor:pointer;transition:opacity .15s;white-space:nowrap;
}
.crd-search-btn:hover{opacity:.85;}
.crd-search-count{font-size:.75rem;color:rgba(255,255,255,.35);white-space:nowrap;}

/* Activity cards */
.crd-grid-act {
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
  gap:.7rem;
}
.act-card {
  background:rgba(255,255,255,.04);
  border:1px solid rgba(255,255,255,.09);
  border-radius:14px;padding:1rem;
  display:flex;flex-direction:column;gap:.55rem;
  transition:background .15s,border-color .15s;
}
.act-card:hover{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.18);}
.act-card-icon {
  width:42px;height:42px;border-radius:11px;
  background:linear-gradient(135deg,#7c3aed,#5b21b6);
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.act-card-body{flex:1;}
.act-card-title{font-size:.88rem;font-weight:700;color:#fff;margin-bottom:.3rem;line-height:1.3;}
.act-card-meta{display:flex;flex-wrap:wrap;gap:.35rem;align-items:center;margin-bottom:.25rem;}
.act-meta-text{font-size:.7rem;color:rgba(255,255,255,.4);}
.act-desc{font-size:.73rem;color:rgba(255,255,255,.3);line-height:1.4;}
.act-card-actions{display:flex;flex-wrap:wrap;gap:.35rem;}

/* Misc */
.ms-pdf-badge{display:inline-block;font-size:.67rem;background:rgba(96,165,250,.15);color:#60a5fa;border:1px solid rgba(96,165,250,.25);border-radius:4px;padding:1px 6px;margin-left:4px;vertical-align:middle;font-weight:700;}

/* Buttons */
.ms-btn{display:inline-flex;align-items:center;gap:.3rem;border:none;border-radius:8px;padding:.35rem .75rem;font-size:.76rem;font-weight:700;cursor:pointer;text-decoration:none;transition:opacity .15s;white-space:nowrap;}
.ms-btn-primary{background:linear-gradient(135deg,#7c3aed,#5b21b6);color:#fff;}
.ms-btn-light  {background:rgba(255,255,255,.1);color:rgba(255,255,255,.8);}
.ms-btn-danger {background:rgba(239,68,68,.18);color:#f87171;}
.ms-btn:hover  {opacity:.82;}

/* Form */
.ms-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
@media(max-width:580px){.ms-form-grid{grid-template-columns:1fr;}}
.ms-label{display:flex;flex-direction:column;gap:.3rem;font-size:.78rem;font-weight:600;color:rgba(255,255,255,.55);}
.ms-input{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff;border-radius:8px;padding:.4rem .65rem;font-size:.82rem;outline:none;transition:border-color .15s;width:100%;box-sizing:border-box;}
.ms-input:focus{border-color:rgba(255,255,255,.3);}
.ms-input option{background:#1a2030;color:#fff;}
.ms-input::placeholder{color:rgba(255,255,255,.28);}
.ms-textarea{resize:vertical;min-height:80px;}
.ms-file-input{margin-top:4px;font-size:.78rem;color:rgba(255,255,255,.55);}
.ms-file-input::file-selector-button{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);color:#fff;border-radius:6px;padding:.3rem .65rem;font-size:.75rem;cursor:pointer;margin-right:.5rem;}

/* Checklist */
.ms-checklist{display:flex;flex-direction:column;gap:.4rem;max-height:220px;overflow-y:auto;padding-right:4px;}
.ms-check-item{display:flex;align-items:center;gap:.5rem;padding:.35rem .5rem;border-radius:7px;background:rgba(255,255,255,.04);font-size:.8rem;color:#fff;cursor:pointer;}
.ms-check-item input{accent-color:#7c3aed;flex-shrink:0;}

/* Table */
.ms-table{width:100%;border-collapse:collapse;font-size:.78rem;}
.ms-table th{text-align:left;padding:.4rem .6rem;color:rgba(255,255,255,.4);font-weight:600;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid rgba(255,255,255,.08);}
.ms-table td{padding:.45rem .6rem;color:rgba(255,255,255,.75);border-bottom:1px solid rgba(255,255,255,.05);}
.ms-table tr:last-child td{border-bottom:none;}
.ms-status{font-size:.7rem;font-weight:700;border-radius:4px;padding:2px 7px;}
.ms-status-preview{background:rgba(251,191,36,.15);color:#fbbf24;}
.ms-status-sent   {background:rgba(74,222,128,.15);color:#4ade80;}
.ms-status-failed {background:rgba(248,113,113,.15);color:#f87171;}

.crd-empty {
  text-align:center;padding:2rem;
  font-size:.83rem;color:rgba(255,255,255,.3);
  background:rgba(255,255,255,.03);
  border:1px solid rgba(255,255,255,.07);
  border-radius:14px;
}
</style>

<script>
function runActSearch() {
  var q  = document.getElementById('actSearch').value.toLowerCase().trim();
  var cv = document.getElementById('actCourse').value.toLowerCase();
  var cards = document.querySelectorAll('#actGrid .act-card');
  var vis = 0;
  cards.forEach(function(c) {
    var matchQ = !q  || (c.dataset.search || '').includes(q);
    var matchC = !cv || (c.dataset.course || '').includes(cv);
    var show = matchQ && matchC;
    c.style.display = show ? '' : 'none';
    if (show) vis++;
  });
  var cnt = document.getElementById('actCount');
  if (cnt) cnt.textContent = vis + ' of ' + cards.length + ' activit' + (cards.length !== 1 ? 'ies' : 'y');
  var noRes = document.getElementById('actNoResults');
  if (noRes) noRes.style.display = (vis === 0 && cards.length > 0) ? '' : 'none';
}
var actSearchInput = document.getElementById('actSearch');
if (actSearchInput) {
  actSearchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') runActSearch();
  });
  runActSearch();
}
</script>

<?php if ($send_preselect): ?>
<script>
document.addEventListener('DOMContentLoaded',function(){
  var el = document.getElementById('send-section');
  if(el) el.scrollIntoView({behavior:'smooth',block:'start'});
});
</script>
<?php endif; ?>

<?php icloud_footer(); ?>
