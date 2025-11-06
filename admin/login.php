<?php
// admin/login.php
session_start();
require '../server/config.php';
require '../server/admin_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT id, password FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin) {
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'Username not found.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Akoto Chambers</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .login-form { max-width: 400px; margin: 5rem auto; padding: 2rem; background: var(--card); border: 1px solid var(--border); border-radius: 16px; }
        .login-form h2 { text-align: center; margin-bottom: 1rem; }
        .login-form input { width: 100%; margin-bottom: 1rem; padding: 0.7rem; border: 1px solid #2b2b2b; background: #121212; color: #fff; border-radius: 10px; }
        .login-form button { width: 100%; background: var(--gold); color: #111; border: none; padding: 0.8rem; border-radius: 12px; cursor: pointer; }
        .login-form .create-account { width: 100%; background: transparent; color: var(--gold); border: 1px solid var(--gold); margin-top: 1rem; }
        .login-form .create-account:hover { background: var(--gold); color: #111; }
        .error { color: var(--red); text-align: center; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <form class="login-form" method="post">
            <h2>Admin Login</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <a href="register.php" class="create-account">Create Account</a>
        </form>
    </div>
</body>
</html>