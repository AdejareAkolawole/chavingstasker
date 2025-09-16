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

// Handle friend request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['friend_email'])) {
    $friend_email = $_POST['friend_email'];
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$friend_email]);
    $friend = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($friend && $friend['id'] != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$_SESSION['user_id'], $friend['id']]);
        $success = "Friend request sent!";
    } else {
        $error = "User not found or invalid request.";
    }
}

// Handle accept/reject friend request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['friend_id'])) {
    $friend_id = $_POST['friend_id'];
    $action = $_POST['action'];
    $status = $action === 'accept' ? 'accepted' : 'rejected';
    $stmt = $pdo->prepare("UPDATE friends SET status = ? WHERE user_id = ? AND friend_id = ?");
    $stmt->execute([$status, $friend_id, $_SESSION['user_id']]);
}

// Fetch pending requests
$pending_stmt = $pdo->prepare("
    SELECT f.*, u.first_name, u.last_name, u.email
    FROM friends f
    JOIN users u ON f.user_id = u.id
    WHERE f.friend_id = ? AND f.status = 'pending'
");
$pending_stmt->execute([$_SESSION['user_id']]);
$pending_requests = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch friends list
$friends_stmt = $pdo->prepare("
    SELECT u.*
    FROM friends f
    JOIN users u ON f.friend_id = u.id
    WHERE f.user_id = ? AND f.status = 'accepted'
    UNION
    SELECT u.*
    FROM friends f
    JOIN users u ON f.user_id = u.id
    WHERE f.friend_id = ? AND f.status = 'accepted'
");
$friends_stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$friends = $friends_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch unread message count
$unread_stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE receiver_id = ? AND is_read = FALSE");
$unread_stmt->execute([$_SESSION['user_id']]);
$unread_count = $unread_stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends - Chavings Tasker</title>
    <link rel="stylesheet" href="friends.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <button class="theme-toggle" onclick="toggleTheme()" aria-label="Toggle light/dark mode"><i class="fas fa-moon"></i></button>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">Chavings Tasker</div>
            <button class="toggle-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar"><i class="fas fa-bars"></i></button>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class="fas fa-home"></i> <span>Feeds</span></a>
            <a href="friends.php" class="active"><i class="fas fa-user-friends"></i> <span>Friends</span></a>
            <a href="apply_job.php"><i class="fas fa-briefcase"></i> <span>Jobs</span></a>
            <a href="messages.php"><i class="fas fa-envelope"></i> <span>Chats</span>
                <?php if ($unread_count > 0): ?>
                    <span class="message-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="events.php"><i class="fas fa-calendar-alt"></i> <span>Events</span></a>
            <a href="marketplace.php"><i class="fas fa-store"></i> <span>Marketplace</span></a>
            <a href="post_ad.php"><i class="fas fa-plus-circle"></i> <span>Ads</span></a>
            <a href="search.php"><i class="fas fa-search"></i> <span>Search</span></a>
            <a href="profile.php"><i class="fas fa-user"></i> <span>Profile</span></a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Log Out</span></a>
        </nav>
    </div>
    <div class="main-content">
        <header class="header">
            <h2>Friends</h2>
            <a href="logout.php" class="cta-btn" aria-label="Log out">Log Out</a>
        </header>
        <section class="dashboard-section">
            <div class="container">
                <h3>Add a Friend</h3>
                <?php if (isset($success)): ?>
                    <p class="success"><?php echo htmlspecialchars($success); ?></p>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <form method="POST" class="input-group">
                    <label for="friend_email"><i class="fas fa-user-plus"></i> Friend's Email</label>
                    <input type="email" name="friend_email" id="friend_email" placeholder="Enter friend's email" required aria-required="true">
                    <button type="submit" class="cta-btn" aria-label="Send friend request">Send Request</button>
                </form>
                <h3>Pending Friend Requests</h3>
                <?php if (empty($pending_requests)): ?>
                    <p>No pending friend requests.</p>
                <?php else: ?>
                    <?php foreach ($pending_requests as $request): ?>
                        <div class="feed-card">
                            <div class="card-header">
                                <i class="fas fa-user-plus"></i>
                                <h4><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></h4>
                            </div>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($request['email']); ?></p>
                            <form method="POST" class="friend-actions">
                                <input type="hidden" name="friend_id" value="<?php echo $request['user_id']; ?>">
                                <button type="submit" name="action" value="accept" class="cta-btn" aria-label="Accept friend request">Accept</button>
                                <button type="submit" name="action" value="reject" class="cta-btn secondary" aria-label="Reject friend request">Reject</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <h3>Your Friends</h3>
                <?php if (empty($friends)): ?>
                    <p>You have no friends yet. Start adding some!</p>
                <?php else: ?>
                    <?php foreach ($friends as $friend): ?>
                        <div class="feed-card">
                            <div class="card-header">
                                <i class="fas fa-user"></i>
                                <h4><?php echo htmlspecialchars($friend['first_name'] . ' ' . $friend['last_name']); ?></h4>
                            </div>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($friend['email']); ?></p>
                            <a href="view_profile.php?user_id=<?php echo $friend['id']; ?>" class="cta-btn secondary" aria-label="View profile of <?php echo htmlspecialchars($friend['first_name']); ?>">View Profile</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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