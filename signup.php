<?php
// signup.php
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();

$error = '';
$referrer_id = null;

// NEW: Check for a referral ID in the URL
if (isset($_GET['ref']) && is_numeric($_GET['ref'])) {
    $referrer_id = (int)$_GET['ref'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $referrer_id_post = filter_input(INPUT_POST, 'referrer_id', FILTER_SANITIZE_NUMBER_INT);
    if ($referrer_id_post) {
        $referrer_id = (int)$referrer_id_post;
    }

    // Hash the password securely
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = "This email is already registered. Please log in.";
        } else {
            // Insert new user into the database
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $email, $password_hash, $phone, $address, $role]);
            
            // Get the ID of the newly created user
            $new_user_id = $pdo->lastInsertId();

            // NEW: Record the referral if a referrer_id exists
            if ($referrer_id) {
                $referral_reward = 1000.00; // Define your reward amount
                $referral_stmt = $pdo->prepare("INSERT INTO referrals (referrer_id, referred_user_id, reward_amount) VALUES (?, ?, ?)");
                $referral_stmt->execute([$referrer_id, $new_user_id, $referral_reward]);
            }
            
            // Log in the new user and redirect to dashboard
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['user_name'] = $first_name;
            header("Location: dashboard.php");
            exit;
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
    <title>Get Started - Chavings Tasker</title>
    <link rel="stylesheet" href="signup.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="signup-container">
        <div class="form-panel">
            <div class="form-header">
                <a href="index.php" class="logo">Chavings Tasker</a>
                <p class="login-prompt">Already have an account? <a href="login.php">Log In</a></p>
            </div>
            <div class="form-content">
                <h1>Get started with Chavings Tasker</h1>
                <p class="subtitle">Create an account to hire or offer your professional services across Nigeria.</p>
                
                <?php if (isset($error)): ?>
                    <p class="error-message" style="color: #D32F2F; background-color: rgba(211, 47, 47, 0.1); padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form action="signup.php" method="post">
                    <div class="form-row">
                        <div class="input-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        <div class="input-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="email">Work Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="input-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="+2348012345678" pattern="\+234[0-9]{10}" required>
                    </div>
                    <div class="input-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" required>
                    </div>
                     <div class="input-group">
                        <label for="role">I am a...</label>
                        <select id="role" name="role" required>
                            <option value="" disabled selected>Select your role</option>
                            <option value="Client">Client (I want to hire)</option>
                            <option value="Lancer">Lancer (I want to work)</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="8+ characters" required>
                    </div>

                    <?php if ($referrer_id): ?>
                        <input type="hidden" name="referrer_id" value="<?php echo htmlspecialchars($referrer_id); ?>">
                    <?php endif; ?>

                    <button type="submit" class="cta-btn">Create Free Account</button>
                    <p class="terms">By creating an account, you agree to Chavings Tasker's <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>.</p>
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