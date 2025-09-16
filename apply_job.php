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

// Handle job application (for Lancers)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_job'])) {
    $task_id = filter_input(INPUT_POST, 'task_id', FILTER_SANITIZE_NUMBER_INT);
    $lancer_id = $_SESSION['user_id'];

    // First, check if the user has already applied for this task
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE task_id = ? AND lancer_id = ?");
    $check_stmt->execute([$task_id, $lancer_id]);
    if ($check_stmt->fetchColumn() > 0) {
        $error = "You have already applied for this gig.";
    } else {
        // Get the client_id from the task
        $task_stmt = $pdo->prepare("SELECT user_id FROM tasks WHERE id = ?");
        $task_stmt->execute([$task_id]);
        $client_id = $task_stmt->fetchColumn();

        if ($client_id) {
            $stmt = $pdo->prepare("INSERT INTO applications (task_id, lancer_id, client_id) VALUES (?, ?, ?)");
            $stmt->execute([$task_id, $lancer_id, $client_id]);
            $success = "Application submitted successfully!";
        } else {
            $error = "Could not find the client for this task.";
        }
    }
}

// Fetch available tasks with poster details
$tasks_stmt = $pdo->prepare("SELECT t.*, u.first_name, u.last_name FROM tasks t JOIN users u ON t.user_id = u.id WHERE t.status = 'Open' ORDER BY t.created_at DESC");
$tasks_stmt->execute();
$tasks = $tasks_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's applications (for Lancers)
$applications = [];
if (strtolower($user['role']) === 'lancer') {
    $applications_stmt = $pdo->prepare("
        SELECT a.status, a.created_at, t.title, t.budget 
        FROM applications a 
        JOIN tasks t ON a.task_id = t.id 
        WHERE a.lancer_id = ? ORDER BY a.created_at DESC
    ");
    $applications_stmt->execute([$_SESSION['user_id']]);
    $applications = $applications_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch user's tasks with applications (for Clients)
$client_applications = [];
if (strtolower($user['role']) === 'client') {
    // This logic will be built out in the next step
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Gigs - Chavings Tasker</title>
    <link rel="stylesheet" href="apply_job.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="logo">Chavings Tasker</a>
            </div>
            <nav class="sidebar-nav">
                <p class="nav-section-title">Menu</p>
                <a href="dashboard.php" class="nav-item"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
                <a href="apply_job.php" class="nav-item active"><i class="fas fa-briefcase"></i><span>Browse Gigs</span></a>
                <a href="post_ad.php" class="nav-item"><i class="fas fa-plus-circle"></i><span>Post a Gig</span></a>
                <a href="messages.php" class="nav-item"><i class="fas fa-comments"></i><span>Messages</span></a>
                <a href="wallet.php" class="nav-item"><i class="fas fa-wallet"></i><span>Wallet</span></a>
                
                <p class="nav-section-title">Grow Your Business</p>
                <a href="promote_gig.php" class="nav-item premium"><i class="fas fa-rocket"></i><span>Promote Gig</span></a>
                <a href="featured_profile.php" class="nav-item premium"><i class="fas fa-star"></i><span>Featured Profile</span></a>
                <a href="business_tools.php" class="nav-item premium"><i class="fas fa-tools"></i><span>Business Tools</span></a>
                <a href="referrals.php" class="nav-item"><i class="fas fa-users"></i><span>Refer & Earn</span></a>
            </nav>
            <div class="sidebar-footer">
                 <a href="profile.php" class="nav-item"><i class="fas fa-user-circle"></i><span>Profile</span></a>
                <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-greeting">
                    <h1>Find Your Next Opportunity</h1>
                    <p>Browse available gigs or manage applications for your posts.</p>
                </div>
                <div class="header-actions">
                    <button class="theme-toggle" id="theme-toggle-btn" aria-label="Toggle Theme"></button>
                </div>
            </header>

            <div class="content-panel">
                 <?php if (isset($error)): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                 <?php if (isset($success)): ?><div class="alert success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
                <div class="tabs-container">
                    <button class="tab-link active" data-tab="available-gigs">Available Gigs</button>
                    <?php if (strtolower($user['role']) === 'lancer'): ?>
                        <button class="tab-link" data-tab="my-applications">My Applications</button>
                    <?php endif; ?>
                    <?php if (strtolower($user['role']) === 'client'): ?>
                        <button class="tab-link" data-tab="client-applications">Received Applications</button>
                    <?php endif; ?>
                </div>

                <div class="tab-content active" id="available-gigs">
                    <?php if (empty($tasks)): ?>
                        <p class="empty-state">No gigs available right now. Check back soon!</p>
                    <?php else: ?>
                        <div class="job-list">
                            <?php foreach ($tasks as $task): ?>
                                <div class="job-list-item">
                                    <div class="item-main-info">
                                        <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                                        <p class="item-description"><?php echo htmlspecialchars(substr($task['description'], 0, 150)); ?>...</p>
                                        <div class="item-tags">
                                            <span class="tag budget">₦<?php echo number_format($task['budget'], 2); ?></span>
                                            <span class="tag category"><?php echo htmlspecialchars($task['category']); ?></span>
                                            <span class="tag deadline"><i class="fas fa-clock"></i> <?php echo date('M d, Y', strtotime($task['deadline'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="item-action">
                                        <?php if (strtolower($user['role']) === 'lancer'): ?>
                                            <form action="apply_job.php" method="post">
                                                <input type="hidden" name="apply_job" value="1">
                                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                <button type="submit" class="cta-btn small">Apply Now</button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="gig_details.php?id=<?php echo $task['id']; ?>" class="view-details-link">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (strtolower($user['role']) === 'lancer'): ?>
                <div class="tab-content" id="my-applications">
                     <?php if (empty($applications)): ?>
                        <p class="empty-state">You haven't applied to any gigs yet.</p>
                    <?php else: ?>
                        <div class="job-list">
                            <?php foreach ($applications as $app): ?>
                                <div class="job-list-item">
                                    <div class="item-main-info">
                                        <h3><?php echo htmlspecialchars($app['title']); ?></h3>
                                        <div class="item-tags">
                                            <span class="tag budget">Budget: ₦<?php echo number_format($app['budget'], 2); ?></span>
                                        </div>
                                    </div>
                                    <div class="item-action-status">
                                         <span class="status-badge <?php echo strtolower($app['status']); ?>">
                                            <?php echo htmlspecialchars($app['status']); ?>
                                         </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (strtolower($user['role']) === 'client'): ?>
                <div class="tab-content" id="client-applications">
                    <p class="empty-state">This is where you will see applications for your gigs. We'll build this next!</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
        // Tab functionality
        const tabLinks = document.querySelectorAll('.tab-link');
        const tabContents = document.querySelectorAll('.tab-content');
        tabLinks.forEach(link => {
            link.addEventListener('click', () => {
                tabLinks.forEach(l => l.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                link.classList.add('active');
                document.getElementById(link.dataset.tab).classList.add('active');
            });
        });
        // Theme Toggle Script
        const themeToggleBtn = document.getElementById('theme-toggle-btn');
        const sunIcon = `<i class="fas fa-sun"></i>`; const moonIcon = `<i class="fas fa-moon"></i>`;
        function applyTheme(theme) { document.documentElement.setAttribute('data-theme', theme); themeToggleBtn.innerHTML = theme === 'dark' ? sunIcon : moonIcon; localStorage.setItem('theme', theme); }
        themeToggleBtn.addEventListener('click', () => { const currentTheme = document.documentElement.getAttribute('data-theme'); applyTheme(currentTheme === 'light' ? 'dark' : 'light'); });
        applyTheme(localStorage.getItem('theme') || 'light');
    </script>
</body>
</html>