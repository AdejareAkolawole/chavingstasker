<?php
// handle_promotion_request.php
require_once 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['gigId']) && isset($input['gigTitle']) && isset($input['planDetails'])) {
    $user_id = $_SESSION['user_id'];
    $gig_id = $input['gigId'];
    $gig_title = $input['gigTitle'];
    $plan_details = $input['planDetails'];

    try {
        // First, check if a pending request for this gig already exists
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM pro_requests WHERE user_id = ? AND item_id = ? AND status = 'Pending' AND type = 'Gig Promotion'");
        $check_stmt->execute([$user_id, $gig_id]);
        if ($check_stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'A pending request for this gig already exists.']);
            exit;
        }

        // Insert the new request into the database
        $stmt = $pdo->prepare("INSERT INTO pro_requests (user_id, item_id, item_title, plan_details, type) VALUES (?, ?, ?, ?, 'Gig Promotion')");
        $stmt->execute([$user_id, $gig_id, $gig_title, $plan_details]);

        echo json_encode(['success' => true, 'message' => 'Admin request sent successfully! We will review your payment and activate your promotion shortly.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
}