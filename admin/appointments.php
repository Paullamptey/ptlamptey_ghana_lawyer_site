<?php
// admin/appointments.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require '../server/config.php';
require '../server/admin_db.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['id']]);

    // Send email notification to client
    require '../server/utils/Mailer.php';
    $config = require '../server/config.php';
    $mailer = new Mailer($config);
    $subject = "Appointment Status Update - Akoto Chambers";
    $html = "<h2>Appointment Status Update</h2>
    <p>Dear " . htmlspecialchars($_POST['name']) . ",</p>
    <p>Your appointment status has been updated to: <strong>" . htmlspecialchars($_POST['status']) . "</strong></p>
    <p>Date: " . htmlspecialchars($_POST['date']) . "</p>
    <p>Time: " . htmlspecialchars($_POST['time']) . "</p>
    <p>If you have any questions, please contact us.</p>";
    $mailer->send($_POST['email'], $_POST['name'], $subject, $html);

    header('Location: appointments.php');
    exit;
}

// Get all appointments
$appointments = $pdo->query("SELECT * FROM appointments ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointment Management - Akoto Chambers</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .nav-admin { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .nav-admin a { color: #d9d9d9; text-decoration: none; padding: 0.5rem 1rem; border-radius: 10px; background: #1a1a1a; }
        .nav-admin a:hover { background: #2a2a2a; }
        .appointment-list { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 1.5rem; margin: 2rem 0; }
        .appointment-item { padding: 1rem 0; border-bottom: 1px solid var(--border); }
        .appointment-item:last-child { border-bottom: none; }
        .status { padding: 0.2rem 0.5rem; border-radius: 5px; font-size: 0.8rem; }
        .status.pending { background: var(--gold); color: #111; }
        .status.confirmed { background: var(--green); color: #fff; }
        .status.cancelled { background: var(--red); color: #fff; }
        .status-select { margin-left: 1rem; }
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
        <h1>Appointment Management</h1>

        <div class="appointment-list">
            <h3>All Appointments</h3>
            <?php foreach ($appointments as $appt): ?>
                <div class="appointment-item">
                    <strong><?php echo htmlspecialchars($appt['name']); ?></strong> - <?php echo htmlspecialchars($appt['email']); ?> - <?php echo htmlspecialchars($appt['phone']); ?><br>
                    Type: <?php echo htmlspecialchars($appt['consult_type']); ?> | Date: <?php echo $appt['date']; ?> | Time: <?php echo $appt['time']; ?><br>
                    Status: <span class="status <?php echo $appt['status']; ?>"><?php echo ucfirst($appt['status']); ?></span>
                    <form method="post" style="display:inline;" class="status-select">
                        <input type="hidden" name="id" value="<?php echo $appt['id']; ?>">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($appt['email']); ?>">
                        <input type="hidden" name="name" value="<?php echo htmlspecialchars($appt['name']); ?>">
                        <input type="hidden" name="date" value="<?php echo $appt['date']; ?>">
                        <input type="hidden" name="time" value="<?php echo $appt['time']; ?>">
                        <select name="status" onchange="this.form.submit()">
                            <option value="pending" <?php if ($appt['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                            <option value="confirmed" <?php if ($appt['status'] == 'confirmed') echo 'selected'; ?>>Confirmed</option>
                            <option value="cancelled" <?php if ($appt['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                        <input type="hidden" name="update_status" value="1">
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>