<?php
// featured_profile.php (Updated with new button and logic)
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$is_lancer = (strtolower($user['role']) === 'lancer');

$is_featured = false;
if ($is_lancer && !empty($user['featured_until']) && $user['featured_until'] !== '0000-00-00 00:00:00') {
    try {
        if (new DateTime($user['featured_until']) > new DateTime()) {
            $is_featured = true;
        }
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Featured Profile - Chavings Tasker</title>
    <link rel="stylesheet" href="featured_profile.css">
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
                <a href="apply_job.php" class="nav-item"><i class="fas fa-briefcase"></i><span>Browse Gigs</span></a>
                <a href="post_ad.php" class="nav-item"><i class="fas fa-plus-circle"></i><span>Post a Gig</span></a>
                <a href="messages.php" class="nav-item"><i class="fas fa-comments"></i><span>Messages</span></a>
                <a href="wallet.php" class="nav-item"><i class="fas fa-wallet"></i><span>Wallet</span></a>
                <p class="nav-section-title">Grow Your Business</p>
                <a href="promote_gig.php" class="nav-item premium"><i class="fas fa-rocket"></i><span>Promote Gig</span></a>
                <a href="featured_profile.php" class="nav-item active premium"><i class="fas fa-star"></i><span>Featured Profile</span></a>
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
                <div class="header-greeting"><h1>Featured Profile (PRO)</h1><p>Stand out from the competition and win more clients.</p></div>
            </header>

            <?php if (!$is_lancer): ?>
                <div class="content-panel"><p>The Featured Profile service is available for Lancers. Switch to your Lancer profile to subscribe!</p></div>
            <?php else: ?>
                
                <div class="status-panel <?php echo $is_featured ? 'active' : 'inactive'; ?>">
                    <?php if ($is_featured): ?>
                        <i class="fas fa-check-circle"></i>
                        <div class="status-text">
                            <h3>PRO Status: Active</h3>
                            <p>Your Featured Profile badge is active until <?php echo date('F j, Y', strtotime($user['featured_until'])); ?>.</p>
                        </div>
                    <?php else: ?>
                        <i class="fas fa-star"></i>
                        <div class="status-text">
                            <h3>Upgrade to PRO</h3>
                            <p>Get a verified badge, appear higher in search results, and get hired faster.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="feature-explanation">
                    <div class="feature-box"><i class="fas fa-user-check"></i><h3>PRO Badge</h3><p>Build trust instantly with a prominent PRO badge on your profile and gigs.</p></div>
                    <div class="feature-box"><i class="fas fa-search-plus"></i><h3>Higher Ranking</h3><p>Appear higher in search results when clients are looking for Lancers with your skills.</p></div>
                    <div class="feature-box"><i class="fas fa-trophy"></i><h3>Win More Gigs</h3><p>Featured Lancers get up to 3x more profile views and messages from clients.</p></div>
                </div>
                
                <div class="pricing-grid">
                    <div class="pricing-card">
                        <h3>Monthly</h3>
                        <p class="price">₦2,000<span>/month</span></p>
                        <p class="desc">Perfect for getting started and boosting your profile visibility.</p>
                        <button class="cta-btn" onclick="openPaymentModal('Monthly PRO Subscription', 2000, 'monthly')">Subscribe Now</button>
                    </div>
                    <div class="pricing-card recommended">
                        <span class="badge">Recommended</span>
                        <h3>Yearly</h3>
                        <p class="price">₦20,000<span>/year</span></p>
                        <p class="desc">Save ₦4,000! The best value for long-term growth and success.</p>
                        <button class="cta-btn" onclick="openPaymentModal('Yearly PRO Subscription', 20000, 'yearly')">Subscribe Now</button>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <div class="modal-overlay" id="payment-modal-overlay" onclick="closePaymentModal()"></div>
    <div class="modal-card" id="payment-modal">
        <h3>Manual Payment Instruction</h3>
        <div class="payment-details">
            <div class="detail-item"><span>Amount to Pay</span><strong id="modal-amount"></strong></div>
            <div class="detail-item"><span>Bank Name</span><strong>OPay</strong></div>
            <div class="detail-item"><span>Account Name</span><strong>Adejare Akolawole</strong></div>
            <div class="detail-item"><span>Account Number</span><strong>6142080244</strong></div>
        </div>
        <a href="#" id="whatsapp-link" target="_blank" class="cta-btn whatsapp-btn"><i class="fab fa-whatsapp"></i> I Have Paid, Send Receipt</a>
        <button id="request-button" class="cta-btn request-btn" style="display: none;"><i class="fas fa-paper-plane"></i> Send Admin Request</button>
        <button type="button" class="cta-btn secondary" onclick="closePaymentModal()">Cancel</button>
    </div>

    <script>
        function openPaymentModal(planName, amount, planType) {
            const amountFormatted = new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(amount);
            document.getElementById('modal-amount').innerText = amountFormatted;
            
            // Show the "Send Admin Request" button
            document.getElementById('request-button').style.display = 'block';

            // Prepare the data for the new button
            document.getElementById('request-button').onclick = function() {
                sendAdminRequest(planType);
            };

            const whatsappNumber = "2348154371207";
            const message = `Hello, I have paid ${amountFormatted} for the ${planName} on Chavings Tasker.\n\nMy User Email: <?php echo $user['email']; ?>\n\nPlease find my receipt attached for approval. Thank you.`;
            const whatsappLink = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
            
            document.getElementById('whatsapp-link').href = whatsappLink;
            document.getElementById('payment-modal-overlay').classList.add('active');
            document.getElementById('payment-modal').classList.add('active');
        }

        function closePaymentModal() {
            document.getElementById('payment-modal-overlay').classList.remove('active');
            document.getElementById('payment-modal').classList.remove('active');
        }

        function sendAdminRequest(planType) {
            const userId = <?php echo $user_id; ?>;
            const userEmail = "<?php echo $user['email']; ?>";
            const planDetails = planType === 'monthly' ? 'Monthly PRO Subscription (₦2,000)' : 'Yearly PRO Subscription (₦20,000)';

            // Here we'll use a simple fetch API call to send the request to the server
            // We need a server-side script to handle this
            fetch('handle_pro_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ userId, userEmail, planDetails })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert("Admin request sent successfully! We will review your payment and activate your PRO status shortly.");
                    closePaymentModal();
                } else {
                    alert("Failed to send request. Please try again or contact support.");
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("An error occurred. Please contact support via WhatsApp.");
            });
        }
    </script>
    <style>
        .request-btn {
            background-color: var(--brand-purple);
            color: white;
            justify-content: center;
            width: 100%;
        }
    </style>
</body>
</html>