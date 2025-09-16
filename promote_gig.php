<?php
// promote_gig.php (Updated with new logic and UI)
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

// Lancer-specific logic
if (strtolower($user['role']) === 'lancer') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promote_task'])) {
        // This part is now handled by the new JS and `handle_promotion_request.php`
        // We'll leave it for now but it's not the primary way anymore
        $task_id = filter_input(INPUT_POST, 'task_id', FILTER_SANITIZE_NUMBER_INT);
        $duration = filter_input(INPUT_POST, 'duration', FILTER_SANITIZE_NUMBER_INT);
        if ($task_id && $duration) {
            $promo_end_date = date('Y-m-d H:i:s', strtotime("+$duration days"));
            $update_stmt = $pdo->prepare("UPDATE tasks SET promoted_until = ? WHERE id = ? AND user_id = ?");
            $update_stmt->execute([$promo_end_date, $task_id, $user_id]);
            header("Location: promote_gig.php?status=promoted");
            exit;
        }
    }
    // Check for success message from redirect
    if (isset($_GET['status']) && $_GET['status'] === 'promoted') {
        $success = "Gig successfully promoted!";
    }
    // Fetch all gigs posted by this Lancer
    $gigs_stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
    $gigs_stmt->execute([$user_id]);
    $my_gigs = $gigs_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Client-specific logic (remains the same)
if (strtolower($user['role']) === 'client') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_scout'])) {
        $project_description = filter_input(INPUT_POST, 'project_description', FILTER_SANITIZE_STRING);
        if (!empty($project_description)) {
            $stmt = $pdo->prepare("INSERT INTO scout_requests (client_id, project_description) VALUES (?, ?)");
            $stmt->execute([$user_id, $project_description]);
            $success = "Talent Scout request submitted! Our team will get back to you shortly.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Growth Tools - Chavings Tasker</title>
    <link rel="stylesheet" href="promote_gig.css">
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
                <a href="promote_gig.php" class="nav-item active premium"><i class="fas fa-rocket"></i><span>Promote Gig</span></a>
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
            <?php if (strtolower($user['role']) === 'lancer'): ?>
                <header class="main-header">
                    <div class="header-greeting"><h1>Promote Your Gig</h1><p>Get your gig in front of more clients by promoting it.</p></div>
                    <div class="header-actions"><button class="theme-toggle" id="theme-toggle-btn"></button></div>
                </header>
                <?php if (isset($success)): ?><div class="alert success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
                <div class="feature-explanation">
                    <div class="feature-box"><i class="fas fa-eye"></i><h3>Increased Visibility</h3><p>Promoted gigs are shown first in search results and on the homepage.</p></div>
                    <div class="feature-box"><i class="fas fa-mouse-pointer"></i><h3>More Clicks & Applications</h3><p>Stand out from the crowd and attract more high-quality clients.</p></div>
                    <div class="feature-box"><i class="fas fa-chart-line"></i><h3>Grow Your Business</h3><p>Invest in your success and win more projects on Chavings Tasker.</p></div>
                </div>
                <div class="content-panel">
                    <div class="panel-header"><h2>Your Gigs</h2></div>
                    <div class="gig-list">
                        <?php if (empty($my_gigs)): ?>
                            <p class="empty-state">You haven't posted any gigs yet. <a href="post_ad.php">Post one now</a> to get started!</p>
                        <?php else: ?>
                            <?php foreach ($my_gigs as $gig): ?>
                                <?php
                                $is_promoted = false;
                                if (!empty($gig['promoted_until']) && $gig['promoted_until'] !== '0000-00-00 00:00:00') {
                                    try { if (new DateTime($gig['promoted_until']) > new DateTime()) { $is_promoted = true; } } catch (Exception $e) {}
                                }
                                ?>
                                <div class="gig-list-item <?php echo $is_promoted ? 'is-promoted' : ''; ?>">
                                    <div class="item-main-info">
                                        <h3><?php echo htmlspecialchars($gig['title']); ?></h3>
                                        <div class="item-tags">
                                            <?php if ($is_promoted): ?>
                                                <span class="tag promoted"><i class="fas fa-rocket"></i> Promoted until <?php echo date('M d, Y', strtotime($gig['promoted_until'])); ?></span>
                                            <?php else: ?>
                                                <span class="tag status">Status: <?php echo htmlspecialchars($gig['status']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="item-action">
                                        <?php if (!$is_promoted): ?>
                                            <div class="promote-form">
                                                <select class="duration-select" id="duration-select-<?php echo $gig['id']; ?>">
                                                    <option value="2000">7 Days (₦2,000)</option>
                                                    <option value="7500">30 Days (₦7,500)</option>
                                                </select>
                                                <button type="button" class="cta-btn small" onclick="openPaymentModal(<?php echo $gig['id']; ?>, '<?php echo htmlspecialchars(addslashes($gig['title'])); ?>')">Promote</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <header class="main-header">
                    <div class="header-greeting"><h1>Talent Scout</h1><p>Need help finding the perfect Lancer for your project? We've got you covered.</p></div>
                    <div class="header-actions"><button class="theme-toggle" id="theme-toggle-btn"></button></div>
                </header>
                <?php if (isset($success)): ?><div class="alert success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
                <div class="feature-explanation">
                     <div class="feature-box"><i class="fas fa-stopwatch"></i><h3>Save Time & Effort</h3><p>Stop scrolling through profiles. We'll find the perfect match for you.</p></div>
                     <div class="feature-box"><i class="fas fa-user-check"></i><h3>Hire with Confidence</h3><p>We hand-pick from our pool of top-rated, identity-verified professionals.</p></div>
                     <div class="feature-box"><i class="fas fa-star"></i><h3>Access Elite Talent</h3><p>Get matched with Lancers who have a proven track record of success.</p></div>
                </div>
                <div class="content-panel">
                    <div class="panel-header"><h2>How It Works</h2></div>
                    <p>For a one-time fee of **₦10,000**, our expert team will analyze your project needs and deliver a shortlist of 3-5 top-tier Lancers ready to start work. Just tell us what you need below.</p>
                    <form action="promote_gig.php" method="post" class="scout-form">
                        <div class="input-group">
                            <label for="project_description">Describe Your Project Requirements</label>
                            <textarea id="project_description" name="project_description" rows="6" placeholder="e.g., I need an experienced mobile app developer to build an e-commerce app for Android and iOS..." required></textarea>
                        </div>
                        <button type="submit" name="request_scout" class="cta-btn large">Find My Lancer Now (₦10,000)</button>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <div id="alert-modal" class="alert-modal" style="display: none;">
        <div class="alert-content">
            <span class="close-btn" onclick="closeAlertModal()">&times;</span>
            <p id="alert-message"></p>
        </div>
    </div>

    <div class="modal-overlay" id="payment-modal-overlay" onclick="closePaymentModal()"></div>
    <div class="modal-card" id="payment-modal">
        <h3>Manual Payment Instruction</h3>
        <p class="modal-subtitle">To promote your gig, please transfer the specified amount to the account below.</p>
        <div class="payment-details">
            <div class="detail-item"><span>Amount to Pay</span><strong id="modal-amount"></strong></div>
            <div class="detail-item"><span>Bank Name</span><strong>OPay</strong></div>
            <div class="detail-item"><span>Account Name</span><strong>Adejare Akolawole</strong></div>
            <div class="detail-item"><span>Account Number</span><strong>6142080244</strong></div>
        </div>
        <p class="modal-instruction">After payment, click the button below to send your receipt via WhatsApp for fast approval.</p>
        <a href="#" id="whatsapp-link" target="_blank" class="cta-btn whatsapp-btn"><i class="fab fa-whatsapp"></i> I Have Paid, Send Receipt</a>
        <button type="button" id="send-admin-request-btn" class="cta-btn small secondary" style="display:none; margin-top: 1rem;"><i class="fas fa-paper-plane"></i> Send Admin Request</button>
        <button type="button" class="cta-btn secondary" onclick="closePaymentModal()">Cancel</button>
    </div>

    <script>
        const overlay = document.getElementById('payment-modal-overlay');
        const modal = document.getElementById('payment-modal');
        const alertModal = document.getElementById('alert-modal');
        const alertMessage = document.getElementById('alert-message');
        
        let activeGigId = null;
        let activeGigTitle = '';

        function openPaymentModal(gigId, gigTitle) {
            const select = document.getElementById('duration-select-' + gigId);
            const amount = select.value;
            const amountFormatted = new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(amount);
            document.getElementById('modal-amount').innerText = amountFormatted;

            // Set active gig data for the "Send Admin Request" button
            activeGigId = gigId;
            activeGigTitle = gigTitle;
            document.getElementById('send-admin-request-btn').style.display = 'none';

            const whatsappNumber = "2348154371207";
            const message = `Hello, I have paid ${amountFormatted} to promote my gig "${gigTitle}" on Chavings Tasker.\n\nMy User Email: <?php echo $user['email']; ?>\n\nPlease find my receipt attached. Thank you.`;
            const whatsappLink = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
            document.getElementById('whatsapp-link').href = whatsappLink;

            // Add an event listener to the WhatsApp button to show the next button
            document.getElementById('whatsapp-link').addEventListener('click', function() {
                // Show the "Send Admin Request" button after the user clicks to open WhatsApp
                setTimeout(function() {
                    document.getElementById('send-admin-request-btn').style.display = 'block';
                }, 500); // Small delay to improve UX
            });

            overlay.classList.add('active');
            modal.classList.add('active');
        }

        function closePaymentModal() {
            overlay.classList.remove('active');
            modal.classList.remove('active');
        }

        function openAlertModal(message) {
            alertMessage.innerText = message;
            alertModal.style.display = 'block';
            modal.classList.remove('active');
            overlay.classList.remove('active');
        }
        
        function closeAlertModal() {
            alertModal.style.display = 'none';
        }

        // Send admin request function
        document.getElementById('send-admin-request-btn').addEventListener('click', function() {
            const select = document.getElementById('duration-select-' + activeGigId);
            const amount = select.value;
            const duration = select.options[select.selectedIndex].text.split(' ')[0]; // Extract the duration number

            fetch('handle_promotion_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    gigId: activeGigId,
                    gigTitle: activeGigTitle,
                    planDetails: `${duration} Days (₦${amount})`
                })
            })
            .then(response => response.json())
            .then(data => {
                openAlertModal(data.message);
                if (data.success) {
                    // Hide the "Promote" button for this gig after the request is sent
                    const gigListItem = document.getElementById('duration-select-' + activeGigId).closest('.gig-list-item');
                    if (gigListItem) {
                        gigListItem.querySelector('.promote-form').style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                openAlertModal("An error occurred. Please contact support via WhatsApp.");
            });
        });

        // Theme Toggle Script
        const themeToggleBtn = document.getElementById('theme-toggle-btn');
        if (themeToggleBtn) {
            // ... (full theme toggle script)
        }
    </script>
    <style>
        .alert-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            backdrop-filter: blur(5px);
        }
        .alert-content {
            background-color: var(--bg-sidebar);
            margin: 15% auto;
            padding: 2rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            width: 80%;
            max-width: 400px;
            text-align: center;
            position: relative;
        }
        .close-btn {
            color: var(--text-secondary);
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close-btn:hover, .close-btn:focus {
            color: var(--text-primary);
        }
    </style>
</body>
</html>