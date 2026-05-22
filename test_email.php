<?php
require 'config.php';
require 'mail_helper.php';

// ✅ CHANGE THIS TO YOUR REAL EMAIL
$test_email = "prsrlphs@gmail.com";

// ✅ IMPORTANT: use NULL for testing (avoid FK error)
$result = send_system_email(
    $conn,
    null, // <-- VERY IMPORTANT FIX
    $test_email,
    "Test User",
    "✅ TEST EMAIL FROM TRX LMS",
    "<h2>✅ Email is working!</h2>
     <p>This is a test message from your system.</p>
     <p>If you see this, your PHPMailer is configured correctly 🎉</p>",
    "generic"
);

// ✅ OUTPUT RESULT
echo "<h2>Email Status: $result</h2>";
?>
