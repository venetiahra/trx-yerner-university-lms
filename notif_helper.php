<?php
/**
 * notif_helper.php — include this wherever notif_push() is needed
 * Hindi nag-o-output ng HTML, safe to include anytime
 */
function notif_push(PDO $conn, int $user_id, string $type, string $title, string $body = '', string $link = ''): void {
    if ($user_id <= 0) return;
    try {
        $st = $conn->prepare(
            'INSERT INTO notifications (user_id, type, title, body, link) VALUES (?, ?, ?, ?, ?)'
        );
        $st->execute([$user_id, $type, $title, $body, $link]);
    } catch (Exception $e) {
        // fail silently — notifications should never break main flow
    }
}

function notif_push_admins(PDO $conn, string $type, string $title, string $body = '', string $link = ''): void {
    try {
        $admins = $conn->query("SELECT id FROM users WHERE role='admin' AND status='active'")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($admins as $aid) {
            notif_push($conn, (int)$aid, $type, $title, $body, $link);
        }
    } catch (Exception $e) {}
}
