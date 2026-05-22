<?php
require 'config.php';
require_once __DIR__ . '/notif_helper.php';

// Session check without loading partials (avoid HTML output)
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthenticated']);
    exit;
}

$uid    = (int)$_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

header('Content-Type: application/json');
header('Cache-Control: no-store');

function notif_ago(string $dt): string {
    $diff = time() - strtotime($dt);
    if ($diff < 60)     return 'Just now';
    if ($diff < 3600)   return (int)($diff/60)   . 'm ago';
    if ($diff < 86400)  return (int)($diff/3600)  . 'h ago';
    if ($diff < 604800) return (int)($diff/86400) . 'd ago';
    return date('M j', strtotime($dt));
}

switch ($action) {

    case 'count':
        $st = $conn->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $st->execute([$uid]);
        echo json_encode(['count' => (int)$st->fetchColumn()]);
        break;

    case 'list':
        $st = $conn->prepare(
            'SELECT id, type, title, body, link, is_read, created_at
             FROM notifications
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT 20'
        );
        $st->execute([$uid]);
        $items = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach ($items as &$n) {
            $n['ago'] = notif_ago($n['created_at']);
        }
        echo json_encode(['items' => $items]);
        break;

    case 'mark_read':
        $conn->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0')
             ->execute([$uid]);
        echo json_encode(['ok' => true]);
        break;

    case 'mark_one':
        $id = (int)($_REQUEST['id'] ?? 0);
        if ($id) {
            $conn->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?')
                 ->execute([$id, $uid]);
        }
        echo json_encode(['ok' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'unknown action']);
}
