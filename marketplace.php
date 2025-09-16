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

// Handle item posting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $category = 'Marketplace';
    $attachment = null;

    // Handle file upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'Uploads/marketplace/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB
        if (in_array($_FILES['attachment']['type'], $allowed_types) && $_FILES['attachment']['size'] <= $max_size) {
            $file_name = uniqid() . '_' . basename($_FILES['attachment']['name']);
            $attachment = $upload_dir . $file_name;
            if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $attachment)) {
                $error = "Failed to upload attachment.";
            }
        } else {
            $error = "Invalid file type or size. Allowed: JPEG, PNG, PDF (max 5MB).";
        }
    }

    if (empty($title)) {
        $error = "Please enter an item title.";
    } elseif ($price <= 0) {
        $error = "Please enter a valid price.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, budget, category, status, attachment) VALUES (?, ?, ?, ?, ?, 'Open', ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $price, $category, $attachment]);
            $success = "Item posted successfully!";
            header("Location: marketplace.php");
            exit;
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Fetch marketplace items
$items_stmt = $pdo->prepare("
    SELECT t.*, u.first_name, u.last_name
    FROM tasks t
    JOIN users u ON t.user_id = u.id
    WHERE t.category = 'Marketplace' AND t.status = 'Open'
    ORDER BY t.created_at DESC
    LIMIT 20
");
$items_stmt->execute();
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Marketplace - Chavings Tasker</title>
    <link rel="stylesheet" href="marketplace.css">
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
            <a href="marketplace.php" class="active" aria-label="Go to Marketplace"><i class="fas fa-store"></i> <span>Marketplace</span></a>
            <a href="post_ad.php" aria-label="Go to Post Ad"><i class="fas fa-plus-circle"></i> <span>Post Ad</span></a>
            <a href="search.php" aria-label="Go to Search"><i class="fas fa-search"></i> <span>Search</span></a>
            <a href="profile.php" aria-label="Go to Profile"><i class="fas fa-user"></i> <span>Profile</span></a>
            <a href="logout.php" aria-label="Log Out"><i class="fas fa-sign-out-alt"></i> <span>Log Out</span></a>
        </nav>
    </div>
    <div class="main-content">
        <header class="header">
            <h2>Marketplace</h2>
            <a href="logout.php" class="cta-btn" aria-label="Log out">Log Out</a>
        </header>
        <section class="marketplace-section" role="main">
            <div class="container">
                <button class="toggle-form-btn cta-btn" onclick="toggleForm()" aria-label="Toggle post item form"><i class="fas fa-plus-circle"></i> Post an Item</button>
                <div class="item-form" id="itemForm" style="display: none;">
                    <?php if (isset($success)): ?>
                        <p class="success" role="alert"><?php echo htmlspecialchars($success); ?></p>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                        <p class="error" role="alert"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <form action="marketplace.php" method="post" enctype="multipart/form-data">
                        <h3>Post an Item</h3>
                        <div class="input-group">
                            <label for="title"><i class="fas fa-tag"></i> Item Title</label>
                            <input type="text" name="title" id="title" placeholder="Enter item title" required>
                        </div>
                        <div class="input-group">
                            <label for="description"><i class="fas fa-align-left"></i> Description</label>
                            <textarea name="description" id="description" placeholder="Describe the item"></textarea>
                        </div>
                        <div class="input-group">
                            <label for="price"><i class="fas fa-money-bill"></i> Price (₦)</label>
                            <input type="number" name="price" id="price" placeholder="Enter price" step="0.01" required>
                        </div>
                        <div class="input-group">
                            <label for="attachment"><i class="fas fa-paperclip"></i> Attachment</label>
                            <input type="file" name="attachment" id="attachment" accept="image/*,.pdf">
                        </div>
                        <button type="submit" class="cta-btn" aria-label="Post item">Post Item</button>
                    </form>
                </div>
                <h3>Marketplace Feed</h3>
                <?php if (empty($items)): ?>
                    <p>No items available. Be the first to post one!</p>
                <?php else: ?>
                    <div class="items-list">
                        <?php foreach ($items as $item): ?>
                            <div class="feed-card">
                                <div class="card-header">
                                    <i class="fas fa-store"></i>
                                    <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                                </div>
                                <?php if ($item['attachment']): ?>
                                    <div class="attachment-preview">
                                        <?php
                                        $ext = strtolower(pathinfo($item['attachment'], PATHINFO_EXTENSION));
                                        if (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['attachment']); ?>" alt="Item image" class="item-image">
                                        <?php else: ?>
                                            <a href="<?php echo htmlspecialchars($item['attachment']); ?>" target="_blank" aria-label="View attachment">View Attachment (<?php echo strtoupper($ext); ?>)</a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <p><strong>Seller:</strong> <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?></p>
                                <p><strong>Price:</strong> ₦<?php echo number_format($item['budget'], 2); ?></p>
                                <p><?php echo htmlspecialchars($item['description'] ?: 'No description provided.'); ?></p>
                                <div class="card-actions">
                                    <a href="messages.php?recipient_id=<?php echo $item['user_id']; ?>" class="cta-btn secondary" aria-label="Contact <?php echo htmlspecialchars($item['first_name']); ?>">Contact Seller</a>
                                    <a href="view_profile.php?user_id=<?php echo $item['user_id']; ?>" class="cta-btn secondary" aria-label="View profile of <?php echo htmlspecialchars($item['first_name']); ?>">View Profile</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
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

        function toggleForm() {
            const form = document.getElementById('itemForm');
            const btn = document.querySelector('.toggle-form-btn i');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            btn.className = form.style.display === 'none' ? 'fas fa-plus-circle' : 'fas fa-times-circle';
        }

        document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
    </script>
</body>
</html>