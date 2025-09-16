<?php
// admin/admin_dashboard.php
require_once 'admin_check.php';
require_once '../db.php';

// Fetch key metrics for the entire platform
$users_count_stmt = $pdo->query("SELECT COUNT(*) AS count FROM users");
$users_count = $users_count_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

$tasks_count_stmt = $pdo->query("SELECT COUNT(*) AS count FROM tasks");
$tasks_count = $tasks_count_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

$applications_count_stmt = $pdo->query("SELECT COUNT(*) AS count FROM applications");
$applications_count = $applications_count_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

$total_transactions_stmt = $pdo->query("SELECT COUNT(*) AS count FROM transactions");
$total_transactions = $total_transactions_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Fetch recent platform activity
$recent_tasks_stmt = $pdo->query("SELECT t.*, u.first_name, u.last_name FROM tasks t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 5");
$recent_tasks = $recent_tasks_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="admin_dashboard.php" class="logo">Admin Panel</a>
            </div>
            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                <a href="admin_dashboard.php" class="nav-item active"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
                <a href="admin_users.php" class="nav-item"><i class="fas fa-users"></i><span>Manage Users</span></a>
                <a href="admin_tasks.php" class="nav-item"><i class="fas fa-briefcase"></i><span>Manage Gigs</span></a>
                <a href="admin_promotions.php" class="nav-item"><i class="fas fa-rocket"></i><span>Promotions</span></a>
                <a href="admin_payouts.php" class="nav-item"><i class="fa fa-file-invoice-dollar"></i><span>Payouts </span></a>
                <a href="admin_pro_requests.php" class="nav-item"><i class="fa fa-hand-paper"></i><span>Pro Request</span></a>
                <a href="admin_verifications.php" class="nav-item"><i class="fas fa-chart-bar"></i><span>Verifications</span></a>
                <a href="admin_transactions.php" class="nav-item"><i class="fas fa-credit-card"></i><span>Transactions</span></a>
                <a href="admin_reports.php" class="nav-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
            </nav>
            <div class="sidebar-footer">
                <a href="../logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-greeting">
                    <h1>Admin Overview</h1>
                    <p>Global statistics for Chavings Tasker.</p>
                </div>
            </header>

            <section class="stats-grid">
                <div class="stat-card">
                    <div class="card-icon gigs"><i class="fas fa-users"></i></div>
                    <div class="card-info">
                        <span class="card-title">Total Users</span>
                        <span class="card-value"><?php echo $users_count; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="card-icon gigs"><i class="fas fa-briefcase"></i></div>
                    <div class="card-info">
                        <span class="card-title">Total Gigs Posted</span>
                        <span class="card-value"><?php echo $tasks_count; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="card-icon gigs"><i class="fas fa-file-alt"></i></div>
                    <div class="card-info">
                        <span class="card-title">Total Applications</span>
                        <span class="card-value"><?php echo $applications_count; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="card-icon messages"><i class="fas fa-credit-card"></i></div>
                    <div class="card-info">
                        <span class="card-title">Transactions</span>
                        <span class="card-value"><?php echo $total_transactions; ?></span>
                    </div>
                </div>
            </section>
            
            <section class="main-area">
                <div class="content-panel">
                    <div class="panel-header">
                        <h2>Newly Posted Gigs</h2>
                         <a href="admin_tasks.php" class="view-all-link">Manage All</a>
                    </div>
                    <div class="activity-list">
                         <?php foreach ($recent_tasks as $task): ?>
                        <div class="activity-item">
                            <div class="item-icon public"><i class="fas fa-bullhorn"></i></div>
                            <div class="item-details">
                                <p><strong><?php echo htmlspecialchars($task['title']); ?></strong></p>
                                <span>By <?php echo htmlspecialchars($task['first_name'] . ' ' . $task['last_name']); ?></span>
                            </div>
                            <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $task['status'])); ?>"><?php echo htmlspecialchars($task['status']); ?></span>
                            <a href="admin_edit_task.php?id=<?php echo $task['id']; ?>" class="item-action"><i class="fas fa-chevron-right"></i></a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>