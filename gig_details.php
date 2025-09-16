<?php
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

$task_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$task_id) {
    header("Location: apply_job.php");
    exit;
}

// Fetch the specific task and the user who posted it
$stmt = $pdo->prepare("SELECT t.*, u.first_name, u.last_name, u.profile_picture FROM tasks t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    header("Location: apply_job.php");
    exit;
}

// --- CORRECTED TIME AGO FUNCTION ---
function time_ago($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    
    $weeks = floor($diff->d / 7);
    if ($weeks > 0) return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    
    return 'just now';
}
// --- END OF FIX ---
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($task['title']); ?> - Chavings Tasker</title>
    <link rel="stylesheet" href="gig_details.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-header"><a href="dashboard.php" class="logo">Chavings Tasker</a></div>
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
                    <a href="apply_job.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Gigs</a>
                    <h1><?php echo htmlspecialchars($task['title']); ?></h1>
                    <p>Posted <?php echo time_ago($task['created_at']); ?> by <?php echo htmlspecialchars($task['first_name']); ?></p>
                </div>
                </header>
            
            <div class="details-layout">
                <div class="content-panel gig-description">
                    <h3>Gig Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                </div>

                <div class="gig-sidebar">
                    <div class="content-panel action-panel">
                        <div class="budget-display">
                            <span>Budget</span>
                            <p>₦<?php echo number_format($task['budget'], 2); ?></p>
                        </div>
                        <div class="deadline-display">
                            <i class="fas fa-clock"></i>
                            <span>Deadline: <?php echo date('F j, Y', strtotime($task['deadline'])); ?></span>
                        </div>
                        <button class="cta-btn apply-btn">Apply Now</button>
                        <a href="messages.php?user_id=<?php echo $task['user_id']; ?>" class="cta-btn secondary message-btn">
                            <i class="fas fa-comments"></i> Message Client
                        </a>
                    </div>
                    <div class="content-panel client-panel">
                        <h3>About the Client</h3>
                        <div class="client-info">
                            <img src="<?php echo htmlspecialchars($task['profile_picture'] ?? 'default-avatar.png'); ?>" alt="Client profile picture" class="client-avatar">
                            <div class="client-name">
                                <strong><?php echo htmlspecialchars($task['first_name'] . ' ' . $task['last_name']); ?></strong>
                                <a href="profile.php?id=<?php echo $task['user_id']; ?>">View Profile</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>