<?php
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT sender_id, COUNT(*) as unread_count
    FROM messages
    WHERE receiver_id = ? AND is_read = FALSE
    GROUP BY sender_id
");
$stmt->execute([$_SESSION['user_id']]);
$unread_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($unread_counts as $row) {
    $result[$row['sender_id']] = (int)$row['unread_count'];
}

echo json_encode($result);
?>