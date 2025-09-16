<?php
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize all variables to prevent "undefined" warnings
$user = [];
$user_tasks = [];
$public_tasks = [];
$applications = [];
$unread_count = 0;
$total_earnings = 0;
$total_spent = 0;
$active_gigs_count = 0;

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Proceed only if user data is found
if ($user) {
    // Fetch user's tasks
    $tasks_stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $tasks_stmt->execute([$_SESSION['user_id']]);
    $user_tasks = $tasks_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch recent platform-wide tasks (public feed)
    $public_tasks_stmt = $pdo->prepare("SELECT t.*, u.first_name, u.last_name FROM tasks t JOIN users u ON t.user_id = u.id WHERE t.status = 'Open' ORDER BY t.created_at DESC LIMIT 10");
    $public_tasks_stmt->execute();
    $public_tasks = $public_tasks_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch recent applications (for Clients) - check role case-insensitively
    if (strtolower($user['role']) === 'client') {
        $applications_stmt = $pdo->prepare("SELECT a.*, t.title, u.first_name, u.last_name, u.email FROM applications a JOIN tasks t ON a.task_id = t.id JOIN users u ON a.lancer_id = u.id WHERE t.user_id = ? ORDER BY a.created_at DESC LIMIT 5");
        $applications_stmt->execute([$_SESSION['user_id']]);
        $applications = $applications_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate total spent
        $spent_stmt = $pdo->prepare("SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND type = 'debit'");
        $spent_stmt->execute([$_SESSION['user_id']]);
        $total_spent = $spent_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    } // Check role case-insensitively for Lancer
    elseif (strtolower($user['role']) === 'lancer') {
        // Calculate total earnings
        $earnings_stmt = $pdo->prepare("SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND type = 'credit'");
        $earnings_stmt->execute([$_SESSION['user_id']]);
        $total_earnings = $earnings_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    // Fetch unread message count
    $unread_stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE receiver_id = ? AND is_read = FALSE");
    $unread_stmt->execute([$_SESSION['user_id']]);
    $unread_count = $unread_stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

    // Active Gigs/Jobs
    $active_gigs_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status = 'In Progress'");
    $active_gigs_stmt->execute([$_SESSION['user_id']]);
    $active_gigs_count = $active_gigs_stmt->fetch(PDO::FETCH_ASSOC)['count'];
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Chavings Tasker</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .search-form {
            flex-grow: 1;
            margin: 0 1rem;
            max-width: 400px;
        }
        .search-input-group {
            display: flex;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }
        .search-input-group input {
            flex-grow: 1;
            border: none;
            padding: 0.75rem 1rem;
            background-color: var(--bg-sidebar);
            color: var(--text-primary);
        }
        .search-input-group input:focus {
            outline: none;
        }
        .search-input-group button {
            border: none;
            background-color: var(--brand-purple);
            color: white;
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .search-input-group button:hover {
            background-color: var(--brand-purple-dark);
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="logo">Chavings Tasker</a>
            </div>
            <nav class="sidebar-nav">
                <p class="nav-section-title">Menu</p>
                <a href="dashboard.php" class="nav-item active"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
                <a href="apply_job.php" class="nav-item"><i class="fas fa-briefcase"></i><span>Browse Gigs</span></a>
                <a href="post_ad.php" class="nav-item"><i class="fas fa-plus-circle"></i><span>Post a Gig</span></a>
                <a href="messages.php" class="nav-item">
                    <i class="fas fa-comments"></i><span>Messages</span>
                    <?php if ($unread_count > 0): ?>
                        <span class="notification-badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="wallet.php" class="nav-item"><i class="fas fa-wallet"></i><span>Wallet</span></a>
                
                <p class="nav-section-title">Grow Your Business</p>
                <a href="promote_gig.php" class="nav-item premium"><i class="fas fa-rocket"></i><span>Promote Gig</span></a>
                <a href="featured_profile.php" class="nav-item premium"><i class="fas fa-star"></i><span>Featured Profile</span></a>
                <a href="business_tools.php" class="nav-item premium"><i class="fas fa-tools"></i><span>Business Tools</span></a>
                <a href="referrals.php" class="nav-item"><i class="fas fa-users"></i><span>Refer & Earn</span></a>
                <a href="verification.php" class="nav-item"><i class="fas fa-user-shield"></i><span>Verification</span></a>
            </nav>
            <div class="sidebar-footer">
                 <a href="profile.php" class="nav-item"><i class="fas fa-user-circle"></i><span>Profile</span></a>
                <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-greeting">
                    <h1>Welcome back, <?php echo htmlspecialchars($user['first_name'] ?? 'User'); ?>!</h1>
                    <p>Here's what's happening with your account today.</p>
                </div>
                <form action="search.php" method="get" class="search-form">
                    <div class="search-input-group">
                        <input type="text" name="query" placeholder="Search for gigs, Lancers, or clients..." required>
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </form>
                <div class="header-actions">
                    <button class="theme-toggle" id="theme-toggle-btn" aria-label="Toggle Theme"></button>
                    <a href="post_ad.php" class="cta-btn"><i class="fas fa-plus"></i> Post a New Gig</a>
                </div>
            </header>

            <section class="stats-grid">
                <div class="stat-card">
                    <div class="card-icon balance"><i class="fas fa-wallet"></i></div>
                    <div class="card-info">
                        <span class="card-title">Account Balance</span>
                        <span class="card-value">₦<?php echo number_format($user['balance'] ?? 0, 2); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="card-icon gigs"><i class="fas fa-briefcase"></i></div>
                    <div class="card-info">
                        <span class="card-title">Active Gigs</span>
                        <span class="card-value"><?php echo $active_gigs_count; ?></span>
                    </div>
                </div>
                <?php if (strtolower($user['role']) === 'client'): ?>
                <div class="stat-card">
                    <div class="card-icon spent"><i class="fas fa-arrow-down"></i></div>
                    <div class="card-info">
                        <span class="card-title">Total Spent</span>
                        <span class="card-value">₦<?php echo number_format($total_spent, 2); ?></span>
                    </div>
                </div>
                <?php else: ?>
                <div class="stat-card">
                    <div class="card-icon earnings"><i class="fas fa-arrow-up"></i></div>
                    <div class="card-info">
                        <span class="card-title">Total Earnings</span>
                        <span class="card-value">₦<?php echo number_format($total_earnings, 2); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                <div class="stat-card">
                    <div class="card-icon messages"><i class="fas fa-comments"></i></div>
                    <div class="card-info">
                        <span class="card-title">Unread Messages</span>
                        <span class="card-value"><?php echo $unread_count; ?></span>
                    </div>
                </div>
            </section>
            
            <section class="main-area">
                <div class="content-panel">
                    <div class="panel-header">
                        <h2>Your Recent Activity</h2>
                        <a href="#" class="view-all-link">View All</a>
                    </div>
                    <div class="activity-list">
                        <?php if (strtolower($user['role']) === 'client' && !empty($applications)): ?>
                            <?php foreach ($applications as $app): ?>
                            <div class="activity-item">
                                <div class="item-icon"><i class="fas fa-user-check"></i></div>
                                <div class="item-details">
                                    <p><strong><?php echo htmlspecialchars($app['first_name']); ?></strong> applied to your gig</p>
                                    <span><?php echo htmlspecialchars($app['title']); ?></span>
                                </div>
                                <a href="#" class="item-action"><i class="fas fa-chevron-right"></i></a>
                            </div>
                            <?php endforeach; ?>
                        <?php elseif (strtolower($user['role']) === 'lancer' && !empty($user_tasks)): ?>
                             <?php foreach ($user_tasks as $task): ?>
                            <div class="activity-item">
                                <div class="item-icon"><i class="fas fa-file-alt"></i></div>
                                <div class="item-details">
                                    <p>You posted the gig: <strong><?php echo htmlspecialchars($task['title']); ?></strong></p>
                                    <span>Status: <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $task['status'])); ?>"><?php echo htmlspecialchars($task['status']); ?></span></span>
                                </div>
                                <a href="#" class="item-action"><i class="fas fa-chevron-right"></i></a>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="activity-item empty">
                                <p>No recent activity. Post or apply for a gig to get started!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="content-panel">
                    <div class="panel-header">
                        <h2>Newly Posted Gigs</h2>
                         <a href="apply_job.php" class="view-all-link">Browse All</a>
                    </div>
                    <div class="activity-list">
                         <?php foreach ($public_tasks as $task): ?>
                        <div class="activity-item">
                            <div class="item-icon public"><i class="fas fa-bullhorn"></i></div>
                            <div class="item-details">
                                <p><strong><?php echo htmlspecialchars($task['title']); ?></strong></p>
                                <span>Budget: ₦<?php echo number_format($task['budget'], 2); ?> by <?php echo htmlspecialchars($task['first_name']); ?></span>
                            </div>
                            <a href="#" class="item-action"><i class="fas fa-chevron-right"></i></a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <script>
        // --- Theme Toggle ---
        const themeToggleBtn = document.getElementById('theme-toggle-btn');
        const sunIcon = `<i class="fas fa-sun"></i>`;
        const moonIcon = `<i class="fas fa-moon"></i>`;

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            themeToggleBtn.innerHTML = theme === 'dark' ? sunIcon : moonIcon;
            localStorage.setItem('theme', theme);
        }

        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            applyTheme(currentTheme === 'light' ? 'dark' : 'light');
        });
        
        applyTheme(localStorage.getItem('theme') || 'light');
    </script>
</body>
</html>