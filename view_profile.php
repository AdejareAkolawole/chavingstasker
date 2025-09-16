<?php
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user_id from URL
$user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
if (!$user_id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: dashboard.php");
    exit;
}

// Set defaults for missing fields
$user['profile_picture'] = $user['profile_picture'] ?? null;
$user['bio'] = $user['bio'] ?? null;
$user['skills'] = $user['skills'] ?? null;
$user['address'] = $user['address'] ?? null;
$user['verified'] = $user['verified'] ?? false;
$user['role'] = $user['role'] ?? 'Client';

// Fetch CVs and portfolio (for Lancers)
$cvs = [];
$portfolio_items = [];
if ($user['role'] === 'Lancer') {
    try {
        $cv_stmt = $pdo->prepare("SELECT * FROM cvs WHERE lancer_id = ? ORDER BY created_at DESC");
        $cv_stmt->execute([$user_id]);
        $cvs = $cv_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error fetching CVs: " . $e->getMessage();
    }

    try {
        $portfolio_stmt = $pdo->prepare("SELECT * FROM portfolio WHERE lancer_id = ? ORDER BY created_at DESC");
        $portfolio_stmt->execute([$user_id]);
        $portfolio_items = $portfolio_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error fetching portfolio: " . $e->getMessage();
    }
}

// Fetch reviews and ratings
$reviews = [];
$avg_rating = 0;
$review_count = 0;
try {
    $reviews_stmt = $pdo->prepare("
        SELECT r.*, u.first_name, u.last_name
        FROM reviews r
        JOIN users u ON r.reviewer_id = u.id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
        LIMIT 3
    ");
    $reviews_stmt->execute([$user_id]);
    $reviews = $reviews_stmt->fetchAll(PDO::FETCH_ASSOC);

    $rating_stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE user_id = ?");
    $rating_stmt->execute([$user_id]);
    $rating_data = $rating_stmt->fetch(PDO::FETCH_ASSOC);
    $avg_rating = round($rating_data['avg_rating'] ?? 0, 1);
    $review_count = $rating_data['review_count'] ?? 0;
} catch (PDOException $e) {
    $error = "Error fetching reviews: " . $e->getMessage();
}

// Fetch unread message count for sidebar
$unread_stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE receiver_id = ? AND is_read = FALSE");
$unread_stmt->execute([$_SESSION['user_id']]);
$unread_count = $unread_stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>'s Profile - Chavings Tasker</title>
    <link rel="stylesheet" href="profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <button class="theme-toggle" onclick="toggleTheme()" aria-label="Toggle light or dark mode"><i class="fas fa-moon"></i></button>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">Chavings Tasker</div>
            <button class="toggle-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar"><i class="fas fa-bars"></i></button>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" aria-label="Go to Dashboard"><i class="fas fa-home"></i> <span>Dashboard</span></a>
            <a href="friends.php" aria-label="Go to Friends"><i class="fas fa-user-friends"></i> <span>Friends</span></a>
            <a href="apply_job.php" aria-label="Go to Jobs"><i class="fas fa-briefcase"></i> <span>Jobs</span></a>
            <a href="messages.php" aria-label="Go to Messages"><i class="fas fa-envelope"></i> <span>Messages</span>
                <?php if ($unread_count > 0): ?>
                    <span class="message-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="events.php" aria-label="Go to Events"><i class="fas fa-calendar-alt"></i> <span>Events</span></a>
            <a href="marketplace.php" aria-label="Go to Marketplace"><i class="fas fa-store"></i> <span>Marketplace</span></a>
            <a href="post_ad.php" aria-label="Go to Post Ad"><i class="fas fa-plus-circle"></i> <span>Post Ad</span></a>
            <a href="search.php" aria-label="Go to Search"><i class="fas fa-search"></i> <span>Search</span></a>
            <a href="profile.php" aria-label="Go to Profile"><i class="fas fa-user"></i> <span>Profile</span></a>
            <a href="logout.php" aria-label="Log Out"><i class="fas fa-sign-out-alt"></i> <span>Log Out</span></a>
        </nav>
    </div>
    <div class="main-content">
        <header class="header">
            <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>'s Profile</h2>
            <a href="logout.php" class="cta-btn" aria-label="Log out">Log Out</a>
        </header>
        <section class="profile-section" role="main">
            <div class="container">
                <?php if (isset($error)): ?>
                    <p class="error" role="alert"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <div class="profile-header">
                    <div class="profile-picture">
                        <?php if ($user['profile_picture']): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile picture">
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="profile-header-info">
                        <h1>
                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                            <?php if ($user['verified']): ?>
                                <i class="fas fa-check-circle verified-badge" title="Verified Profile"></i>
                            <?php endif; ?>
                        </h1>
                        <p class="role"><?php echo htmlspecialchars($user['role']); ?></p>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['address'] ?: 'Not set'); ?></p>
                        <p class="skills"><i class="fas fa-tools"></i> <?php echo htmlspecialchars($user['skills'] ?: 'No skills listed'); ?></p>
                        <p class="bio"><i class="fas fa-align-left"></i> <?php echo htmlspecialchars($user['bio'] ?: 'No bio provided'); ?></p>
                        <a href="messages.php?recipient_id=<?php echo $user_id; ?>" class="cta-btn" aria-label="Message user">Message</a>
                    </div>
                </div>
                <?php if ($user['role'] === 'Lancer'): ?>
                    <div class="cv-section">
                        <h3>CVs</h3>
                        <?php if (!empty($cvs)): ?>
                            <div class="cv-list">
                                <?php foreach ($cvs as $cv): ?>
                                    <div class="cv-item">
                                        <a href="<?php echo htmlspecialchars($cv['file_path']); ?>" target="_blank" aria-label="View CV"><?php echo htmlspecialchars(basename($cv['file_path'])); ?></a>
                                        <p><strong>Uploaded:</strong> <?php echo date('M d, Y', strtotime($cv['created_at'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No CVs uploaded yet.</p>
                        <?php endif; ?>
                    </div>
                    <div class="portfolio-section">
                        <h3>Portfolio</h3>
                        <?php if (!empty($portfolio_items)): ?>
                            <div class="portfolio-grid">
                                <?php foreach ($portfolio_items as $item): ?>
                                    <?php if (!empty($item['title'])): ?>
                                        <div class="portfolio-item">
                                            <h4><?php echo htmlspecialchars($item['title'] ?? 'Untitled'); ?></h4>
                                            <?php if (!empty($item['description'])): ?>
                                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($item['file_path'])): ?>
                                                <?php
                                                $ext = strtolower(pathinfo($item['file_path'], PATHINFO_EXTENSION));
                                                if (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                                                    <img src="<?php echo htmlspecialchars($item['file_path']); ?>" alt="Portfolio item" class="portfolio-image">
                                                <?php else: ?>
                                                    <a href="<?php echo htmlspecialchars($item['file_path']); ?>" target="_blank" aria-label="View portfolio file">View File (<?php echo strtoupper($ext); ?>)</a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No portfolio items added yet.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="reviews-section">
                    <h3>Reviews</h3>
                    <?php if (!empty($reviews)): ?>
                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <h4><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></h4>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p><?php echo htmlspecialchars($review['comment'] ?: 'No comment provided.'); ?></p>
                                    <p class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No reviews yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        }

        function toggleTheme() {
            const html = document.documentElement;
            const icon = document.querySelector('.theme-toggle i');
            html.setAttribute('data-theme', html.getAttribute('data-theme') === 'light' ? 'dark' : 'light');
            icon.className = html.getAttribute('data-theme') === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            localStorage.setItem('theme', html.getAttribute('data-theme'));
        }

        document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
    </script>
</body>
</html>