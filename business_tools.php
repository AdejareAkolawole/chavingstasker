<?php
// business_tools.php (Updated with better modal UI)
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
$success_message = '';

// --- Handle Add Event (for Lancers) ---
if ($is_lancer && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $event_date = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    if ($title && $event_date) {
        $insert_stmt = $pdo->prepare("INSERT INTO events (user_id, title, event_date, location, description) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->execute([$user_id, $title, $event_date, $location, $description]);
        $success_message = "New event added successfully!";
    }
}

// --- Handle Delete Event (for Lancers) ---
if ($is_lancer && isset($_GET['delete_event_id'])) {
    $event_id = filter_input(INPUT_GET, 'delete_event_id', FILTER_SANITIZE_NUMBER_INT);
    $delete_stmt = $pdo->prepare("DELETE FROM events WHERE id = ? AND user_id = ?");
    $delete_stmt->execute([$event_id, $user_id]);
    header("Location: business_tools.php");
    exit;
}

// --- Fetch user's Events (for Lancers) ---
$events = [];
if ($is_lancer) {
    $events_stmt = $pdo->prepare("SELECT * FROM events WHERE user_id = ? ORDER BY event_date DESC");
    $events_stmt->execute([$user_id]);
    $events = $events_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- Handle Scout Request (for Clients) ---
if (strtolower($user['role']) === 'client' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_scout'])) {
    $project_description = filter_input(INPUT_POST, 'project_description', FILTER_SANITIZE_STRING);
    if (!empty($project_description)) {
        $stmt = $pdo->prepare("INSERT INTO scout_requests (client_id, project_description) VALUES (?, ?)");
        $stmt->execute([$user_id, $project_description]);
        $success_message = "Talent Scout request submitted! Our team will get back to you shortly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Tools - Chavings Tasker</title>
    <link rel="stylesheet" href="promote_gig.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .tool-card {
            background: var(--bg-sidebar);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .tool-card h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .tool-card .icon-wrapper {
            background: rgba(109, 40, 217, 0.1);
            color: var(--brand-purple);
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .tool-card p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        .tool-card .cta-btn {
            margin-top: auto;
        }
        
        .planner-container {
            margin-top: 1rem;
        }
        .planner-container h3 {
            font-size: 1.2rem;
            font-family: 'Poppins', sans-serif;
            margin-bottom: 1rem;
        }
        .planner-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 0.75rem;
            background: var(--bg-main);
            transition: var(--transition);
        }
        .planner-item:hover {
            box-shadow: var(--shadow-sm);
        }
        .planner-item-details strong {
            display: block;
            font-weight: 600;
        }
        .planner-item-details span {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        /* New Modal Styling */
        #add-event-modal {
            padding: 0 !important; /* Remove padding from the modal card */
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 1rem;
        }
        .modal-header h3 {
            margin-bottom: 0;
            font-size: 1.5rem;
        }
        .modal-header .close-modal {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.3s;
        }
        .modal-header .close-modal:hover {
            color: var(--text-primary);
        }
        
        .modal-body {
            padding: 0 2rem 2rem;
        }
        
        .modal-body .input-group {
            margin-bottom: 1rem;
        }
        
        .modal-body .form-actions {
            margin-top: 1.5rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
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
                <a href="business_tools.php" class="nav-item active premium"><i class="fas fa-tools"></i><span>Business Tools</span></a>
                <a href="referrals.php" class="nav-item"><i class="fas fa-users"></i><span>Refer & Earn</span></a>
            </nav>
            <div class="sidebar-footer">
                <a href="profile.php" class="nav-item"><i class="fas fa-user-circle"></i><span>Profile</span></a>
                <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-greeting"><h1>Business Tools</h1><p>Tools to help you manage your projects and grow your business.</p></div>
                <div class="header-actions"><button class="theme-toggle" id="theme-toggle-btn"></button></div>
            </header>
            
            <?php if (isset($success_message)): ?><div class="alert success"><?php echo htmlspecialchars($success_message); ?></div><?php endif; ?>

            <?php if ($is_lancer): ?>
                <div class="tools-grid">
                    <div class="tool-card" id="planner-card">
                        <div class="icon-wrapper"><i class="fas fa-calendar-alt"></i></div>
                        <h3>Project Planner</h3>
                        <p>Keep track of your project deadlines and important events.</p>
                        <button class="cta-btn secondary" onclick="toggleModal('add-event-modal')">Add New Event</button>
                    </div>

                    <div class="tool-card">
                        <div class="icon-wrapper"><i class="fas fa-upload"></i></div>
                        <h3>CV & Portfolio Manager</h3>
                        <p>Upload and manage your professional documents and portfolio items.</p>
                        <a href="profile.php" class="cta-btn secondary">Go to Profile</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="icon-wrapper"><i class="fas fa-store"></i></div>
                        <h3>Marketplace</h3>
                        <p>Buy and sell digital assets, tools, and resources with other Lancers.</p>
                        <button class="cta-btn secondary">Browse Marketplace</button>
                    </div>
                </div>

                <div class="content-panel" style="margin-top: 2rem;">
                     <div class="panel-header"><h3>Upcoming Events</h3></div>
                     <?php if (empty($events)): ?>
                        <p class="empty-state">You have no upcoming events.</p>
                     <?php else: ?>
                        <div class="planner-container">
                            <?php foreach ($events as $event): ?>
                                <div class="planner-item">
                                    <div class="planner-item-details">
                                        <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                        <span><i class="fas fa-clock"></i> <?php echo (new DateTime($event['event_date']))->format('M j, Y H:i'); ?></span>
                                    </div>
                                    <div class="item-action">
                                        <a href="business_tools.php?delete_event_id=<?php echo $event['id']; ?>" class="cta-btn small secondary" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                     <?php endif; ?>
                </div>

            <?php else: ?>
                <div class="content-panel">
                    <div class="panel-header"><h2><i class="fas fa-search"></i> Talent Scout</h2></div>
                    <p class="modal-subtitle">Need help finding the perfect Lancer for your project? We've got you covered.</p>
                    <div class="feature-explanation">
                         <div class="feature-box"><i class="fas fa-stopwatch"></i><h3>Save Time & Effort</h3><p>Stop scrolling through profiles. We'll find the perfect match for you.</p></div>
                         <div class="feature-box"><i class="fas fa-user-check"></i><h3>Hire with Confidence</h3><p>We hand-pick from our pool of top-rated, identity-verified professionals.</p></div>
                         <div class="feature-box"><i class="fas fa-star"></i><h3>Access Elite Talent</h3><p>Get matched with Lancers who have a proven track record of success.</p></div>
                    </div>
                    <form action="business_tools.php" method="post" class="scout-form">
                        <p>For a one-time fee of **₦10,000**, our expert team will analyze your project needs and deliver a shortlist of 3-5 top-tier Lancers ready to start work. Just tell us what you need below.</p>
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

    <div class="modal-overlay" id="add-event-modal-overlay" onclick="toggleModal('add-event-modal')"></div>
    <div class="modal-card" id="add-event-modal">
        <div class="modal-header">
            <h3>Add New Event</h3>
            <button type="button" class="close-modal" onclick="toggleModal('add-event-modal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form action="business_tools.php" method="post">
                <input type="hidden" name="add_event" value="1">
                 <div class="input-group">
                    <label for="title">Event Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                 <div class="input-group">
                    <label for="event_date">Date & Time</label>
                    <input type="datetime-local" id="event_date" name="event_date" required>
                </div>
                 <div class="input-group">
                    <label for="location">Location (Optional)</label>
                    <input type="text" id="location" name="location">
                </div>
                <div class="input-group">
                    <label for="description">Event Notes</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-actions">
                     <button type="button" class="cta-btn secondary" onclick="toggleModal('add-event-modal')">Cancel</button>
                     <button type="submit" class="cta-btn">Save Event</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleModal(modalId) {
            document.getElementById(modalId + '-overlay').classList.toggle('active');
            document.getElementById(modalId).classList.toggle('active');
        }

        // Theme Toggle Script
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