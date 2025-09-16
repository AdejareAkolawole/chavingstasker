<?php
// search.php
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$search_query = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_STRING);

// Fetch user data for sidebar
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Search for gigs
$gigs_stmt = $pdo->prepare("SELECT t.*, u.first_name, u.last_name FROM tasks t JOIN users u ON t.user_id = u.id WHERE t.status = 'Open' AND (t.title LIKE ? OR t.description LIKE ?) ORDER BY t.created_at DESC");
$gigs_stmt->execute(["%$search_query%", "%$search_query%"]);
$gigs_results = $gigs_stmt->fetchAll(PDO::FETCH_ASSOC);

// Search for users (Lancers)
$lancers_stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'Lancer' AND (first_name LIKE ? OR last_name LIKE ? OR skills LIKE ? OR bio LIKE ?)");
$lancers_stmt->execute(["%$search_query%", "%$search_query%", "%$search_query%", "%$search_query%"]);
$lancer_results = $lancers_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
        /* Custom styles for search results */
        .result-card {
            background: var(--bg-sidebar);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
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
                <a href="dashboard.php" class="nav-item"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
                <a href="apply_job.php" class="nav-item"><i class="fas fa-briefcase"></i><span>Browse Gigs</span></a>
                <a href="post_ad.php" class="nav-item"><i class="fas fa-plus-circle"></i><span>Post a Gig</span></a>
                <a href="messages.php" class="nav-item"><i class="fas fa-comments"></i><span>Messages</span></a>
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
                    <h1>Search Results</h1>
                    <p>Found <?php echo count($gigs_results) + count($lancer_results); ?> results for "<?php echo htmlspecialchars($search_query); ?>"</p>
                </div>
            </header>

            <div class="content-panel" style="margin-bottom: 2rem;">
                <div class="panel-header"><h2>Gigs</h2></div>
                <div class="activity-list">
                    <?php if (empty($gigs_results)): ?>
                        <p class="empty-state">No gigs found matching your search.</p>
                    <?php else: ?>
                        <?php foreach ($gigs_results as $gig): ?>
                             <div class="activity-item">
                                <div class="item-icon public"><i class="fas fa-bullhorn"></i></div>
                                <div class="item-details">
                                    <p><strong><?php echo htmlspecialchars($gig['title']); ?></strong></p>
                                    <span>Budget: ₦<?php echo number_format($gig['budget'], 2); ?> by <?php echo htmlspecialchars($gig['first_name']); ?></span>
                                </div>
                                <a href="#" class="item-action"><i class="fas fa-chevron-right"></i></a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="content-panel">
                <div class="panel-header"><h2>Lancers</h2></div>
                <div class="activity-list">
                    <?php if (empty($lancer_results)): ?>
                        <p class="empty-state">No Lancers found matching your search.</p>
                    <?php else: ?>
                        <?php foreach ($lancer_results as $lancer): ?>
                            <div class="activity-item">
                                <div class="item-icon"><i class="fas fa-user-circle"></i></div>
                                <div class="item-details">
                                    <p><strong><?php echo htmlspecialchars($lancer['first_name'] . ' ' . $lancer['last_name']); ?></strong></p>
                                    <span><?php echo htmlspecialchars($lancer['role']); ?>: <?php echo htmlspecialchars($lancer['skills'] ?? 'No skills listed'); ?></span>
                                </div>
                                <a href="#" class="item-action"><i class="fas fa-chevron-right"></i></a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script>
        // (Your theme toggle script)
    </script>
</body>
</html>