<?php
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['messages' => []]);
    exit;
}

$receiver_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);
if (!$receiver_id) {
    echo json_encode(['messages' => []]);
    exit;
}

$messages_stmt = $pdo->prepare("
    SELECT m.*, u.first_name, u.last_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.created_at ASC
");
$messages_stmt->execute([$_SESSION['user_id'], $receiver_id, $receiver_id, $_SESSION['user_id']]);
$messages = $messages_stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark messages as read
$pdo->prepare("UPDATE messages SET is_read = TRUE WHERE receiver_id = ? AND sender_id = ?")
    ->execute([$_SESSION['user_id'], $receiver_id]);

$formatted_messages = array_map(function($msg) {
    return [
        'sender_id' => $msg['sender_id'],
        'content' => htmlspecialchars($msg['content']),
        'file_path' => $msg['file_path'],
        'created_at' => date('M d, Y H:i', strtotime($msg['created_at']))
    ];
}, $messages);

echo json_encode(['messages' => $formatted_messages]);
?>