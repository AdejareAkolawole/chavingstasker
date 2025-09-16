<?php
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// (All of your existing PHP logic for fetching user data, handling top up, and withdrawals remains here)
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['top_up'])) {
    // (Top up logic)
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw'])) {
    // (Withdraw logic)
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet - Chavings Tasker</title>
    <link rel="stylesheet" href="wallet.css">
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
                <a href="apply_job.php" class="nav-item"><i class="fas fa-briefcase"></i><span>Browse Gigs</span></a>
                <a href="post_ad.php" class="nav-item"><i class="fas fa-plus-circle"></i><span>Post a Gig</span></a>
                <a href="messages.php" class="nav-item"><i class="fas fa-comments"></i><span>Messages</span></a>
                <a href="wallet.php" class="nav-item active"><i class="fas fa-wallet"></i><span>Wallet</span></a>
                
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
                    <h1>My Wallet</h1>
                    <p>Manage your balance, top up funds, and withdraw earnings.</p>
                </div>
                <div class="header-actions">
                    <button class="theme-toggle" id="theme-toggle-btn" aria-label="Toggle Theme"></button>
                </div>
            </header>

            <?php if (isset($error)): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if (isset($success)): ?><div class="alert success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

            <div class="balance-display-card">
                <span class="card-title">Current Available Balance</span>
                <span class="balance-amount">₦<?php echo number_format($user['balance'] ?? 0, 2); ?></span>
            </div>

            <div class="main-area">
                <div class="content-panel">
                    <div class="panel-header">
                        <h2><i class="fas fa-plus-circle icon-green"></i> Top Up Funds</h2>
                    </div>
                    <form action="wallet.php" method="post">
                        <input type="hidden" name="top_up" value="1">
                        <div class="input-group">
                            <label for="amount">Amount (Minimum ₦1000)</label>
                            <input type="number" name="amount" id="amount" step="100" min="1000" required>
                        </div>
                        <div class="input-group">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" required>
                                <option value="" disabled selected>Select Method</option>
                                <option value="card">Card (Paystack)</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <button type="submit" class="cta-btn green">Proceed to Top Up</button>
                    </form>
                </div>

                <div class="content-panel">
                    <div class="panel-header">
                        <h2><i class="fas fa-arrow-circle-down icon-purple"></i> Withdraw Earnings</h2>
                    </div>
                    <form action="wallet.php" method="post">
                        <input type="hidden" name="withdraw" value="1">
                        <div class="input-group">
                            <label for="withdraw_amount">Amount (Minimum ₦1000)</label>
                            <input type="number" name="amount" id="withdraw_amount" step="100" min="1000" required>
                        </div>
                        <div class="input-group">
                            <label for="account_number">Bank Account Number</label>
                            <input type="text" name="account_number" id="account_number" pattern="[0-9]{10}" placeholder="10-digit number" required>
                        </div>
                        <div class="input-group">
                            <label for="bank_name">Bank Name</label>
                            <input type="text" name="bank_name" id="bank_name" required>
                        </div>
                        <button type="submit" class="cta-btn">Request Withdrawal</button>
                    </form>
                </div>
            </div>

        </main>
    </div>

    <script>
        // (Theme toggle script from dashboard.php)
    </script>
</body>
</html>