<?php
// admin/admin_users.php
require_once 'admin_check.php';
require_once '../db.php';

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("Location: admin_users.php");
    exit;
}

// Fetch all users
$users_stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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
                <a href="admin_users.php" class="nav-item active"><i class="fas fa-users"></i><span>Manage Users</span></a>
                <a href="admin_tasks.php" class="nav-item"><i class="fas fa-briefcase"></i><span>Manage Gigs</span></a>
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
                    <h1>Manage Users</h1>
                    <p>View, edit, and delete user accounts.</p>
                </div>
            </header>

            <section class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Admin</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td><?php echo $user['is_admin'] ? 'Yes' : 'No'; ?></td>
                                <td class="action-btns">
                                    <a href="admin_edit_user.php?id=<?php echo $user['id']; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="admin_users.php?delete_id=<?php echo $user['id']; ?>" class="delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fas fa-trash-alt"></i></a>
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