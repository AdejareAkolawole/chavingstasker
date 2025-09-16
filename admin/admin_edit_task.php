<?php
// admin/admin_edit_task.php
require_once 'admin_check.php';
require_once '../db.php';

$task_id = $_GET['id'] ?? null;
if (!$task_id) {
    header("Location: admin_tasks.php");
    exit;
}

// Fetch task details
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    header("Location: admin_tasks.php");
    exit;
}

// Handle form submission for editing the task
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $budget = $_POST['budget'];
    $status = $_POST['status'];
    $category = $_POST['category'];
    $promoted_until = !empty($_POST['promoted_until']) ? $_POST['promoted_until'] : null;

    // Update task in database
    $update_stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, budget = ?, status = ?, category = ?, promoted_until = ? WHERE id = ?");
    $update_stmt->execute([$title, $description, $budget, $status, $category, $promoted_until, $task_id]);

    header("Location: admin_tasks.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gig</title>
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
        input, textarea, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--bg-main);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
        }
        textarea { resize: vertical; min-height: 150px; }
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
                    <h1>Edit Gig: <?php echo htmlspecialchars($task['title']); ?></h1>
                </div>
            </header>

            <section class="form-container">
                <form action="admin_edit_task.php?id=<?php echo $task['id']; ?>" method="POST">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required><?php echo htmlspecialchars($task['description']); ?></textarea>
                    </div>
                     <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="Development" <?php echo ($task['category'] === 'Development') ? 'selected' : ''; ?>>Development</option>
                            <option value="Design" <?php echo ($task['category'] === 'Design') ? 'selected' : ''; ?>>Design</option>
                            <option value="Writing" <?php echo ($task['category'] === 'Writing') ? 'selected' : ''; ?>>Writing</option>
                            <option value="Other" <?php echo ($task['category'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="budget">Budget</label>
                        <input type="number" id="budget" name="budget" value="<?php echo htmlspecialchars($task['budget']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="Open" <?php echo ($task['status'] === 'Open') ? 'selected' : ''; ?>>Open</option>
                            <option value="In Progress" <?php echo ($task['status'] === 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="Completed" <?php echo ($task['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>

                    <hr style="margin: 2rem 0; border-color: var(--border-color);">
                    <h3 style="margin-bottom: 1rem;">Promotion</h3>
                     <div class="form-group">
                        <label for="promoted_until">Set Promotion End Date</label>
                        <input type="datetime-local" id="promoted_until" name="promoted_until" value="<?php echo $task['promoted_until'] ? date('Y-m-d\TH:i', strtotime($task['promoted_until'])) : ''; ?>">
                         <small style="color: var(--text-secondary); display:block; margin-top:0.5rem;">To disable promotion, clear the date and time field.</small>
                    </div>
                    
                    <button type="submit" class="submit-btn">Save Changes</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>