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

// Handle event creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $event_date = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    try {
        $stmt = $pdo->prepare("INSERT INTO events (user_id, title, description, event_date, location) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $title, $description, $event_date, $location]);
        $success = "Event created successfully!";
        header("Location: events.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch events
$events_stmt = $pdo->prepare("
    SELECT e.*, u.first_name, u.last_name
    FROM events e
    JOIN users u ON e.user_id = u.id
    WHERE e.event_date >= NOW()
    ORDER BY e.event_date ASC
    LIMIT 10
");
$events_stmt->execute();
$events = $events_stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Events - Chavings Tasker</title>
    <link rel="stylesheet" href="events.css">
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
            <a href="events.php" class="active" aria-label="Go to Events"><i class="fas fa-calendar-alt"></i> <span>Events</span></a>
            <a href="marketplace.php" aria-label="Go to Marketplace"><i class="fas fa-store"></i> <span>Marketplace</span></a>
            <a href="post_ad.php" aria-label="Go to Post Ad"><i class="fas fa-plus-circle"></i> <span>Post Ad</span></a>
            <a href="search.php" aria-label="Go to Search"><i class="fas fa-search"></i> <span>Search</span></a>
            <a href="profile.php" aria-label="Go to Profile"><i class="fas fa-user"></i> <span>Profile</span></a>
            <a href="logout.php" aria-label="Log Out"><i class="fas fa-sign-out-alt"></i> <span>Log Out</span></a>
        </nav>
    </div>
    <div class="main-content">
        <header class="header">
            <h2>Events</h2>
            <a href="logout.php" class="cta-btn" aria-label="Log out">Log Out</a>
        </header>
        <section class="events-section" role="main">
            <div class="container">
                <?php if (isset($success)): ?>
                    <p class="success" role="alert"><?php echo htmlspecialchars($success); ?></p>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <p class="error" role="alert"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <form action="events.php" method="post" class="event-form">
                    <h3>Create New Event</h3>
                    <div class="input-group">
                        <label for="title"><i class="fas fa-calendar"></i> Event Title</label>
                        <input type="text" name="title" id="title" placeholder="Enter event title" required>
                    </div>
                    <div class="input-group">
                        <label for="description"><i class="fas fa-align-left"></i> Description</label>
                        <textarea name="description" id="description" placeholder="Describe the event"></textarea>
                    </div>
                    <div class="input-group">
                        <label for="event_date"><i class="fas fa-clock"></i> Event Date & Time</label>
                        <input type="datetime-local" name="event_date" id="event_date" required>
                    </div>
                    <div class="input-group">
                        <label for="location"><i class="fas fa-map-marker-alt"></i> Location</label>
                        <input type="text" name="location" id="location" placeholder="Enter location">
                    </div>
                    <button type="submit" class="cta-btn" aria-label="Create event">Create Event</button>
                </form>
                <h3>Upcoming Events</h3>
                <?php if (empty($events)): ?>
                    <p>No upcoming events. Create one above!</p>
                <?php else: ?>
                    <div class="events-list">
                        <?php foreach ($events as $event): ?>
                            <div class="feed-card">
                                <div class="card-header">
                                    <i class="fas fa-calendar-alt"></i>
                                    <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                </div>
                                <p><strong>Host:</strong> <?php echo htmlspecialchars($event['first_name'] . ' ' . $event['last_name']); ?></p>
                                <p><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($event['event_date'])); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location'] ?: 'Not specified'); ?></p>
                                <p><?php echo htmlspecialchars($event['description'] ?: 'No description provided.'); ?></p>
                                <a href="view_profile.php?user_id=<?php echo $event['user_id']; ?>" class="cta-btn secondary" aria-label="View profile of <?php echo htmlspecialchars($event['first_name']); ?>">View Host Profile</a>
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

        document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
    </script>
</body>
</html>