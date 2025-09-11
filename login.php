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
            header("Location: dashboard.php");
            exit;
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="logo">Chavings Tasker</div>
        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="signup.php">Create Account</a>
        </nav>
    </header>
    <section class="login-section">
        <div class="container">
            <div class="form-card">
                <h2>Log In to Your Account</h2>
                <p>Access your tasks and profile.</p>
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <form action="login.php" method="post">
                    <div class="input-group">
                        <span class="input-icon">✉️</span>
                        <input type="email" name="email" placeholder="Email Address" required>
                    </div>
                    <div class="input-group">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" class="cta-btn">Log In</button>
                </form>
                <p class="form-footer">Don't have an account? <a href="signup.php">Create Account</a></p>
            </div>
        </div>
    </section>
</body>
</html>