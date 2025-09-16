<?php
// admin/admin_transactions.php
require_once 'admin_check.php';
require_once '../db.php';

// Fetch all transactions with user details
$transactions_stmt = $pdo->query("SELECT t.*, u.first_name, u.last_name, u.email FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
$transactions = $transactions_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Transactions</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        th {
            font-size: 0.9rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 600;
        }
        tr:hover {
            background-color: var(--bg-main);
        }
        .type-credit { color: var(--brand-green); font-weight: 600; }
        .type-debit { color: #EF4444; font-weight: 600; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="admin_dashboard.php" class="logo">Admin Panel</a>
            </div>
            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                <a href="admin_dashboard.php" class="nav-item"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
                <a href="admin_users.php" class="nav-item"><i class="fas fa-users"></i><span>Manage Users</span></a>
                <a href="admin_tasks.php" class="nav-item"><i class="fas fa-briefcase"></i><span>Manage Gigs</span></a>
                <a href="admin_promotions.php" class="nav-item"><i class="fas fa-rocket"></i><span>Promotions</span></a>
                <a href="admin_transactions.php" class="nav-item active"><i class="fas fa-credit-card"></i><span>Transactions</span></a>
                <a href="admin_reports.php" class="nav-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
            </nav>
            <div class="sidebar-footer">
                <a href="../logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-greeting">
                    <h1>All Transactions</h1>
                    <p>Financial overview of all platform transactions.</p>
                </div>
            </header>

            <section class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['email']); ?></td>
                                <td>₦<?php echo number_format($transaction['amount'], 2); ?></td>
                                <td class="type-<?php echo strtolower($transaction['type']); ?>"><?php echo htmlspecialchars(ucfirst($transaction['type'])); ?></td>
                                <td><?php echo htmlspecialchars($transaction['description'] ?? 'N/A'); ?></td>
                                <td><?php echo (new DateTime($transaction['created_at']))->format('M j, Y H:i'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>