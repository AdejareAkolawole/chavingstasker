<?php
// profile.php
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- HANDLE ROLE SWITCHING (This logic is fine, no changes needed here) ---
if (isset($_GET['action']) && $_GET['action'] === 'switch_role') {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_role = $stmt->fetchColumn();

    $new_role = (strtolower($current_role) === 'client') ? 'Lancer' : 'Client';

    $update_stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $update_stmt->execute([$new_role, $_SESSION['user_id']]);

    header("Location: profile.php");
    exit;
}

// --- HANDLE PROFILE UPDATE (Placeholder) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // (Your profile update logic here)
}

// --- HANDLE PROFILE PICTURE UPLOAD (Placeholder) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    // (Your picture upload logic here)
}

// Fetch user data after any potential updates
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$reviews = []; // Placeholder for reviews
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Chavings Tasker</title>
    <link rel="stylesheet" href="profile.css">
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
                <a href="featured_profile.php" class="nav-item premium"><i class="fas fa-star"></i><span>Featured Profile</span></a>
                <a href="business_tools.php" class="nav-item premium"><i class="fas fa-tools"></i><span>Business Tools</span></a>
                <a href="referrals.php" class="nav-item"><i class="fas fa-users"></i><span>Refer & Earn</span></a>
                <a href="verification.php" class="nav-item"><i class="fas fa-user-shield"></i><span>Verification</span></a>
            </nav>
            <div class="sidebar-footer">
                 <a href="profile.php" class="nav-item active"><i class="fas fa-user-circle"></i><span>Profile</span></a>
                <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-greeting"><h1>My Profile</h1><p>Manage your public profile and account details.</p></div>
                <div class="header-actions"><button class="theme-toggle" id="theme-toggle-btn"></button></div>
            </header>

            <div class="profile-header-card">
                <div class="profile-info">
                    <div class="profile-picture-wrapper">
                        <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'default-avatar.png'); ?>" alt="Profile Picture" class="profile-picture">
                        <form action="profile.php" method="post" enctype="multipart/form-data" id="pfp-form">
                            <input type="file" name="profile_picture" id="profile-picture-input" accept="image/*" onchange="this.form.submit()" style="display: none;">
                            <label for="profile-picture-input" class="edit-picture-btn" title="Change Profile Picture"><i class="fas fa-camera"></i></label>
                        </form>
                    </div>
                    <div class="profile-details">
                        <h2>
                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                            <?php if ($user['is_admin']): ?>
                                <span class="admin-badge" title="Admin (Green Tick)"><i class="fas fa-check-circle" style="color:#10B981;"></i></span>
                            <?php endif; ?>
                            <?php if ($user['verified']): ?>
                                <span class="verified-badge" title="Verified User (Blue Tick)"><i class="fas fa-check"></i></span>
                            <?php endif; ?>
                        </h2>
                        <p class="profile-role"><?php echo htmlspecialchars($user['role']); ?></p>
                        <p class="profile-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['address'] ?? 'Location not set'); ?></p>
                    </div>
                </div>
                <div class="profile-actions">
                    <a href="profile.php?action=switch_role" class="cta-btn secondary">
                        <i class="fas fa-exchange-alt"></i> Switch to <?php echo (strtolower($user['role']) === 'client') ? 'Lancer' : 'Client'; ?>
                    </a>
                    <button class="cta-btn" onclick="toggleModal()">Edit Profile</button>
                </div>
            </div>

            <div class="content-panel">
                 <div class="tabs-container">
                    <button class="tab-link active" data-tab="about">About</button>
                    <button class="tab-link" data-tab="reviews">Reviews</button>
                </div>
                <div class="tab-content active" id="about">
                    <h3>About Me</h3>
                    <p><?php echo nl2br(htmlspecialchars($user['bio'] ?? 'No bio provided. Click "Edit Profile" to add one.')); ?></p>
                    <h3 class="section-subheader">Skills</h3>
                    <div class="skills-tags">
                        <?php $skills = !empty($user['skills']) ? explode(',', $user['skills']) : []; ?>
                        <?php if(empty($skills)): ?> <p>No skills listed.</p> <?php endif; ?>
                        <?php foreach($skills as $skill): ?>
                            <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="tab-content" id="reviews">
                     <h3>Reviews (<?php echo count($reviews); ?>)</h3>
                     <?php if(empty($reviews)): ?> <p>You have no reviews yet.</p> <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="modal-overlay" onclick="toggleModal()"></div>
    <div class="modal-card" id="edit-profile-modal">
        <h3>Edit Your Profile</h3>
        <form action="profile.php" method="post">
            <input type="hidden" name="update_profile" value="1">
            <div class="form-row">
                <div class="input-group"><label for="first_name">First Name</label><input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required></div>
                <div class="input-group"><label for="last_name">Last Name</label><input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required></div>
            </div>
            <div class="input-group"><label for="address">Address / Location</label><input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" placeholder="e.g., Lagos, Nigeria"></div>
            <div class="input-group"><label for="skills">Skills (comma separated)</label><input type="text" id="skills" name="skills" value="<?php echo htmlspecialchars($user['skills'] ?? ''); ?>" placeholder="e.g., Graphic Design, PHP, Content Writing"></div>
            <div class="input-group"><label for="bio">About Me</label><textarea id="bio" name="bio" rows="5" placeholder="Tell everyone a little bit about yourself and your skills."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea></div>
            <div class="form-actions"><button type="button" class="cta-btn secondary" onclick="toggleModal()">Cancel</button><button type="submit" class="cta-btn">Save Changes</button></div>
        </form>
    </div>

    <script>
        // JavaScript for Modal and Theme Toggle
        function toggleModal() {
            document.getElementById('modal-overlay').classList.toggle('active');
            document.getElementById('edit-profile-modal').classList.toggle('active');
        }
        const themeToggleBtn = document.getElementById('theme-toggle-btn');
        const sunIcon = `<i class="fas fa-sun"></i>`; const moonIcon = `<i class="fas fa-moon"></i>`;
        function applyTheme(theme) { document.documentElement.setAttribute('data-theme', theme); themeToggleBtn.innerHTML = theme === 'dark' ? sunIcon : moonIcon; localStorage.setItem('theme', theme); }
        themeToggleBtn.addEventListener('click', () => { const currentTheme = document.documentElement.getAttribute('data-theme'); applyTheme(currentTheme === 'light' ? 'dark' : 'light'); });
        applyTheme(localStorage.getItem('theme') || 'light');
        const tabLinks = document.querySelectorAll('.tab-link');
        const tabContents = document.querySelectorAll('.tab-content');
        tabLinks.forEach(link => { link.addEventListener('click', () => { tabLinks.forEach(l => l.classList.remove('active')); tabContents.forEach(c => c.classList.remove('active')); link.classList.add('active'); document.getElementById(link.dataset.tab).classList.add('active'); }); });
    </script>
</body>
</html>