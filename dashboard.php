<?php
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch recent tasks (placeholder)
$tasks_stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$tasks_stmt->execute([$_SESSION['user_id']]);
$tasks = $tasks_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Chavings Tasker</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">Chavings Tasker</div>
            <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        </div>
        <nav class="sidebar-nav">
            <a href="#balance" class="active"><i class="fas fa-wallet"></i> <span>Balance</span></a>
            <a href="#apply-job"><i class="fas fa-briefcase"></i> <span>Apply for Job</span></a>
            <a href="#post-ad"><i class="fas fa-plus-circle"></i> <span>Post Ad</span></a>
            <a href="#messages"><i class="fas fa-envelope"></i> <span>Messages</span></a>
            <a href="#profile"><i class="fas fa-user"></i> <span>Profile</span></a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Log Out</span></a>
        </nav>
    </div>
    <div class="main-content">
        <header class="header">
            <h2>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
            <a href="logout.php" class="cta-btn">Log Out</a>
        </header>
        <section class="dashboard-section">
            <div class="container">
                <div class="balance-card">
                    <h3>Your Wallet Balance</h3>
                    <div class="balance-container">
                        <p class="balance-amount" id="balance-amount">₦<?php echo number_format($user['balance'], 2); ?></p>
                        <button class="toggle-balance" onclick="toggleBalance()">
                            <i class="fas fa-eye" id="balance-toggle-icon"></i>
                        </button>
                    </div>
                    <div class="balance-actions">
                        <a href="#top-up" class="cta-btn">Top Up</a>
                        <a href="#withdraw" class="cta-btn secondary">Withdraw</a>
                    </div>
                </div>
                <div class="action-boxes">
                    <a href="#apply-job" class="action-box">
                        <i class="fas fa-briefcase"></i>
                        <h4>Apply for Job</h4>
                        <p>Browse and apply for available tasks.</p>
                    </a>
                    <a href="#post-ad" class="action-box">
                        <i class="fas fa-plus-circle"></i>
                        <h4>Post Ad</h4>
                        <p>Create a new task for professionals.</p>
                    </a>
                    <a href="#messages" class="action-box">
                        <i class="fas fa-envelope"></i>
                        <h4>Messages</h4>
                        <p>Chat with clients or lancers.</p>
                    </a>
                    <a href="#profile" class="action-box">
                        <i class="fas fa-user"></i>
                        <h4>Profile</h4>
                        <p>Manage your account details.</p>
                    </a>
                </div>
                <div class="recent-activity">
                    <h3>Recent Activity</h3>
                    <?php if (empty($tasks)): ?>
                        <p>No recent tasks. Start by posting or applying!</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($tasks as $task): ?>
                                <li>
                                    <strong><?php echo htmlspecialchars($task['title']); ?></strong> 
                                    (<?php echo $task['status']; ?>) - 
                                    <?php echo date('M d, Y', strtotime($task['created_at'])); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }

        function toggleBalance() {
            const balance = document.getElementById('balance-amount');
            const icon = document.getElementById('balance-toggle-icon');
            const isHidden = balance.textContent === '****';
            if (isHidden) {
                balance.textContent = '₦<?php echo number_format($user['balance'], 2); ?>';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                localStorage.setItem('balanceVisible', 'true');
            } else {
                balance.textContent = '****';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                localStorage.setItem('balanceVisible', 'false');
            }
        }

        window.onload = function() {
            const balanceVisible = localStorage.getItem('balanceVisible') === 'true';
            if (!balanceVisible) {
                document.getElementById('balance-amount').textContent = '****';
                document.getElementById('balance-toggle-icon').classList.remove('fa-eye');
                document.getElementById('balance-toggle-icon').classList.add('fa-eye-slash');
            }
        };
    </script>
</body>
</html>