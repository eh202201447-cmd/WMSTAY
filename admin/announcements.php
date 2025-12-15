<?php
session_start();
require "../includes/guard.php";
requireRole(['admin']);
require "../includes/db_connect.php";

if (isset($_POST['post_announcement'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $created_by = $_SESSION['user_id'];

    if ($title && $content) {
        $stmt = $conn->prepare("INSERT INTO announcements (title, content, created_by) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $content, $created_by);
        $stmt->execute();

        // Send emails
        require "../includes/mailer.php";
        $announcementId = $conn->insert_id;
        $sent = sendAnnouncementEmailToAll($announcementId);
        echo "<script>alert('Announcement posted and $sent emails sent');</script>";
    }
}

$announcements = $conn->query("SELECT a.*, ad.name as admin_name FROM announcements a LEFT JOIN admins ad ON a.created_by = ad.id ORDER BY a.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Announcements - WMSTAY</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="app">
    <div class="sidebar">
        <h2 class="brand">WMSTAY Admin</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="bookings.php">Bookings</a>
        <a href="payments.php">Payments</a>
        <a href="rooms.php">Rooms</a>
        <a href="reports.php">Reports</a>
        <a href="announcements.php" class="active">Announcements</a>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="main">
        <h1>Manage Announcements</h1>

        <!-- POST ANNOUNCEMENT -->
        <div class="card">
            <h2>Post New Announcement</h2>
            <form method="post">
                <div class="form-group">
                    <label>Title:</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Content:</label>
                    <textarea name="content" rows="5" required></textarea>
                </div>
                <button type="submit" name="post_announcement" class="btn primary">Post Announcement</button>
            </form>
        </div>

        <!-- ALL ANNOUNCEMENTS -->
        <div class="card">
            <h2>All Announcements</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Posted By</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($a = $announcements->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['title']) ?></td>
                            <td><?= htmlspecialchars($a['admin_name']) ?></td>
                            <td><?= $a['created_at'] ?></td>
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