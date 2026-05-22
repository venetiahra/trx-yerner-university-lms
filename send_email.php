<?php
require 'config.php';
require 'mail_helper.php';
require_admin();

$templates = [
    'pending'     => 'Registration Pending',
    'approved'    => 'Account Approved',
    'blocked'     => 'Account Locked',
    'suspended'   => 'Account Suspended',
    'reactivated' => 'Account Reactivated',
];

$result  = null;
$preview = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to       = trim($_POST['to']   ?? '');
    $name     = trim($_POST['name'] ?? '');
    $template = $_POST['template']  ?? '';

    if ($to && $name && isset($templates[$template])) {
        $html = match($template) {
            'pending'     => registration_pending_email($APP_NAME, $name),
            'approved'    => account_approved_email($APP_NAME, $name),
            'blocked'     => account_blocked_email($APP_NAME, $name),
            'suspended'   => account_suspended_email($APP_NAME, $name),
            'reactivated' => account_reactivated_email($APP_NAME, $name),
        };

        if (isset($_POST['preview'])) {
            $preview = $html;
        } else {
            $status = send_system_email(
                $conn, null, $to, $name,
                '[TRX-Yerner University] ' . $templates[$template],
                $html, $template
            );
            $result = $status;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Send Test Email — TRX-Yerner University</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: #07121f;
      color: #fff;
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      padding: 48px 16px;
    }

    .wrap { width: 100%; max-width: 560px; }

    /* Header */
    .page-header {
      margin-bottom: 28px;
      padding-bottom: 18px;
      border-bottom: 1px solid rgba(201,168,76,0.25);
    }
    .page-header .label {
      font-size: 10px;
      letter-spacing: .14em;
      text-transform: uppercase;
      color: #c9a84c;
      font-weight: 700;
      margin-bottom: 6px;
    }
    .page-header h1 {
      font-size: 22px;
      font-weight: 700;
      color: #fff;
    }
    .page-header p {
      margin-top: 5px;
      font-size: 13px;
      color: rgba(255,255,255,0.45);
    }

    /* Card */
    .card {
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.10);
      border-radius: 14px;
      padding: 28px;
      margin-bottom: 18px;
    }

    /* Form */
    .form-group { margin-bottom: 18px; }
    label {
      display: block;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: rgba(255,255,255,0.45);
      margin-bottom: 7px;
    }
    input, select {
      width: 100%;
      padding: 11px 14px;
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 8px;
      color: #fff;
      font-size: 14px;
      outline: none;
      transition: border-color .15s, background .15s;
    }
    input::placeholder { color: rgba(255,255,255,0.25); }
    input:focus, select:focus {
      border-color: rgba(201,168,76,0.6);
      background: rgba(255,255,255,0.10);
    }
    select option { background: #0f2040; color: #fff; }

    /* Template pills */
    .template-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
    }
    .tpl-opt {
      position: relative;
    }
    .tpl-opt input[type="radio"] {
      position: absolute;
      opacity: 0;
      width: 0;
    }
    .tpl-opt label {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px 14px;
      border-radius: 8px;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.10);
      cursor: pointer;
      font-size: 12px;
      font-weight: 600;
      text-transform: none;
      letter-spacing: 0;
      color: rgba(255,255,255,0.65);
      transition: all .15s;
    }
    .tpl-opt input:checked + label {
      background: rgba(201,168,76,0.15);
      border-color: rgba(201,168,76,0.50);
      color: #f5c842;
    }
    .tpl-dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    /* Buttons */
    .btn-row { display: flex; gap: 10px; margin-top: 6px; }
    .btn {
      flex: 1;
      padding: 12px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      border: none;
      transition: opacity .15s, transform .1s;
    }
    .btn:hover { opacity: .88; transform: translateY(-1px); }
    .btn:active { transform: translateY(0); }
    .btn-send {
      background: linear-gradient(135deg, #c9a84c, #e8c97a);
      color: #07121f;
    }
    .btn-preview {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.15);
      color: rgba(255,255,255,0.80);
    }

    /* Alert */
    .alert {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 14px 18px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 18px;
    }
    .alert-sent    { background: rgba(34,197,94,0.15);  border: 1px solid rgba(34,197,94,0.35); color: #4ade80; }
    .alert-failed  { background: rgba(239,68,68,0.15);  border: 1px solid rgba(239,68,68,0.35); color: #f87171; }
    .alert-pending { background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.35); color: #fbbf24; }

    /* Preview frame */
    .preview-wrap { margin-top: 24px; }
    .preview-label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .10em;
      color: rgba(255,255,255,0.40);
      margin-bottom: 10px;
    }
    iframe {
      width: 100%;
      height: 580px;
      border: 1px solid rgba(255,255,255,0.10);
      border-radius: 12px;
      background: #07121f;
    }

    /* Back link */
    .back {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      color: rgba(255,255,255,0.35);
      text-decoration: none;
      margin-top: 20px;
      transition: color .15s;
    }
    .back:hover { color: #c9a84c; }
  </style>
</head>
<body>
<div class="wrap">

  <div class="page-header">
    <div class="label">TRX-Yerner University LMS</div>
    <h1>&#9993; Send Test Email</h1>
    <p>Preview or send a test notification email to any address.</p>
  </div>

  <?php if ($result !== null): ?>
    <?php if ($result === 'sent'): ?>
      <div class="alert alert-sent">&#10003;&nbsp; Email sent successfully!</div>
    <?php elseif ($result === 'pending'): ?>
      <div class="alert alert-pending">&#9203;&nbsp; Saved as pending (MAIL_PREVIEW_ONLY is ON).</div>
    <?php else: ?>
      <div class="alert alert-failed">&#10005;&nbsp; Email failed to send. Check SMTP settings in mail_config.php.</div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="card">
    <form method="POST">

      <div class="form-group">
        <label>Recipient Email</label>
        <input type="email" name="to" placeholder="e.g. student@email.com"
               value="<?= e($_POST['to'] ?? '') ?>" required/>
      </div>

      <div class="form-group">
        <label>Recipient Name</label>
        <input type="text" name="name" placeholder="e.g. Juan dela Cruz"
               value="<?= e($_POST['name'] ?? '') ?>" required/>
      </div>

      <div class="form-group">
        <label>Email Template</label>
        <div class="template-grid">
          <?php
          $dots = [
            'pending'     => ['#f5c842', '&#9203; Registration Pending'],
            'approved'    => ['#4ade80', '&#10003; Account Approved'],
            'blocked'     => ['#f87171', '&#128274; Account Locked'],
            'suspended'   => ['#fbbf24', '&#9888; Account Suspended'],
            'reactivated' => ['#4ade80', '&#9989; Account Reactivated'],
          ];
          foreach ($dots as $val => [$color, $label]):
            $checked = (($_POST['template'] ?? 'approved') === $val) ? 'checked' : '';
          ?>
          <div class="tpl-opt">
            <input type="radio" name="template" id="tpl_<?= $val ?>"
                   value="<?= $val ?>" <?= $checked ?>>
            <label for="tpl_<?= $val ?>">
              <span class="tpl-dot" style="background:<?= $color ?>;
                    box-shadow:0 0 6px <?= $color ?>88;"></span>
              <?= $label ?>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="btn-row">
        <button type="submit" name="preview" class="btn btn-preview">&#128065; Preview</button>
        <button type="submit" class="btn btn-send">&#9993; Send Email</button>
      </div>

    </form>
  </div>

  <?php if ($preview): ?>
  <div class="preview-wrap">
    <div class="preview-label">&#128065; Email Preview</div>
    <iframe srcdoc="<?= htmlspecialchars($preview, ENT_QUOTES) ?>"></iframe>
  </div>
  <?php endif; ?>

  <a class="back" href="dashboard.php">&#8592; Back to Dashboard</a>

</div>
</body>
</html>
