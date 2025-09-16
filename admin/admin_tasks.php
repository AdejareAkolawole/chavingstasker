<?php
// admin/admin_tasks.php
require_once 'admin_check.php';
require_once '../db.php';

// Handle task deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("Location: admin_tasks.php");
    exit;
}

// Fetch all tasks with the user's name
$tasks_stmt = $pdo->query("SELECT t.*, u.first_name, u.last_name FROM tasks t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
$tasks = $tasks_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gigs</title>
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
        .action-btns a {
            color: var(--brand-purple);
            margin-right: 0.5rem;
        }
        .action-btns a:hover {
            color: var(--brand-purple-dark);
        }
        .delete-btn {
            color: #EF4444;
        }
        .delete-btn:hover {
            color: #B91C1C;
        }
        .status-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-badge.open { background-color: rgba(16, 185, 129, 0.1); color: var(--brand-green); }
        .status-badge.in-progress { background-color: rgba(59, 130, 246, 0.1); color: #3B82F6; }
        .status-badge.completed { background-color: rgba(132, 142, 156, 0.1); color: var(--text-secondary); }
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
                <a href="admin_tasks.php" class="nav-item active"><i class="fas fa-briefcase"></i><span>Manage Gigs</span></a>
                <a href="admin_promotions.php" class="nav-item"><i class="fas fa-rocket"></i><span>Promotions</span></a>
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
                    <h1>Manage Gigs</h1>
                    <p>View and manage all posted gigs on the platform.</p>
                </div>
            </header>

            <section class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Budget</th>
                            <th>Status</th>
                            <th>Client</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['id']); ?></td>
                                <td><?php echo htmlspecialchars($task['title']); ?></td>
                                <td><?php echo htmlspecialchars($task['category']); ?></td>
                                <td>₦<?php echo number_format($task['budget'], 2); ?></td>
                                <td><span class="status-badge <?php echo strtolower(str_replace(' ', '-', $task['status'])); ?>"><?php echo htmlspecialchars($task['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($task['first_name'] . ' ' . $task['last_name']); ?></td>
                                <td class="action-btns">
                                    <a href="admin_edit_task.php?id=<?php echo $task['id']; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="admin_tasks.php?delete_id=<?php echo $task['id']; ?>" class="delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this task?');"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>