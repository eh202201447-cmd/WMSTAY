<?php
session_start();
require "../includes/guard.php";
requireRole(['admin', 'staff']);
require "../includes/db_connect.php";

if (isset($_POST['auto_assign'])) {
    require "../includes/allocation.php";
    $semesterId = (int)$_POST['semester_id'];
    $count = allocatePendingBookings($semesterId);
    echo "<script>alert('$count rooms assigned'); window.location.reload();</script>";
}

// Pending bookings
$pending = $conn->query("
    SELECT b.*, s.full_name, r.room_number, r.room_type
    FROM bookings b
    LEFT JOIN students s ON b.student_id = s.id
    LEFT JOIN rooms r ON b.room_id = r.id
    WHERE b.status = 'pending'
    ORDER BY b.created_at ASC
");

// All bookings
$all = $conn->query("
    SELECT b.*, s.full_name, r.room_number, r.room_type, b.priority_score, b.allocation_reason
    FROM bookings b
    LEFT JOIN students s ON b.student_id = s.id
    LEFT JOIN rooms r ON b.room_id = r.id
    ORDER BY b.created_at DESC
");

// Semesters for select
$semesters = $conn->query("SELECT * FROM semesters");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Bookings - WMSTAY</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="app">
    <div class="sidebar">
        <h2 class="brand">WMSTAY Admin</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="bookings.php" class="active">Bookings</a>
        <a href="payments.php">Payments</a>
        <?php if ($_SESSION['role'] == 'admin'): ?>
        <a href="rooms.php">Rooms</a>
        <a href="reports.php">Reports</a>
        <a href="announcements.php">Announcements</a>
        <?php endif; ?>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="main">

        <h1>Manage Bookings</h1>

        <!-- PENDING BOOKINGS -->
        <div class="card">
            <h2>Pending Bookings</h2>

            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($b = $pending->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($b['full_name']) ?></td>
                            <td><?= htmlspecialchars($b['room_number']) ?></td>
                            <td><?= htmlspecialchars($b['room_type']) ?></td>
                            <td><?= ucfirst($b['status']) ?></td>
                            <td><?= $b['created_at'] ?></td>
                            <td>
                                <a class="btn small primary"
                                   href="update_booking.php?id=<?= $b['id'] ?>&action=approve">
                                   Approve
                                </a>
                                <a class="btn small danger"
                                   href="update_booking.php?id=<?= $b['id'] ?>&action=reject">
                                   Reject
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- AUTO ASSIGN -->
        <div class="card">
            <h2>Auto-Assign Rooms</h2>
            <form method="post">
                <label for="semester_id">Semester:</label>
                <select name="semester_id" id="semester_id">
                    <?php while($sem = $semesters->fetch_assoc()): ?>
                        <option value="<?= $sem['id'] ?>"><?= htmlspecialchars($sem['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="auto_assign" class="btn primary">Auto-Assign Rooms</button>
            </form>
        </div>

        <!-- ALL BOOKINGS -->
        <div class="card">
            <h2>All Bookings</h2>

            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Room</th>
                            <th>Status</th>
                            <th>Priority Score</th>
                            <th>Allocation Reason</th>
                            <th>Created</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($b = $all->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($b['full_name']) ?></td>
                            <td><?= $b['room_number'] ? htmlspecialchars($b['room_number']) : 'Not Assigned' ?></td>
                            <td><?= ucfirst($b['status']) ?></td>
                            <td><?= $b['priority_score'] ?></td>
                            <td><?= htmlspecialchars($b['allocation_reason'] ?? '') ?></td>
                            <td><?= $b['created_at'] ?></td>
                            <td><?= $b['updated_at'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
</body>
</html>