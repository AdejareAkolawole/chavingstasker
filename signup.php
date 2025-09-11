<?php
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Server-side validation
    if (strlen($_POST['password']) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif (!preg_match('/^\+234[0-9]{10}$/', $phone)) {
        $error = "Phone must be in format +234XXXXXXXXXX.";
    } elseif (!in_array($role, ['Client', 'Lancer'])) {
        $error = "Invalid role selected.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $email, $password, $phone, $address, $role]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $first_name;
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $error = "Error: Email already exists or invalid input.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Chavings Tasker</title>
    <link rel="stylesheet" href="signup.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="logo">Chavings Tasker</div>
        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="login.php">Login</a>
        </nav>
    </header>
    <section class="signup-section">
        <div class="container">
            <div class="form-card">
                <h2>Create Your Account</h2>
                <p>Join Chavings Tasker to offer your skills or hire trusted professionals.</p>
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <form action="signup.php" method="post" onsubmit="return validateForm()">
                    <div class="input-group">
                        <span class="input-icon">👤</span>
                        <input type="text" name="first_name" placeholder="First Name" required>
                    </div>
                    <div class="input-group">
                        <span class="input-icon">👤</span>
                        <input type="text" name="last_name" placeholder="Last Name" required>
                    </div>
                    <div class="input-group">
                        <span class="input-icon">✉️</span>
                        <input type="email" name="email" placeholder="Email Address" required>
                    </div>
                    <div class="input-group">
                        <span class="input-icon">📞</span>
                        <input type="tel" name="phone" placeholder="Phone (+234XXXXXXXXXX)" pattern="\+234[0-9]{10}" required>
                    </div>
                    <div class="input-group">
                        <span class="input-icon">🏠</span>
                        <input type="text" name="address" placeholder="Address" required>
                    </div>
                    <div class="input-group">
                        <span class="input-icon">💼</span>
                        <select name="role" required>
                            <option value="" disabled selected>Select Role</option>
                            <option value="Client">Client (Hire Professionals)</option>
                            <option value="Lancer">Lancer (Offer Services)</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="password" id="password" placeholder="Password (min 8 characters)" required>
                    </div>
                    <button type="submit" class="cta-btn">Sign Up</button>
                </form>
                <p class="form-footer">Already have an account? <a href="login.php">Log In</a></p>
            </div>
        </div>
    </section>
    <script>
        function validateForm() {
            const password = document.getElementById('password').value;
            const phone = document.querySelector('input[name="phone"]').value;
            if (password.length < 8) {
                alert('Password must be at least 8 characters.');
                return false;
            }
            if (!/^\+234[0-9]{10}$/.test(phone)) {
                alert('Phone must be in format +234XXXXXXXXXX.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>