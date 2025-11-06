<?php
// admin/clients.php
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
        $stmt = $pdo->prepare("INSERT INTO clients (name, email, phone, case_type, notes, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$_POST['name'], $_POST['email'], $_POST['phone'], $_POST['case_type'], $_POST['notes']]);
    } elseif (isset($_POST['update'])) {
        $stmt = $pdo->prepare("UPDATE clients SET name=?, email=?, phone=?, case_type=?, notes=? WHERE id=?");
        $stmt->execute([$_POST['name'], $_POST['email'], $_POST['phone'], $_POST['case_type'], $_POST['notes'], $_POST['id']]);
    } elseif (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id=?");
        $stmt->execute([$_POST['id']]);
    }
    header('Location: clients.php');
    exit;
}

// Get all clients
$clients = $pdo->query("SELECT * FROM clients ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Management - Akoto Chambers</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .nav-admin { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .nav-admin a { color: #d9d9d9; text-decoration: none; padding: 0.5rem 1rem; border-radius: 10px; background: #1a1a1a; }
        .nav-admin a:hover { background: #2a2a2a; }
        .client-list { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 1.5rem; margin: 2rem 0; }
        .client-item { padding: 1rem 0; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .client-item:last-child { border-bottom: none; }
        .client-info { flex: 1; }
        .client-actions { display: flex; gap: 0.5rem; }
        .btn-small { padding: 0.3rem 0.6rem; font-size: 0.9rem; }
        .form-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; }
        .form-modal.show { display: flex; align-items: center; justify-content: center; }
        .form-content { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 2rem; width: 90%; max-width: 500px; }
        .form-content h3 { margin-top: 0; }
        .form-content input, .form-content select, .form-content textarea { width: 100%; margin-bottom: 1rem; padding: 0.7rem; border: 1px solid #2b2b2b; background: #121212; color: #fff; border-radius: 10px; }
        .form-content button { background: var(--gold); color: #111; border: none; padding: 0.8rem; border-radius: 12px; cursor: pointer; }
        .form-content .cancel { background: var(--red); color: #fff; margin-left: 1rem; }
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
        <h1>Client Management</h1>
        <button onclick="showForm()" class="btn btn-gold">Add New Client</button>

        <div class="client-list">
            <h3>All Clients</h3>
            <?php foreach ($clients as $client): ?>
                <div class="client-item">
                    <div class="client-info">
                        <strong><?php echo htmlspecialchars($client['name']); ?></strong><br>
                        <?php echo htmlspecialchars($client['email']); ?> | <?php echo htmlspecialchars($client['phone']); ?><br>
                        Case: <?php echo htmlspecialchars($client['case_type']); ?>
                    </div>
                    <div class="client-actions">
                        <button onclick="editClient(<?php echo $client['id']; ?>, '<?php echo addslashes($client['name']); ?>', '<?php echo addslashes($client['email']); ?>', '<?php echo addslashes($client['phone']); ?>', '<?php echo addslashes($client['case_type']); ?>', '<?php echo addslashes($client['notes']); ?>')" class="btn btn-outline btn-small">Edit</button>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                            <button type="submit" name="delete" onclick="return confirm('Delete this client?')" class="btn btn-red btn-small">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="clientForm" class="form-modal">
        <div class="form-content">
            <h3 id="formTitle">Add Client</h3>
            <form method="post">
                <input type="hidden" name="id" id="clientId">
                <input type="text" name="name" id="clientName" placeholder="Full Name" required>
                <input type="email" name="email" id="clientEmail" placeholder="Email" required>
                <input type="tel" name="phone" id="clientPhone" placeholder="Phone" required>
                <select name="case_type" id="clientCaseType" required>
                    <option value="General">General</option>
                    <option value="Family Law">Family Law</option>
                    <option value="Property & Land">Property & Land</option>
                    <option value="Immigration">Immigration</option>
                    <option value="Corporate & Compliance">Corporate & Compliance</option>
                    <option value="Criminal Defence">Criminal Defence</option>
                </select>
                <textarea name="notes" id="clientNotes" placeholder="Notes" rows="3"></textarea>
                <button type="submit" name="create" id="submitBtn">Create</button>
                <button type="button" onclick="hideForm()" class="cancel">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function showForm() {
            document.getElementById('formTitle').textContent = 'Add Client';
            document.getElementById('submitBtn').name = 'create';
            document.getElementById('clientId').value = '';
            document.getElementById('clientName').value = '';
            document.getElementById('clientEmail').value = '';
            document.getElementById('clientPhone').value = '';
            document.getElementById('clientCaseType').value = 'General';
            document.getElementById('clientNotes').value = '';
            document.getElementById('clientForm').classList.add('show');
        }

        function editClient(id, name, email, phone, caseType, notes) {
            document.getElementById('formTitle').textContent = 'Edit Client';
            document.getElementById('submitBtn').name = 'update';
            document.getElementById('clientId').value = id;
            document.getElementById('clientName').value = name;
            document.getElementById('clientEmail').value = email;
            document.getElementById('clientPhone').value = phone;
            document.getElementById('clientCaseType').value = caseType;
            document.getElementById('clientNotes').value = notes;
            document.getElementById('clientForm').classList.add('show');
        }

        function hideForm() {
            document.getElementById('clientForm').classList.remove('show');
        }
    </script>
</body>
</html>