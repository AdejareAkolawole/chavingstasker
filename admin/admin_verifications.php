<?php
// admin/admin_verifications.php
require_once 'admin_check.php';
require_once '../db.php';

// Handle approval or rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $request_id = $_GET['id'];
    $action = $_GET['action'];

    // Get the user ID from the request
    $stmt = $pdo->prepare("SELECT user_id FROM verification_requests WHERE id = ?");
    $stmt->execute([$request_id]);
    $user_id = $stmt->fetchColumn();

    if ($action === 'approve') {
        // Update user's 'verified' status to 1
        $update_user_stmt = $pdo->prepare("UPDATE users SET verified = 1 WHERE id = ?");
        $update_user_stmt->execute([$user_id]);
        
        // Update request status
        $update_request_stmt = $pdo->prepare("UPDATE verification_requests SET status = 'Approved' WHERE id = ?");
        $update_request_stmt->execute([$request_id]);
    } elseif ($action === 'reject') {
        // Update request status
        $update_request_stmt = $pdo->prepare("UPDATE verification_requests SET status = 'Rejected' WHERE id = ?");
        $update_request_stmt->execute([$request_id]);
    }
    header("Location: admin_verifications.php");
    exit;
}

// Fetch all pending requests
$requests_stmt = $pdo->query("SELECT vr.*, u.first_name, u.last_name, u.email FROM verification_requests vr JOIN users u ON vr.user_id = u.id WHERE vr.status = 'Pending' ORDER BY vr.submitted_at DESC");
$pending_requests = $requests_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifications - Admin Panel</title>
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .action-btns a.approve { color: var(--brand-green); }
        .action-btns a.reject { color: #EF4444; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-header"><a href="admin_dashboard.php" class="logo">Admin Panel</a></div>
            <nav class="sidebar-nav">
                <a href="admin_verifications.php" class="nav-item active"><i class="fas fa-user-check"></i><span>Verifications</span></a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h1>Pending Verifications</h1>
            </header>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Document Type</th>
                            <th>Submitted At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pending_requests)): ?>
                            <tr><td colspan="5" style="text-align: center;">No pending verification requests.</td></tr>
                        <?php else: ?>
                            <?php foreach ($pending_requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['email']); ?></td>
                                <td><?php echo htmlspecialchars($request['document_type']); ?></td>
                                <td><?php echo (new DateTime($request['submitted_at']))->format('M j, Y H:i'); ?></td>
                                <td class="action-btns">
                                    <a href="../<?php echo htmlspecialchars($request['document_path']); ?>" target="_blank" title="View Document"><i class="fas fa-eye"></i></a>
                                    <a href="admin_verifications.php?action=approve&id=<?php echo $request['id']; ?>" class="approve" title="Approve" onclick="return confirm('Approve this verification?');"><i class="fas fa-check-circle"></i></a>
                                    <a href="admin_verifications.php?action=reject&id=<?php echo $request['id']; ?>" class="reject" title="Reject" onclick="return confirm('Reject this verification?');"><i class="fas fa-times-circle"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>