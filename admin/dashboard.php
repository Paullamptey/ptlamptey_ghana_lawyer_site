<?php
// admin/dashboard.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require '../server/config.php';
require '../server/admin_db.php';

// Get statistics
$stats = [];
$stats['total_clients'] = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$stats['total_appointments'] = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$stats['pending_appointments'] = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn();
$stats['total_complaints'] = $pdo->query("SELECT COUNT(*) FROM complaints")->fetchColumn();

// Recent appointments
$recent_appointments = $pdo->query("SELECT * FROM appointments ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent clients
$recent_clients = $pdo->query("SELECT * FROM clients ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Akoto Chambers</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin: 2rem 0; }
        .stat-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 1.5rem; text-align: center; }
        .stat-card h3 { margin: 0 0 0.5rem 0; font-size: 2rem; color: var(--gold); }
        .stat-card p { margin: 0; color: #d0d0d0; }
        .recent-list { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 1.5rem; margin: 2rem 0; }
        .recent-list h3 { margin-top: 0; }
        .recent-item { padding: 0.5rem 0; border-bottom: 1px solid var(--border); }
        .recent-item:last-child { border-bottom: none; }
        .nav-admin { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .nav-admin a { color: #d9d9d9; text-decoration: none; padding: 0.5rem 1rem; border-radius: 10px; background: #1a1a1a; }
        .nav-admin a:hover { background: #2a2a2a; }
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
        <h1>Admin Dashboard</h1>

        <div class="dashboard">
            <div class="stat-card">
                <h3><?php echo $stats['total_clients']; ?></h3>
                <p>Total Clients</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_appointments']; ?></h3>
                <p>Total Appointments</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['pending_appointments']; ?></h3>
                <p>Pending Appointments</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_complaints']; ?></h3>
                <p>Total Complaints</p>
            </div>
        </div>

        <div class="recent-list">
            <h3>Recent Appointments</h3>
            <?php foreach ($recent_appointments as $appt): ?>
                <div class="recent-item">
                    <strong><?php echo htmlspecialchars($appt['name']); ?></strong> - <?php echo htmlspecialchars($appt['consult_type']); ?> on <?php echo $appt['date']; ?> at <?php echo $appt['time']; ?> (<?php echo $appt['status']; ?>)
                </div>
            <?php endforeach; ?>
        </div>

        <div class="recent-list">
            <h3>Recent Clients</h3>
            <?php foreach ($recent_clients as $client): ?>
                <div class="recent-item">
                    <strong><?php echo htmlspecialchars($client['name']); ?></strong> - <?php echo htmlspecialchars($client['email']); ?> - <?php echo htmlspecialchars($client['case_type']); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>