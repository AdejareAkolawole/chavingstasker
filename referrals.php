<?php
// referrals.php (Updated with cash out logic)
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// --- Handle Cash Out Request ---
$cash_out_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cash_out'])) {
    // Check if a pending request already exists
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM payout_requests WHERE user_id = ? AND status = 'Pending'");
    $check_stmt->execute([$user_id]);
    if ($check_stmt->fetchColumn() > 0) {
        $cash_out_message = "You have a pending payout request. Please wait for it to be processed.";
    } else {
        // Calculate total unpaid earnings
        $unpaid_earnings_stmt = $pdo->prepare("SELECT SUM(reward_amount) AS total FROM referrals WHERE referrer_id = ? AND status = 'Pending'");
        $unpaid_earnings_stmt->execute([$user_id]);
        $unpaid_earnings = $unpaid_earnings_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Only allow cash out if balance is >= 1000
        if ($unpaid_earnings >= 1000) {
            // Create a new payout request
            $insert_stmt = $pdo->prepare("INSERT INTO payout_requests (user_id, amount) VALUES (?, ?)");
            $insert_stmt->execute([$user_id, $unpaid_earnings]);
            $cash_out_message = "Cash out request submitted successfully! We will process it shortly.";
        } else {
            $cash_out_message = "Minimum cash out amount is ₦1,000.";
        }
    }
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch referrals for the current user
$referrals_stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name FROM referrals r JOIN users u ON r.referred_user_id = u.id WHERE r.referrer_id = ? ORDER BY r.referred_at DESC");
$referrals_stmt->execute([$user_id]);
$my_referrals = $referrals_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total earnings from referrals
$total_referred_earnings_stmt = $pdo->prepare("SELECT SUM(reward_amount) AS total FROM referrals WHERE referrer_id = ? AND status = 'Paid'");
$total_referred_earnings_stmt->execute([$user_id]);
$total_referred_earnings = $total_referred_earnings_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Calculate pending earnings from referrals
$pending_earnings_stmt = $pdo->prepare("SELECT SUM(reward_amount) AS total FROM referrals WHERE referrer_id = ? AND status = 'Pending'");
$pending_earnings_stmt->execute([$user_id]);
$pending_earnings = $pending_earnings_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refer & Earn - Chavings Tasker</title>
    <link rel="stylesheet" href="promote_gig.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .referral-link-container {
            background: var(--bg-main); border: 1px solid var(--border-color); border-radius: var(--radius); padding: 1.5rem; text-align: center; margin-bottom: 2rem;
        }
        .referral-link-container h3 { font-size: 1.2rem; margin-bottom: 0.5rem; }
        .referral-link-input { display: flex; align-items: center; justify-content: center; margin-top: 1rem; }
        .referral-link-input input {
            flex-grow: 1; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-right: none; border-radius: 8px 0 0 8px; background-color: var(--bg-sidebar); color: var(--text-primary); font-family: 'Inter', sans-serif;
        }
        .referral-link-input button {
            background-color: var(--brand-purple); color: white; padding: 0.75rem 1rem; border: 1px solid var(--brand-purple); border-left: none; border-radius: 0 8px 8px 0; cursor: pointer; transition: var(--transition);
        }
        .referral-link-input button:hover { background-color: var(--brand-purple-dark); }
        .referral-list-item {
            display: flex; justify-content: space-between; align-items: center; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 0.75rem; background: var(--bg-main); transition: var(--transition);
        }
        .referral-list-item.paid {
            border-left: 4px solid var(--brand-green);
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-header"><a href="dashboard.php" class="logo">Chavings Tasker</a></div>
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
                <a href="referrals.php" class="nav-item active"><i class="fas fa-users"></i><span>Refer & Earn</span></a>
            </nav>
            <div class="sidebar-footer">
                <a href="profile.php" class="nav-item"><i class="fas fa-user-circle"></i><span>Profile</span></a>
                <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-greeting"><h1>Refer & Earn</h1><p>Invite friends to join and get rewarded for every new user!</p></div>
                <div class="header-actions"><button class="theme-toggle" id="theme-toggle-btn"></button></div>
            </header>
            
            <?php if (!empty($cash_out_message)): ?>
                <div class="alert success"><?php echo htmlspecialchars($cash_out_message); ?></div>
            <?php endif; ?>

            <div class="referral-link-container">
                <h3>Your Referral Link</h3>
                <p>Share this link with your friends. When they sign up, you'll earn ₦100!</p>
                <div class="referral-link-input">
                    <input type="text" id="referral-link" value="http://localhost/chavings_tasker/signup.php?ref=<?php echo $user_id; ?>" readonly>
                    <button onclick="copyLink()"><i class="fas fa-copy"></i></button>
                </div>
            </div>

            <section class="stats-grid">
                <div class="stat-card">
                    <div class="card-icon gigs"><i class="fas fa-user-plus"></i></div>
                    <div class="card-info">
                        <span class="card-title">Pending Earnings</span>
                        <span class="card-value">₦<?php echo number_format($pending_earnings, 2); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="card-icon earnings"><i class="fas fa-money-bill-alt"></i></div>
                    <div class="card-info">
                        <span class="card-title">Total Cashed Out</span>
                        <span class="card-value">₦<?php echo number_format($total_referred_earnings, 2); ?></span>
                    </div>
                </div>
                 <div class="stat-card">
                    <div class="card-icon messages"><i class="fas fa-user-check"></i></div>
                    <div class="card-info">
                        <span class="card-title">Total Referrals</span>
                        <span class="card-value"><?php echo count($my_referrals); ?></span>
                    </div>
                </div>
            </section>
            
            <div class="content-panel" style="margin-top: 2rem;">
                <div class="panel-header">
                    <h2>Referral History</h2>
                    <form action="referrals.php" method="post" style="display:inline;">
                        <?php if ($pending_earnings >= 1000): ?>
                             <button type="submit" name="cash_out" class="cta-btn">Cash Out ₦<?php echo number_format($pending_earnings, 2); ?></button>
                        <?php else: ?>
                            <p class="cta-btn secondary" style="cursor:not-allowed;">Cash Out (₦1,000 min)</p>
                        <?php endif; ?>
                    </form>
                </div>
                 <?php if (empty($my_referrals)): ?>
                    <p class="empty-state">You have not referred any users yet.</p>
                 <?php else: ?>
                    <div class="gig-list">
                        <?php foreach ($my_referrals as $referral): ?>
                            <div class="referral-list-item <?php echo strtolower($referral['status']); ?>">
                                <div class="item-main-info">
                                    <h3><?php echo htmlspecialchars($referral['first_name'] . ' ' . $referral['last_name']); ?></h3>
                                    <div class="item-tags">
                                        <span class="tag">Status: <?php echo htmlspecialchars($referral['status']); ?></span>
                                        <span class="tag">Date: <?php echo (new DateTime($referral['referred_at']))->format('M j, Y'); ?></span>
                                    </div>
                                </div>
                                <div class="item-action">
                                     <strong>+₦<?php echo number_format($referral['reward_amount'], 2); ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                 <?php endif; ?>
            </div>

        </main>
    </div>

    <script>
        function copyLink() {
            const linkInput = document.getElementById('referral-link');
            linkInput.select();
            linkInput.setSelectionRange(0, 99999);
            document.execCommand('copy');
            alert("Referral link copied to clipboard!");
        }

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