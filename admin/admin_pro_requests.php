<?php
// admin/admin_pro_requests.php
require_once 'admin_check.php';
require_once '../db.php';

// Handle request approval
if (isset($_GET['approve_id'])) {
    $request_id = $_GET['approve_id'];
    $stmt = $pdo->prepare("SELECT user_id, plan_details FROM pro_requests WHERE id = ?");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($request) {
        $user_id = $request['user_id'];
        $duration = (strpos($request['plan_details'], 'Yearly') !== false) ? '1 YEAR' : '1 MONTH';
        
        // Update the user's featured_until date
        $update_user_stmt = $pdo->prepare("UPDATE users SET featured_until = DATE_ADD(NOW(), INTERVAL $duration) WHERE id = ?");
        $update_user_stmt->execute([$user_id]);

        // Mark the request as Approved
        $update_request_stmt = $pdo->prepare("UPDATE pro_requests SET status = 'Approved' WHERE id = ?");
        $update_request_stmt->execute([$request_id]);
    }
    header("Location: admin_pro_requests.php");
    exit;
}

// Handle request rejection
if (isset($_GET['reject_id'])) {
    $request_id = $_GET['reject_id'];
    $update_request_stmt = $pdo->prepare("UPDATE pro_requests SET status = 'Rejected' WHERE id = ?");
    $update_request_stmt->execute([$request_id]);
    header("Location: admin_pro_requests.php");
    exit;
}

// Fetch all pending PRO requests
$requests_stmt = $pdo->query("SELECT pr.*, u.first_name, u.last_name FROM pro_requests pr JOIN users u ON pr.user_id = u.id WHERE pr.status = 'Pending' ORDER BY pr.requested_at DESC");
$requests = $requests_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRO Requests</title>
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .table-container {
            background: var(--bg-sidebar);
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { font-size: 0.9rem; color: var(--text-secondary); text-transform: uppercase; font-weight: 600; }
        tr:hover { background-color: var(--bg-main); }
        .action-btns a { color: var(--brand-purple); margin-right: 0.5rem; }
        .action-btns a.approve { color: var(--brand-green); }
        .action-btns a.reject { color: #EF4444; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-header"><a href="admin_dashboard.php" class="logo">Admin Panel</a></div>
            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                <a href="admin_dashboard.php" class="nav-item"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
                <a href="admin_users.php" class="nav-item"><i class="fas fa-users"></i><span>Manage Users</span></a>
                <a href="admin_tasks.php" class="nav-item"><i class="fas fa-briefcase"></i><span>Manage Gigs</span></a>
                <a href="admin_promotions.php" class="nav-item"><i class="fas fa-rocket"></i><span>Promotions</span></a>
                <a href="admin_transactions.php" class="nav-item"><i class="fas fa-credit-card"></i><span>Transactions</span></a>
                <a href="admin_reports.php" class="nav-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
                <a href="admin_pro_requests.php" class="nav-item active"><i class="fas fa-star"></i><span>PRO Requests</span></a>
            </nav>
            <div class="sidebar-footer"><a href="../logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a></div>
        </aside>
        <main class="main-content">
            <header class="main-header"><h1>PRO Requests</h1><p>Review and approve PRO subscription payments.</p></header>
            <section class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr><td colspan="6" style="text-align: center; color: var(--text-secondary);">No pending PRO requests.</td></tr>
                        <?php else: ?>
                            <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['id']); ?></td>
                                <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['user_email']); ?></td>
                                <td><?php echo htmlspecialchars($request['plan_details']); ?></td>
                                <td><?php echo (new DateTime($request['requested_at']))->format('M j, Y H:i'); ?></td>
                                <td class="action-btns">
                                    <a href="admin_pro_requests.php?approve_id=<?php echo $request['id']; ?>" class="approve" onclick="return confirm('Approve this request?');"><i class="fas fa-check-circle"></i> Approve</a>
                                    <a href="admin_pro_requests.php?reject_id=<?php echo $request['id']; ?>" class="reject" onclick="return confirm('Reject this request?');"><i class="fas fa-times-circle"></i> Reject</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>