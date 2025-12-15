<?php
session_start();
require "../includes/guard.php";
requireRole(['admin', 'staff']);
require "../includes/db_connect.php";

$id = (int)$_GET['id'];
$ticket = $conn->query("SELECT mr.*, s.full_name, r.room_number, a.name as staff_name FROM maintenance_reports mr LEFT JOIN students s ON mr.student_id = s.id LEFT JOIN rooms r ON mr.room_id = r.id LEFT JOIN admins a ON mr.assigned_staff_id = a.id WHERE mr.id=$id")->fetch_assoc();
if (!$ticket) {
    header("Location: reports.php?tab=maintenance");
    exit;
}

$staff_list = $conn->query("SELECT id, name FROM admins WHERE role='staff' OR role='admin'");

if (isset($_POST['update_ticket'])) {
    $new_status = $_POST['status'];
    $assigned_staff = (int)$_POST['assigned_staff'];
    $notes = trim($_POST['resolution_notes']);

    $update = "UPDATE maintenance_reports SET status='$new_status', assigned_staff_id=" . ($assigned_staff ? $assigned_staff : 'NULL') . ", resolution_notes='$notes'";
    if ($new_status == 'in-progress' && !$ticket['started_at']) {
        $update .= ", started_at=NOW()";
    } elseif ($new_status == 'completed' && !$ticket['completed_at']) {
        $update .= ", completed_at=NOW()";
    }
    $update .= " WHERE id=$id";

    $conn->query($update);
    header("Location: ticket_detail.php?id=$id");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket Detail - WMSTAY</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="app">
    <div class="sidebar">
        <h2 class="brand">WMSTAY Admin</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="bookings.php">Bookings</a>
        <a href="payments.php">Payments</a>
        <?php if ($_SESSION['role'] == 'admin'): ?>
        <a href="rooms.php">Rooms</a>
        <a href="reports.php">Reports</a>
        <a href="announcements.php">Announcements</a>
        <?php endif; ?>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="main">
        <h1>Ticket Detail #<?= $ticket['id'] ?></h1>

        <div class="card">
            <h2>Ticket Information</h2>
            <p><strong>Student:</strong> <?= htmlspecialchars($ticket['full_name']) ?></p>
            <p><strong>Room:</strong> <?= htmlspecialchars($ticket['room_number']) ?></p>
            <p><strong>Title:</strong> <?= htmlspecialchars($ticket['title']) ?></p>
            <p><strong>Description:</strong> <?= htmlspecialchars($ticket['description']) ?></p>
            <p><strong>Status:</strong> <?= ucfirst($ticket['status']) ?></p>
            <p><strong>Assigned Staff:</strong> <?= htmlspecialchars($ticket['staff_name'] ?? 'Unassigned') ?></p>
            <p><strong>Created:</strong> <?= $ticket['created_at'] ?></p>
            <p><strong>Started:</strong> <?= $ticket['started_at'] ?: 'Not started' ?></p>
            <p><strong>Completed:</strong> <?= $ticket['completed_at'] ?: 'Not completed' ?></p>
            <p><strong>Resolution Notes:</strong> <?= htmlspecialchars($ticket['resolution_notes'] ?: 'None') ?></p>
        </div>

        <div class="card">
            <h2>Update Ticket</h2>
            <form method="post">
                <div class="form-group">
                    <label>Status:</label>
                    <select name="status">
                        <option value="pending" <?= $ticket['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="in-progress" <?= $ticket['status'] == 'in-progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= $ticket['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Assign Staff:</label>
                    <select name="assigned_staff">
                        <option value="">Unassigned</option>
                        <?php while($staff = $staff_list->fetch_assoc()): ?>
                            <option value="<?= $staff['id'] ?>" <?= $ticket['assigned_staff_id'] == $staff['id'] ? 'selected' : '' ?>><?= htmlspecialchars($staff['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Resolution Notes:</label>
                    <textarea name="resolution_notes" rows="4"><?= htmlspecialchars($ticket['resolution_notes'] ?: '') ?></textarea>
                </div>
                <button type="submit" name="update_ticket" class="btn primary">Update Ticket</button>
            </form>
        </div>

        <a href="reports.php?tab=maintenance" class="btn">Back to Tickets</a>
    </div>
</div>
</body>
</html>