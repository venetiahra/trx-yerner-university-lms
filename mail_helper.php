<?php
require_once 'mail_config.php';

// ===========================
// ENSURE TABLE
// Matches your existing SQL table structure
// ===========================
function ensure_account_email_logs($conn){
    $conn->exec("
        CREATE TABLE IF NOT EXISTS account_email_logs(
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            recipient VARCHAR(150) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            body TEXT NOT NULL,
            type VARCHAR(100) DEFAULT NULL,
            status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
            sent_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

// ===========================
// SEND EMAIL FUNCTION
// ===========================
function send_system_email($conn, $user_id, $to, $name, $subject, $html, $type = 'generic'){

    ensure_account_email_logs($conn);

    // Your DB uses pending/sent/failed only.
    // So preview mode will be saved as pending.
    $status = 'pending';
    $error  = '';

    if (!MAIL_PREVIEW_ONLY) {

        if (file_exists(__DIR__ . '/vendor/autoload.php')) {

            require_once __DIR__ . '/vendor/autoload.php';

            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);

                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USERNAME;
                $mail->Password   = SMTP_PASSWORD;
                $mail->SMTPSecure = SMTP_SECURE;
                $mail->Port       = SMTP_PORT;

                $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
                $mail->addAddress($to, $name);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $html;

                $mail->send();

                $status = 'sent';

            } catch (Exception $e) {
                $status = 'failed';
                $error  = $e->getMessage();
            }

        } else {
            $status = 'failed';
            $error  = 'PHPMailer not installed.';
        }
    }

    // If email failed, add the error inside the body log
    // because your current DB table has no error_message column.
    $log_body = $html;

    if ($error !== '') {
        $log_body .= '<hr><p><b>Email Error:</b> ' . e($error) . '</p>';
    }

    $sent_at = $status === 'sent' ? date('Y-m-d H:i:s') : null;

    $stmt = $conn->prepare("
        INSERT INTO account_email_logs
        (user_id, recipient, subject, body, type, status, sent_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $user_id,
        $to,
        $subject,
        $log_body,
        $type,
        $status,
        $sent_at
    ]);

    return $status;
}

// ===========================
// EMAIL TEMPLATES
// ===========================

// ── Base email wrapper (dark navy + gold, matches website theme) ──
function email_base($title, $accent, $icon, $body_html) {
    $year = date('Y');
    return '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>' . htmlspecialchars($title) . '</title>
</head>
<body style="margin:0;padding:0;background-color:#07121f;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" border="0"
       style="background-color:#07121f;padding:40px 16px;">
  <tr><td align="center">
  <table width="600" cellpadding="0" cellspacing="0" border="0"
         style="max-width:600px;width:100%;border-radius:16px;
                border:1px solid ' . $accent . '44;background-color:#0c1d35;overflow:hidden;">

    <!-- Header -->
    <tr>
      <td style="background-color:#07121f;padding:28px 32px;
                 border-bottom:2px solid ' . $accent . ';">
        <table cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td style="width:46px;height:46px;border-radius:12px;
                       background-color:' . $accent . ';text-align:center;
                       vertical-align:middle;font-size:24px;line-height:46px;">
              ' . $icon . '
            </td>
            <td style="padding-left:14px;vertical-align:middle;">
              <div style="font-size:10px;letter-spacing:0.14em;text-transform:uppercase;
                          color:' . $accent . ';font-weight:700;margin-bottom:3px;">
                TRX-Yerner University LMS
              </div>
              <div style="font-size:20px;font-weight:bold;color:#ffffff;
                          font-family:Georgia,serif;">
                ' . htmlspecialchars($title) . '
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- Body -->
    <tr>
      <td style="padding:32px 32px 28px;background-color:#0c1d35;">
        ' . $body_html . '
      </td>
    </tr>

    <!-- Divider -->
    <tr>
      <td style="padding:0 32px;">
        <div style="height:1px;background-color:#ffffff14;"></div>
      </td>
    </tr>

    <!-- Footer -->
    <tr>
      <td style="padding:20px 32px 28px;background-color:#07121f;text-align:center;">
        <p style="margin:0;font-size:11px;color:#ffffff44;line-height:1.7;">
          This is an automated message from
          <strong style="color:#ffffff66;">TRX-Yerner University LMS</strong>.<br/>
          Please do not reply directly to this email.
          &nbsp;&bull;&nbsp;
          &copy; ' . $year . ' TRX-Yerner University. All rights reserved.
        </p>
      </td>
    </tr>

  </table>
  </td></tr>
</table>
</body>
</html>';
}

// ── Shared helpers ──
function _eg($name) {
    return '<p style="margin:0 0 18px;font-size:14px;color:#ffffffaa;">
              Hello, <strong style="color:#ffffff;font-size:15px;">'
           . htmlspecialchars($name) . '</strong>
            </p>';
}
function _et($text) {
    return '<p style="margin:0 0 14px;font-size:14px;line-height:1.75;color:#ffffffbb;">'
           . $text . '</p>';
}
function _ebadge($label, $bg, $color) {
    return '<div style="display:inline-block;margin:14px 0 18px;padding:9px 22px;
                         border-radius:99px;background-color:' . $bg . ';
                         color:' . $color . ';font-size:13px;font-weight:700;
                         letter-spacing:0.04em;">'
           . $label . '</div><br/>';
}
function _einfo($rows) {
    $html = '<table width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="margin:18px 0 22px;border-radius:10px;
                           border:1px solid #ffffff18;background-color:#ffffff08;">';
    foreach ($rows as $i => [$label, $value]) {
        $bg = $i % 2 === 0 ? 'transparent' : '#ffffff05';
        $html .= '<tr style="background-color:' . $bg . ';">
                    <td style="padding:11px 16px;font-size:11px;font-weight:700;
                                text-transform:uppercase;letter-spacing:0.08em;
                                color:#ffffff44;width:36%;
                                border-right:1px solid #ffffff10;">'
                    . htmlspecialchars($label) . '</td>
                    <td style="padding:11px 16px;font-size:13px;
                                color:#ffffff;font-weight:600;">'
                    . htmlspecialchars($value) . '</td>
                  </tr>';
    }
    return $html . '</table>';
}

// ── Registration Pending ──
function registration_pending_email($app, $name) {
    $b  = _eg($name);
    $b .= _et('Thank you for registering at <strong style="color:#c9a84c;">'
              . htmlspecialchars($app) . '</strong>. Your account has been created and is now awaiting review.');
    $b .= _ebadge('&#9203;&nbsp; Pending Approval', '#f5c84222', '#f5c842');
    $b .= _einfo([
        ['Status',    'Pending — awaiting admin review'],
        ['Next step', 'You will be notified once your account is approved'],
    ]);
    $b .= _et('If you have questions, please contact the university administration office.');
    return email_base('Registration Received', '#c9a84c', '&#127979;', $b);
}

// ── Account Approved ──
function account_approved_email($app, $name) {
    $b  = _eg($name);
    $b .= _et('Great news! Your account for <strong style="color:#c9a84c;">'
              . htmlspecialchars($app) . '</strong> has been approved. You now have full access to the portal.');
    $b .= _ebadge('&#10003;&nbsp; Account Active', '#22c55e22', '#4ade80');
    $b .= _einfo([
        ['Status', 'Active — full access granted'],
        ['Portal',  $app],
    ]);
    $b .= _et('You may now log in to your portal and start using the system. Welcome aboard!');
    return email_base('Account Approved', '#4ade80', '&#9989;', $b);
}

// ── Account Blocked / Locked ──
function account_blocked_email($app, $name) {
    $b  = _eg($name);
    $b .= _et('We are writing to inform you that your account at <strong style="color:#c9a84c;">'
              . htmlspecialchars($app) . '</strong> has been <strong style="color:#f87171;">locked</strong> by the administrator.');
    $b .= _ebadge('&#128274;&nbsp; Account Locked', '#ef444422', '#f87171');
    $b .= _einfo([
        ['Status',  'Locked — access revoked'],
        ['Reason',  'Administrator action'],
        ['Contact', 'Reach out to the university admin office'],
    ]);
    $b .= _et('If you believe this is a mistake, please contact the university administration as soon as possible.');
    return email_base('Account Locked', '#f87171', '&#128274;', $b);
}

// ── Account Suspended ──
function account_suspended_email($app, $name) {
    $b  = _eg($name);
    $b .= _et('Your account at <strong style="color:#c9a84c;">'
              . htmlspecialchars($app) . '</strong> has been temporarily <strong style="color:#fbbf24;">suspended</strong>.');
    $b .= _ebadge('&#9888;&nbsp; Account Suspended', '#f59e0b22', '#fbbf24');
    $b .= _einfo([
        ['Status',  'Suspended — temporary restriction'],
        ['Access',  'Login and portal features are currently disabled'],
        ['Contact', 'Reach out to admin to resolve your account status'],
    ]);
    $b .= _et('This suspension may be temporary. Please contact the university administrator for further information.');
    return email_base('Account Suspended', '#fbbf24', '&#9888;', $b);
}

// ── Account Reactivated ──
function account_reactivated_email($app, $name) {
    $b  = _eg($name);
    $b .= _et('Welcome back! Your account at <strong style="color:#c9a84c;">'
              . htmlspecialchars($app) . '</strong> has been reactivated. Full access has been restored.');
    $b .= _ebadge('&#10003;&nbsp; Account Reactivated', '#22c55e22', '#4ade80');
    $b .= _einfo([
        ['Status', 'Active — access fully restored'],
        ['Portal',  $app],
    ]);
    $b .= _et('You can now log in to your portal and continue where you left off.');
    return email_base('Account Reactivated', '#4ade80', '&#9989;', $b);
}
?>