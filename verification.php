<?php
// verification.php
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';
$user_data = [];
$pending_request = false;

// Fetch user data and check for a pending request
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

$check_stmt = $pdo->prepare("SELECT COUNT(*) FROM verification_requests WHERE user_id = ? AND status = 'Pending'");
$check_stmt->execute([$user_id]);
if ($check_stmt->fetchColumn() > 0) {
    $pending_request = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) {
    $document_type = filter_input(INPUT_POST, 'document_type', FILTER_SANITIZE_STRING);
    $upload_dir = 'uploads/verifications/';
    
    // Check for pending requests again to prevent duplicate submissions
    if ($pending_request) {
        $error_message = "You have a pending verification request. Please wait for it to be reviewed.";
    } else {
        // Handle file upload
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['document_file']['tmp_name'], $file_path)) {
            $stmt = $pdo->prepare("INSERT INTO verification_requests (user_id, document_type, document_path) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $document_type, $file_path]);
            $success_message = "Document submitted for verification successfully! We will review it shortly.";
            $pending_request = true; // Update state after successful submission
        } else {
            $error_message = "File upload failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Verified - Chavings Tasker</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .verification-status-panel {
            padding: 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .verification-status-panel.pending {
            background-color: rgba(255, 193, 7, 0.1);
            color: #FFC107;
            border: 1px solid rgba(255, 193, 7, 0.2);
        }
        .verification-status-panel.verified {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--brand-green);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        .verification-status-panel.unverified {
            background-color: rgba(239, 68, 68, 0.1);
            color: #EF4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        /* New Form Styles */
        .content-panel form {
            display: flex;
            flex-direction: column;
        }
        .input-group {
            margin-bottom: 1.5rem;
        }
        .input-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .input-group select,
        .input-group input[type="text"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--bg-main);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
        }
        .input-group select:focus,
        .input-group input[type="text"]:focus {
            outline: none;
            border-color: var(--brand-purple);
            box-shadow: 0 0 0 3px rgba(109, 40, 217, 0.2);
        }
        .input-group select {
            appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2214%22%20height%3D%2214%22%20viewBox%3D%220%200%2014%2014%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M2.5%204.5L7%209L11.5%204.5%22%20stroke%3D%22%234A5568%22%20stroke-width%3D%221.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 1rem center;
        }
        
        .custom-file-upload {
            display: inline-block;
            cursor: pointer;
            background-color: var(--brand-purple);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
        }
        .custom-file-upload:hover {
            background-color: var(--brand-purple-dark);
        }
        .file-upload-info {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }
        .cta-btn.large {
            padding: 0.875rem;
            font-size: 1rem;
            width: 100%;
        }
        
        input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            pointer-events: none;
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
                <a href="business_tools.php" class="nav-item premium"><i class="fas fa-tools"></i><span>Business Tools</span></a>
                <a href="referrals.php" class="nav-item"><i class="fas fa-users"></i><span>Refer & Earn</span></a>
                <a href="verification.php" class="nav-item active"><i class="fas fa-user-shield"></i><span>Verification</span></a>
            </nav>
            <div class="sidebar-footer">
                <a href="profile.php" class="nav-item"><i class="fas fa-user-circle"></i><span>Profile</span></a>
                <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-greeting"><h1>Get Verified</h1><p>Upload a document to verify your identity and get a trusted badge.</p></div>
                <div class="header-actions"><button class="theme-toggle" id="theme-toggle-btn"></button></div>
            </header>
            
            <?php if (!empty($success_message)): ?><div class="alert success"><?php echo htmlspecialchars($success_message); ?></div><?php endif; ?>
            <?php if (!empty($error_message)): ?><div class="alert error"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>
            
            <?php if ($user_data['verified']): ?>
                <div class="verification-status-panel verified">
                    <i class="fas fa-check-circle"></i> Your account is fully verified. You have a **blue badge** on your profile.
                </div>
            <?php elseif ($pending_request): ?>
                <div class="verification-status-panel pending">
                    <i class="fas fa-hourglass-half"></i> Your verification request has been submitted and is pending review. We'll notify you when it's complete.
                </div>
            <?php else: ?>
                <div class="verification-status-panel unverified">
                    <i class="fas fa-exclamation-triangle"></i> Your account is not yet verified. Upload a document below to get started.
                </div>
                <div class="content-panel">
                     <div class="panel-header"><h2>Submit Your Document</h2></div>
                     <form action="verification.php" method="post" enctype="multipart/form-data">
                         <div class="input-group">
                            <label for="document_type">Document Type</label>
                            <select id="document_type" name="document_type" required>
                                <option value="National ID">National ID Card</option>
                                <option value="Voter's Card">Voter's Card</option>
                                <option value="Passport">International Passport</option>
                                <option value="Driver's License">Driver's License</option>
                            </select>
                         </div>
                         <div class="input-group">
                            <label for="document_file" class="custom-file-upload">Choose File</label>
                            <input type="file" id="document_file" name="document_file" accept=".jpg,.jpeg,.png,.pdf" onchange="updateFileName(this)" required>
                            <span id="file-name" class="file-upload-info">No file chosen</span>
                         </div>
                         <button type="submit" class="cta-btn large">Submit for Verification</button>
                     </form>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <script>
        function updateFileName(input) {
            const fileNameSpan = document.getElementById('file-name');
            if (input.files.length > 0) {
                fileNameSpan.textContent = input.files[0].name;
            } else {
                fileNameSpan.textContent = 'No file chosen';
            }
        }
        
        // --- Theme Toggle ---
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