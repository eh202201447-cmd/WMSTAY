<?php
session_start();
require "../includes/guard.php";
requireRole(['admin', 'staff']);
require "../includes/db_connect.php";

if (isset($_POST['send_test'])) {
    require "../includes/mailer.php";
    $email = trim($_POST['test_email']);
    if (sendTestEmail($email)) {
        echo "<script>alert('Test email sent to $email');</script>";
    } else {
        echo "<script>alert('Failed to send test email');</script>";
    }
}

// COUNTS
$total_students   = $conn->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'];
$total_rooms      = $conn->query("SELECT COUNT(*) AS c FROM rooms")->fetch_assoc()['c'];
$pending_bookings = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status='pending'")->fetch_assoc()['c'];
$pending_payments = $conn->query("SELECT COUNT(*) AS c FROM payments WHERE status='pending'")->fetch_assoc()['c'];

// RECENT BOOKINGS
$recent_bookings = $conn->query("
    SELECT b.*, s.full_name, r.room_number
    FROM bookings b
    LEFT JOIN students s ON b.student_id = s.id
    LEFT JOIN rooms r ON b.room_id = r.id
    ORDER BY b.created_at DESC
    LIMIT 10
");

// RECENT PAYMENTS
$recent_payments = $conn->query("
    SELECT p.*, s.full_name
    FROM payments p
    LEFT JOIN students s ON p.student_id = s.id
    ORDER BY p.created_at DESC
    LIMIT 10
");

// LOGIN SUCCESS BANNER
$showSuccess = isset($_GET['login']) && $_GET['login'] === 'success';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - WMSTAY</title>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="../assets/script.js" defer></script>
</head>
<body>

<div class="app">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2 class="brand">WMSTAY Admin</h2>
        <a class="active" href="/wmstay/admin/dashboard.php">Dashboard</a>
        <a href="/wmstay/admin/bookings.php">Bookings</a>
        <a href="/wmstay/admin/payments.php">Payments</a>
        <?php if ($_SESSION['role'] == 'admin'): ?>
        <a href="/wmstay/admin/rooms.php">Rooms</a>
        <a href="/wmstay/admin/reports.php">Reports</a>
        <a href="/wmstay/admin/announcements.php">Announcements</a>
        <?php endif; ?>
        <a href="/wmstay/logout.php">Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main">

        <div class="page-header">
            <h1 class="page-title">Admin Dashboard</h1>
            <button id="menuToggle" class="btn small">☰</button>
        </div>

        <?php if ($showSuccess): ?>
            <div class="alert-success">Login successful</div>
        <?php endif; ?>

        <!-- STAT CARDS -->
        <div class="stats-row">
            <div class="stat-card stat-students">
                <div class="stat-label">Total Students</div>
                <div class="stat-value"><?= $total_students ?></div>
            </div>

            <div class="stat-card stat-rooms">
                <div class="stat-label">Total Rooms</div>
                <div class="stat-value"><?= $total_rooms ?></div>
            </div>

            <div class="stat-card stat-bookings">
                <div class="stat-label">Pending Bookings</div>
                <div class="stat-value"><?= $pending_bookings ?></div>
            </div>

            <div class="stat-card stat-payments">
                <div class="stat-label">Pending Payments</div>
                <div class="stat-value"><?= $pending_payments ?></div>
            </div>
        </div>

        <!-- RECENT BOOKINGS -->
        <div class="card">
            <h2>Recent Bookings</h2>

            <!-- ✅ CENTERED TABLE -->
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Room</th>
                            <th>Status</th>
                            <th>Requested At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($b = $recent_bookings->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($b['full_name']) ?></td>
                            <td><?= htmlspecialchars($b['room_number']) ?></td>
                            <td><?= ucfirst($b['status']) ?></td>
                            <td><?= $b['created_at'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RECENT PAYMENTS -->
        <div class="card">
            <h2>Recent Payments</h2>
            
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($p = $recent_payments->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['full_name']) ?></td>
                            <td><?= htmlspecialchars($p['amount']) ?></td>
                            <td><?= htmlspecialchars($p['payment_type']) ?></td>
                            <td><?= ucfirst($p['status']) ?></td>
                            <td><?= $p['created_at'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TEST EMAIL -->
        <?php if ($_SESSION['role'] == 'admin'): ?>
        <div class="card">
            <h2>Send Test Email</h2>
            <form method="post">
                <div class="form-group">
                    <label>Email Address:</label>
                    <input type="email" name="test_email" required>
                </div>
                <button type="submit" name="send_test" class="btn">Send Test Email</button>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>