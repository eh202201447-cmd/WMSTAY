<?php
session_start();
require "../includes/guard.php";
requireRole(['admin', 'staff']);
require "../includes/db_connect.php";

$tab = $_GET['tab'] ?? 'maintenance';
$semester_id = $_GET['semester_id'] ?? 1;

// Semesters for filter
$semesters = $conn->query("SELECT * FROM semesters");

// Reports data based on tab
if ($tab == 'occupancy') {
    $total_capacity = $conn->query("SELECT SUM(capacity) as cap FROM rooms")->fetch_assoc()['cap'] ?? 0;
    $occupied = $conn->query("SELECT COUNT(*) as occ FROM bookings WHERE semester_id=$semester_id AND status='approved' AND room_id IS NOT NULL")->fetch_assoc()['occ'] ?? 0;
    $occupancy_rate = $total_capacity ? round(($occupied / $total_capacity) * 100, 2) : 0;
} elseif ($tab == 'applications') {
    $applicants = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE semester_id=$semester_id")->fetch_assoc()['cnt'] ?? 0;
    $accepted = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE semester_id=$semester_id AND status='approved'")->fetch_assoc()['cnt'] ?? 0;
    $total_applicants = $conn->query("SELECT COUNT(*) as cnt FROM bookings")->fetch_assoc()['cnt'] ?? 0;
    $total_accepted = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE status='approved'")->fetch_assoc()['cnt'] ?? 0;
} elseif ($tab == 'payments') {
    $paid = $conn->query("SELECT COUNT(*) as cnt FROM payments WHERE semester_id=$semester_id AND status='paid'")->fetch_assoc()['cnt'] ?? 0;
    $unpaid = $conn->query("SELECT COUNT(*) as cnt FROM payments WHERE semester_id=$semester_id AND status='pending' AND (due_date IS NULL OR due_date >= CURDATE())")->fetch_assoc()['cnt'] ?? 0;
    $overdue = $conn->query("SELECT COUNT(*) as cnt FROM payments WHERE semester_id=$semester_id AND status='pending' AND due_date < CURDATE()")->fetch_assoc()['cnt'] ?? 0;
    $total_paid = $conn->query("SELECT COUNT(*) as cnt FROM payments WHERE status='paid'")->fetch_assoc()['cnt'] ?? 0;
    $total_unpaid = $conn->query("SELECT COUNT(*) as cnt FROM payments WHERE status='pending' AND (due_date IS NULL OR due_date >= CURDATE())")->fetch_assoc()['cnt'] ?? 0;
    $total_overdue = $conn->query("SELECT COUNT(*) as cnt FROM payments WHERE status='pending' AND due_date < CURDATE()")->fetch_assoc()['cnt'] ?? 0;
} elseif ($tab == 'maintenance') {
    $filter_status = $_GET['status'] ?? '';
    $filter_date_from = $_GET['date_from'] ?? '';
    $filter_date_to = $_GET['date_to'] ?? '';
    $filter_room = $_GET['room'] ?? '';
    $filter_student = $_GET['student'] ?? '';

    $query = "SELECT mr.*, s.full_name, r.room_number, a.name as staff_name FROM maintenance_reports mr LEFT JOIN students s ON mr.student_id = s.id LEFT JOIN rooms r ON mr.room_id = r.id LEFT JOIN admins a ON mr.assigned_staff_id = a.id WHERE 1=1";
    if ($filter_status) $query .= " AND mr.status='$filter_status'";
    if ($filter_date_from) $query .= " AND mr.created_at >= '$filter_date_from'";
    if ($filter_date_to) $query .= " AND mr.created_at <= '$filter_date_to'";
    if ($filter_room) $query .= " AND r.room_number LIKE '%$filter_room%'";
    if ($filter_student) $query .= " AND s.full_name LIKE '%$filter_student%'";
    $query .= " ORDER BY mr.created_at DESC";

    $reports = $conn->query($query);
    $status_counts = $conn->query("SELECT status, COUNT(*) as cnt FROM maintenance_reports GROUP BY status");
    $avg_resolution = $conn->query("SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours FROM maintenance_reports WHERE status='completed' AND completed_at IS NOT NULL")->fetch_assoc()['avg_hours'] ?? 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Reports - WMSTAY</title>
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
        <a href="reports.php" class="active">Reports</a>
        <a href="announcements.php">Announcements</a>
        <?php endif; ?>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="main">
        <h1>Reports Dashboard</h1>

        <!-- Filters -->
        <div class="card">
            <form method="get">
                <label>Semester:</label>
                <select name="semester_id">
                    <?php $semesters->data_seek(0); while($sem = $semesters->fetch_assoc()): ?>
                        <option value="<?= $sem['id'] ?>" <?= $sem['id'] == $semester_id ? 'selected' : '' ?>><?= htmlspecialchars($sem['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="hidden" name="tab" value="<?= $tab ?>">
                <button type="submit" class="btn">Filter</button>
            </form>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <a href="?tab=occupancy&semester_id=<?= $semester_id ?>" class="<?= $tab == 'occupancy' ? 'active' : '' ?>">Occupancy</a>
            <a href="?tab=applications&semester_id=<?= $semester_id ?>" class="<?= $tab == 'applications' ? 'active' : '' ?>">Applications</a>
            <a href="?tab=payments&semester_id=<?= $semester_id ?>" class="<?= $tab == 'payments' ? 'active' : '' ?>">Payments</a>
            <a href="?tab=maintenance&semester_id=<?= $semester_id ?>" class="<?= $tab == 'maintenance' ? 'active' : '' ?>">Maintenance</a>
        </div>

        <!-- Tab Content -->
        <?php if ($tab == 'occupancy'): ?>
            <div class="card">
                <h2>Dorm Occupancy Report</h2>
                <p>Total Capacity: <?= $total_capacity ?></p>
                <p>Occupied: <?= $occupied ?></p>
                <p>Occupancy Rate: <?= $occupancy_rate ?>%</p>
                <a href="export.php?type=occupancy&semester_id=<?= $semester_id ?>" class="btn">Export CSV</a>
            </div>
        <?php elseif ($tab == 'applications'): ?>
            <div class="card">
                <h2>Applications Report</h2>
                <p>Applicants (Semester): <?= $applicants ?></p>
                <p>Accepted (Semester): <?= $accepted ?></p>
                <p>Acceptance Rate (Semester): <?= $applicants ? round(($accepted / $applicants) * 100, 2) : 0 ?>%</p>
                <p>Total Applicants: <?= $total_applicants ?></p>
                <p>Total Accepted: <?= $total_accepted ?></p>
                <a href="export.php?type=applications&semester_id=<?= $semester_id ?>" class="btn">Export CSV</a>
            </div>
        <?php elseif ($tab == 'payments'): ?>
            <div class="card">
                <h2>Payment Compliance Report</h2>
                <p>Paid (Semester): <?= $paid ?></p>
                <p>Unpaid (Semester): <?= $unpaid ?></p>
                <p>Overdue (Semester): <?= $overdue ?></p>
                <p>Total Paid: <?= $total_paid ?></p>
                <p>Total Unpaid: <?= $total_unpaid ?></p>
                <p>Total Overdue: <?= $total_overdue ?></p>
                <a href="export.php?type=payments&semester_id=<?= $semester_id ?>" class="btn">Export CSV</a>
            </div>
        <?php elseif ($tab == 'maintenance'): ?>
            <div class="card">
                <h2>Maintenance Statistics</h2>
                <p>Average Resolution Time: <?= round($avg_resolution, 2) ?> hours</p>
                <ul>
                    <?php $status_counts->data_seek(0); while($sc = $status_counts->fetch_assoc()): ?>
                        <li><?= ucfirst($sc['status']) ?>: <?= $sc['cnt'] ?></li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="card">
                <h2>Maintenance Tickets</h2>
                <!-- Filters -->
                <form method="get" style="margin-bottom: 20px;">
                    <input type="hidden" name="tab" value="maintenance">
                    <label>Status:</label>
                    <select name="status">
                        <option value="">All</option>
                        <option value="pending" <?= $filter_status == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="in-progress" <?= $filter_status == 'in-progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= $filter_status == 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                    <label>Date From:</label>
                    <input type="date" name="date_from" value="<?= $filter_date_from ?>">
                    <label>Date To:</label>
                    <input type="date" name="date_to" value="<?= $filter_date_to ?>">
                    <label>Room:</label>
                    <input type="text" name="room" value="<?= htmlspecialchars($filter_room) ?>" placeholder="Room number">
                    <label>Student:</label>
                    <input type="text" name="student" value="<?= htmlspecialchars($filter_student) ?>" placeholder="Student name">
                    <button type="submit" class="btn">Filter</button>
                </form>

                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Room</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Assigned Staff</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($r = $reports->fetch_assoc()): ?>
                            <tr>
                                <td><?= $r['id'] ?></td>
                                <td><?= htmlspecialchars($r['full_name']) ?></td>
                                <td><?= htmlspecialchars($r['room_number']) ?></td>
                                <td><?= htmlspecialchars($r['title']) ?></td>
                                <td><?= ucfirst($r['status']) ?></td>
                                <td><?= htmlspecialchars($r['staff_name'] ?? 'Unassigned') ?></td>
                                <td><?= $r['created_at'] ?></td>
                                <td>
                                    <a href="ticket_detail.php?id=<?= $r['id'] ?>" class="btn small primary">View</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <a href="export.php?type=maintenance" class="btn">Export CSV</a>
            </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
