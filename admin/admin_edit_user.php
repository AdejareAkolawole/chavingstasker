<?php
// admin/admin_edit_user.php
require_once 'admin_check.php';
require_once '../db.php';

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header("Location: admin_users.php");
    exit;
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: admin_users.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $verified = isset($_POST['verified']) ? 1 : 0;

    $update_stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, is_admin = ?, verified = ? WHERE id = ?");
    $update_stmt->execute([$first_name, $last_name, $email, $role, $is_admin, $verified, $user_id]);
    header("Location: admin_users.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .form-container {
            background: var(--bg-sidebar);
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        input[type="text"], input[type="email"], select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--bg-main);
            color: var(--text-primary);
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem; /* Adjust spacing */
        }
        .checkbox-group input {
            width: auto;
        }
        .submit-btn {
            background: var(--brand-purple);
            color: white;
            padding: 0.75rem 1.25rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        .submit-btn:hover {
            background: var(--brand-purple-dark);
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
                    <h1>Edit User: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                </div>
            </header>

            <section class="form-container">
                <form action="admin_edit_user.php?id=<?php echo $user['id']; ?>" method="POST">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                     <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="Client" <?php echo ($user['role'] === 'Client') ? 'selected' : ''; ?>>Client</option>
                            <option value="Lancer" <?php echo ($user['role'] === 'Lancer') ? 'selected' : ''; ?>>Lancer</option>
                        </select>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="is_admin" name="is_admin" <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
                        <label for="is_admin">Is Admin? (Green Tick)</label>
                    </div>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="verified" name="verified" <?php echo $user['verified'] ? 'checked' : ''; ?>>
                        <label for="verified">Is Verified? (Blue Tick)</label>
                    </div>

                    <button type="submit" class="submit-btn">Save Changes</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>