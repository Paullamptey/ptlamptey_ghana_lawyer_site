<?php
// admin/admins.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require '../server/config.php';
require '../server/admin_db.php';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
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
                $success = 'Admin account created successfully.';
            }
        }
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($email)) {
            $error = 'Username and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } else {
            // Check if username or email already exists for other admins
            $stmt = $pdo->prepare("SELECT id FROM admin WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $id]);
            if ($stmt->fetch()) {
                $error = 'Username or email already exists.';
            } else {
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE admin SET username=?, email=?, password=? WHERE id=?");
                    $stmt->execute([$username, $email, $hashed_password, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE admin SET username=?, email=? WHERE id=?");
                    $stmt->execute([$username, $email, $id]);
                }
                $success = 'Admin account updated successfully.';
            }
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        // Prevent deleting the current admin
        if ($id == $_SESSION['admin_id']) {
            $error = 'You cannot delete your own account.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM admin WHERE id=?");
            $stmt->execute([$id]);
            $success = 'Admin account deleted successfully.';
        }
    }
    header('Location: admins.php');
    exit;
}

// Get all admins
$admins = $pdo->query("SELECT id, username, email, created_at FROM admin ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Management - Akoto Chambers</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .nav-admin { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .nav-admin a { color: #d9d9d9; text-decoration: none; padding: 0.5rem 1rem; border-radius: 10px; background: #1a1a1a; }
        .nav-admin a:hover { background: #2a2a2a; }
        .admin-list { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 1.5rem; margin: 2rem 0; }
        .admin-item { padding: 1rem 0; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .admin-item:last-child { border-bottom: none; }
        .admin-info { flex: 1; }
        .admin-actions { display: flex; gap: 0.5rem; }
        .btn-small { padding: 0.3rem 0.6rem; font-size: 0.9rem; }
        .form-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; }
        .form-modal.show { display: flex; align-items: center; justify-content: center; }
        .form-content { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 2rem; width: 90%; max-width: 500px; }
        .form-content h3 { margin-top: 0; }
        .form-content input { width: 100%; margin-bottom: 1rem; padding: 0.7rem; border: 1px solid #2b2b2b; background: #121212; color: #fff; border-radius: 10px; }
        .form-content button { background: var(--gold); color: #111; border: none; padding: 0.8rem; border-radius: 12px; cursor: pointer; }
        .form-content .cancel { background: var(--red); color: #fff; margin-left: 1rem; }
        .error { color: var(--red); margin-bottom: 1rem; }
        .success { color: var(--green); margin-bottom: 1rem; }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container nav-wrap">
            <a href="#" class="brand">
                <span class="brand-mark">⚖</span>
                <span class="brand-text">Akoto Chambers - Admin</span>
            </a>
            <nav class="nav">
                <a href="dashboard.php">Dashboard</a>
                <a href="clients.php">Clients</a>
                <a href="appointments.php">Appointments</a>
                <a href="admins.php">Admins</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>Admin Management</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <button onclick="showForm()" class="btn btn-gold">Add New Admin</button>

        <div class="admin-list">
            <h3>All Admins</h3>
            <?php foreach ($admins as $admin): ?>
                <div class="admin-item">
                    <div class="admin-info">
                        <strong><?php echo htmlspecialchars($admin['username']); ?></strong><br>
                        <?php echo htmlspecialchars($admin['email']); ?><br>
                        Created: <?php echo $admin['created_at']; ?>
                    </div>
                    <div class="admin-actions">
                        <button onclick="editAdmin(<?php echo $admin['id']; ?>, '<?php echo addslashes($admin['username']); ?>', '<?php echo addslashes($admin['email']); ?>')" class="btn btn-outline btn-small">Edit</button>
                        <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $admin['id']; ?>">
                                <button type="submit" name="delete" onclick="return confirm('Delete this admin account?')" class="btn btn-red btn-small">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="adminForm" class="form-modal">
        <div class="form-content">
            <h3 id="formTitle">Add Admin</h3>
            <form method="post">
                <input type="hidden" name="id" id="adminId">
                <input type="text" name="username" id="adminUsername" placeholder="Username" required>
                <input type="email" name="email" id="adminEmail" placeholder="Email" required>
                <input type="password" name="password" id="adminPassword" placeholder="Password" required>
                <input type="password" name="confirm_password" id="adminConfirmPassword" placeholder="Confirm Password" required>
                <button type="submit" name="create" id="submitBtn">Create</button>
                <button type="button" onclick="hideForm()" class="cancel">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function showForm() {
            document.getElementById('formTitle').textContent = 'Add Admin';
            document.getElementById('submitBtn').name = 'create';
            document.getElementById('adminId').value = '';
            document.getElementById('adminUsername').value = '';
            document.getElementById('adminEmail').value = '';
            document.getElementById('adminPassword').value = '';
            document.getElementById('adminConfirmPassword').value = '';
            document.getElementById('adminForm').classList.add('show');
        }

        function editAdmin(id, username, email) {
            document.getElementById('formTitle').textContent = 'Edit Admin';
            document.getElementById('submitBtn').name = 'update';
            document.getElementById('adminId').value = id;
            document.getElementById('adminUsername').value = username;
            document.getElementById('adminEmail').value = email;
            document.getElementById('adminPassword').value = '';
            document.getElementById('adminConfirmPassword').value = '';
            document.getElementById('adminForm').classList.add('show');
        }

        function hideForm() {
            document.getElementById('adminForm').classList.remove('show');
        }
    </script>
</body>
</html>