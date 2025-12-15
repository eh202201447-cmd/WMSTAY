<?php
session_start();
require "../includes/guard.php";
requireRole(['student']);
require "../includes/db_connect.php";

$student_id = $_SESSION['student_id'];

// Student info
$student = $conn->query("SELECT * FROM students WHERE id = $student_id")->fetch_assoc();

// Latest booking
$room = $conn->query("
    SELECT b.status, r.room_number, r.room_type
    FROM bookings b
    LEFT JOIN rooms r ON b.room_id = r.id
    WHERE b.student_id = $student_id
    ORDER BY b.id DESC
    LIMIT 1
")->fetch_assoc();

// Payments
$payments = $conn->query("
    SELECT * FROM payments
    WHERE student_id = $student_id
    ORDER BY created_at DESC
");

// login success banner
$showSuccess = isset($_GET['login']) && $_GET['login'] === 'success';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard - WMSTAY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="../assets/script.js" defer></script>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse" id="sidebar">
            <div class="position-sticky pt-3">
                <h2 class="brand">WMSTAY</h2>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link active" href="/wmstay/student/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/wmstay/student/bookings.php">My Bookings</a></li>
                    <li class="nav-item"><a class="nav-link" href="/wmstay/student/payments.php">My Payments</a></li>
                    <li class="nav-item"><a class="nav-link" href="/wmstay/student/reports.php">Report / Maintenance</a></li>
                    <li class="nav-item"><a class="nav-link" href="/wmstay/student/profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="/wmstay/logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Student Dashboard</h1>
                <button id="menuToggle" class="btn btn-outline-secondary d-md-none">☰</button>
            </div>

        <?php if ($showSuccess): ?>
            <div class="alert alert-success">Login successful</div>
        <?php endif; ?>

        <div class="row">
            <!-- My Information -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <span>👤</span> My Information
                    </div>
                    <div class="card-body">
                        <p><b>Name:</b> <?= htmlspecialchars($student['full_name']) ?></p>
                        <p><b>Email:</b> <?= htmlspecialchars($student['email']) ?></p>
                        <p><b>Department:</b> <?= htmlspecialchars($student['department']) ?></p>
                        <p><b>Program:</b> <?= htmlspecialchars($student['program']) ?></p>
                        <p><b>Contact:</b> <?= htmlspecialchars($student['contact']) ?></p>
                        <p><b>Address:</b> <?= htmlspecialchars($student['address']) ?></p>
                    </div>
                </div>
            </div>

            <!-- My Room -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <span>🛏️</span> My Room
                    </div>
                    <div class="card-body">
                <?php if ($room): ?>
                    <?php if ($room['status'] === 'approved'): ?>
                        <p><b>Room:</b> <?= htmlspecialchars($room['room_number']) ?></p>
                        <p><b>Type:</b> <?= htmlspecialchars($room['room_type']) ?></p>
                        <p><b>Status:</b> Approved</p>
                    <?php elseif ($room['status'] === 'pending'): ?>
                        <p>Your room booking is <b>pending</b> approval.</p>
                    <?php elseif ($room['status'] === 'rejected'): ?>
                        <p>Your last booking was <b>rejected</b>.</p>
                        <a class="btn primary" href="/wmstay/student/bookings.php">Book Again</a>
                    <?php endif; ?>
                <?php else: ?>
                    <p>You don't have an approved room booking yet.</p>
                    <a class="btn primary" href="/wmstay/student/bookings.php">Book Now</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="card">
            <h2>Recent Payments</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = $payments->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['amount']) ?></td>
                        <td><?= htmlspecialchars($p['payment_type']) ?></td>
                        <td><?= ucfirst($p['status']) ?></td>
                        <td><?= htmlspecialchars($p['created_at']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
</body>
</html>
