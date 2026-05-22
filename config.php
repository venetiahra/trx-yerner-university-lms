<?php

// ===========================
// APP NAME
// ===========================
$APP_NAME = "TRX-Yerner University LMS";

// ===========================
// DATABASE CONNECTION
// ===========================
$host = 'localhost';
$dbname = 'lms_db';
$username = 'root';
$password = '';

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ===========================
// SESSION
// ===========================
session_start();

// ===========================
// HELPERS
// ===========================
function redirect($url) {
    header("Location: $url");
    exit;
}

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// ===========================
// LOGIN CHECK
// ===========================
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

// ✅ NEW: ADMIN CHECK
function require_admin() {
    require_login();

    if (!in_array($_SESSION['user_role'] ?? '', ['admin','owner'])) {
        die("Access denied.");
    }
}

// ✅ MEMBER/PROF CHECK
function require_member() {
    require_login();

    if (!in_array($_SESSION['user_role'] ?? '', ['member','admin','owner'])) {
        die("Access denied.");
    }
}

// ===========================
// ROLE REDIRECT
// ===========================
function role_home() {
    $role = $_SESSION['user_role'] ?? '';

    switch ($role) {
        case 'owner':
        case 'admin':
            return 'dashboard.php';

        case 'student':
            return 'student_dashboard.php';

        case 'member':
            return 'member_dashboard.php';

        default:
            return 'login.php';
    }
}

// ===========================
// FLASH MESSAGE SYSTEM
// ===========================
function flash($key, $message = null) {

    // SET flash message
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return;
    }

    // GET flash message
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]); // remove after use
        return $msg;
    }

    return null;
}

?>