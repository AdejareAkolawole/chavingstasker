<?php
// handle_pro_request.php
require_once 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['userId']) && isset($input['userEmail']) && isset($input['planDetails'])) {
    $user_id = $input['userId'];
    $user_email = $input['userEmail'];
    $plan_details = $input['planDetails'];

    try {
        $stmt = $pdo->prepare("INSERT INTO pro_requests (user_id, user_email, plan_details) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $user_email, $plan_details]);

        echo json_encode(['success' => true, 'message' => 'Request saved successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
}
?>