<?php
// admin/register.php
require '../server/config.php';
require '../server/admin_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM admin WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admin (username, password, email, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$username, $hashed_password, $email]);
            $success = 'Admin account created successfully. You can now login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Registration - Akoto Chambers</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .register-form { max-width: 400px; margin: 5rem auto; padding: 2rem; background: var(--card); border: 1px solid var(--border); border-radius: 16px; }
        .register-form h2 { text-align: center; margin-bottom: 1rem; }
        .register-form input { width: 100%; margin-bottom: 1rem; padding: 0.7rem; border: 1px solid #2b2b2b; background: #121212; color: #fff; border-radius: 10px; }
        .register-form button { width: 100%; background: var(--gold); color: #111; border: none; padding: 0.8rem; border-radius: 12px; cursor: pointer; }
        .register-form .login-link { width: 100%; background: transparent; color: var(--gold); border: 1px solid var(--gold); margin-top: 1rem; text-decoration: none; display: inline-block; text-align: center; }
        .register-form .login-link:hover { background: var(--gold); color: #111; }
        .error { color: var(--red); text-align: center; margin-bottom: 1rem; }
        .success { color: var(--green); text-align: center; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <form class="register-form" method="post">
            <h2>Create Admin Account</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <input type="text" name="username" placeholder="Username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            <input type="password" name="password" placeholder="Password (min 8 characters)" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Create Account</button>
            <a href="login.php" class="login-link">Back to Login</a>
        </form>
    </div>
</body>
</html>