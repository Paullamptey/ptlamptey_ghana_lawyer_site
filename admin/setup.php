<?php
// admin/setup.php - Run once to create admin user
require __DIR__ . '/../server/config.php';
require __DIR__ . '/../server/admin_db.php';

$username = 'admin';
$password = password_hash('password123', PASSWORD_DEFAULT); // Change this
$email = 'admin@akotochambers.com';

$stmt = $pdo->prepare("INSERT INTO admin (username, password, email, created_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$username, $password, $email]);

echo "Admin user created. Username: admin, Password: password123 (change immediately)";
?>