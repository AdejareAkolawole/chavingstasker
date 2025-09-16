<?php
// admin/admin_payouts.php
require_once 'admin_check.php';
require_once '../db.php';

// Handle 'Pay' action
if (isset($_GET['pay_id'])) {
    $payout_id = $_GET['pay_id'];
    $stmt = $pdo->prepare("SELECT user_id FROM payout_requests WHERE id = ?");
    $stmt->execute([$payout_id]);
    $payout = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($payout) {
        $user_id = $payout['user_id'];
        
        // Update all pending referrals for this user to 'Paid'
        $update_referrals_stmt = $pdo->prepare("UPDATE referrals SET status = 'Paid' WHERE referrer_id = ? AND status = 'Pending'");
        $update_referrals_stmt->execute([$user_id]);

        // Update the payout request status to 'Paid'
        $update_payout_stmt = $pdo->prepare("UPDATE payout_requests SET status = 'Paid' WHERE id = ?");
        $update_payout_stmt->execute([$payout_id]);
    }
    header("Location: admin_payouts.php");
    exit;
}

// Fetch all pending payout requests
$payouts_stmt = $pdo->query("SELECT pr.*, u.first_name, u.last_name, u.email FROM payout_requests pr JOIN users u ON pr.user_id = u.id WHERE pr.status = 'Pending' ORDER BY pr.request_date ASC");
$pending_payouts = $payouts_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payouts</title>
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .table-container { background: var(--bg-sidebar); border-radius: var(--radius); border: 1px solid var(--border-color); padding: 1.5rem; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { font-size: 0.9rem; color: var(--text-secondary); text-transform: uppercase; font-weight: 600; }
        tr:hover { background-color: var(--bg-main); }
        .action-btns a { color: var(--brand-purple); margin-right: 0.5rem; }
        .action-btns a.pay { color: var(--brand-green); }
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
                 <a href="admin_payouts.php" class="nav-item active"><i class="fas fa-money-check-alt"></i><span>Payouts</span></a>
            </nav>
            <div class="sidebar-footer"><a href="../logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a></div>
        </aside>
        <main class="main-content">
            <header class="main-header"><h1>Pending Payouts</h1><p>Review and process user cash out requests.</p></header>
            <section class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Amount</th>
                            <th>Request Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pending_payouts)): ?>
                            <tr><td colspan="6" style="text-align: center; color: var(--text-secondary);">No pending payout requests.</td></tr>
                        <?php else: ?>
                            <?php foreach ($pending_payouts as $payout): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payout['id']); ?></td>
                                <td><?php echo htmlspecialchars($payout['first_name'] . ' ' . $payout['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($payout['email']); ?></td>
                                <td>₦<?php echo number_format($payout['amount'], 2); ?></td>
                                <td><?php echo (new DateTime($payout['request_date']))->format('M j, Y H:i'); ?></td>
                                <td class="action-btns">
                                    <a href="admin_payouts.php?pay_id=<?php echo $payout['id']; ?>" class="pay" onclick="return confirm('Confirm you have paid this user?');"><i class="fas fa-check-circle"></i> Pay</a>
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