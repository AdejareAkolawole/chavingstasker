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

// Handle task posting
if (isset($_POST['title'])) {
    if (strtolower($user['role']) !== 'client') {
        $error = "Only Clients can post tasks.";
    } else {
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
        $budget = filter_input(INPUT_POST, 'budget', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $deadline = filter_input(INPUT_POST, 'deadline', FILTER_SANITIZE_STRING);

        // (Your existing attachment handling logic can remain here)

        if (empty($title) || empty($description) || empty($category) || empty($budget) || empty($deadline)) {
            $error = "All fields are required.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, category, budget, deadline, attachment) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $title, $description, $category, $budget, $deadline, null]); // Assuming attachment for now
                $success = "Task posted successfully! You will be redirected shortly.";
                header("Refresh: 3; url=dashboard.php");
            } catch (PDOException $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Gig - Chavings Tasker</title>
    <link rel="stylesheet" href="post_ad.css">
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
                <a href="post_ad.php" class="nav-item active"><i class="fas fa-plus-circle"></i><span>Post a Gig</span></a>
                <a href="messages.php" class="nav-item"><i class="fas fa-comments"></i><span>Messages</span></a>
                <a href="wallet.php" class="nav-item"><i class="fas fa-wallet"></i><span>Wallet</span></a>
                
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
                    <h1>Post a New Gig</h1>
                    <p>Describe your task and get connected with skilled Lancers.</p>
                </div>
                <div class="header-actions">
                    <button class="theme-toggle" id="theme-toggle-btn" aria-label="Toggle Theme"></button>
                </div>
            </header>

            <section class="form-section">
                <div class="content-panel form-panel">
                    <?php if (isset($error)): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                    <?php if (isset($success)): ?><div class="alert success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

                    <?php if (strtolower($user['role']) === 'client'): ?>
                    <form action="post_ad.php" method="post" enctype="multipart/form-data">
                        <div class="input-group">
                            <label for="title">Gig Title</label>
                            <input type="text" id="title" name="title" placeholder="e.g., I need a modern logo for my business" required>
                        </div>
                        <div class="input-group">
                            <label for="description">Detailed Description</label>
                            <textarea id="description" name="description" rows="6" placeholder="Describe the work to be done, including deliverables, requirements, etc." required></textarea>
                        </div>
                        <div class="form-row">
                            <div class="input-group">
                                <label for="category">Category</label>
                                <select id="category" name="category" required>
                                    <option value="" disabled selected>Select a category</option>
                                    <option value="Graphic & Design">Graphic & Design</option>
                                    <option value="Digital Marketing">Digital Marketing</option>
                                    <option value="Writing & Translation">Writing & Translation</option>
                                    <option value="Web Development">Web Development</option>
                                    <option value="Video & Animation">Video & Animation</option>
                                    <option value="Local Services">Local Services (Plumbing, etc.)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="input-group">
                                <label for="budget">Budget (₦)</label>
                                <input type="number" id="budget" name="budget" placeholder="e.g., 50000" step="100" min="1000" required>
                            </div>
                            <div class="input-group">
                                <label for="deadline">Deadline</label>
                                <input type="date" id="deadline" name="deadline" required>
                            </div>
                        </div>
                        <div class="form-row">
                             <div class="input-group">
                                <label for="attachment">Attachment (Optional)</label>
                                <input type="file" name="attachment" id="attachment" class="file-input">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="cta-btn">Post Gig</button>
                        </div>
                    </form>
                    <?php else: ?>
                        <div class="alert error">Only clients can post new gigs.</div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <script>
        // (Your theme toggle script from dashboard.php)
    </script>
</body>
</html>