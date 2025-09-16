<?php
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'];
            
            // Check if the authenticated user is an admin
            if ($user['is_admin'] == 1) {
                // If admin, redirect to the admin dashboard
                header("Location: admin/admin_dashboard.php");
                exit;
            } else {
                // If not an admin, redirect to the regular user dashboard
                header("Location: dashboard.php");
                exit;
            }
        } else {
            $error = "Invalid email or password.";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - Chavings Tasker</title>
    <link rel="stylesheet" href="login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="signup-container">
        <div class="form-panel">
            <div class="form-header">
                <a href="index.php" class="logo">Chavings Tasker</a>
                <p class="login-prompt">Don't have an account? <a href="signup.php">Sign Up</a></p>
            </div>
            <div class="form-content">
                <h1>Welcome Back!</h1>
                <p class="subtitle">Log in to your account to continue.</p>
                
                <?php if (isset($error)): ?>
                    <p class="error-message" style="color: #D32F2F; background-color: rgba(211, 47, 47, 0.1); padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form action="login.php" method="post">
                    <div class="input-group">
                        <label for="email">Work Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="cta-btn">Log In</button>
                </form>
            </div>
        </div>
        <div class="info-panel">
            <div class="info-content">
                <h2>Unlock Nigeria's Top Talent.</h2>
                <p>From local artisans to digital experts, find identity-verified professionals you can trust for any task.</p>
                <div class="feature-card">
                    <img src="default.jpg" alt="Professional Lancer">
                    <div class="card-caption">
                        <strong>Adebayo Cole</strong>
                        <span class="lancer-role">Digital Marketer & SEO Expert</span>
                        <span class="verified-badge">✔ Verified Lancer</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>