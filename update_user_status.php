<?php
require 'config.php';

require_admin();

$id = $_GET['id'] ?? 0;
$action = $_GET['action'] ?? '';

if (!$id || !$action) {
    redirect('users.php');
}

// determine new status
switch ($action) {
    case 'approve':
        $status = 'active';
        break;

    case 'suspend':
        $status = 'suspended';
        break;

    case 'block':
        $status = 'blocked';
        break;

    case 'reactivate':
        $status = 'active';
        break;

    default:
        redirect('users.php');
}

$stmt = $conn->prepare("UPDATE users SET status=? WHERE id=?");
$stmt->execute([$status, $id]);

flash('success', 'User status updated!');
redirect('users.php');