<?php
session_start();
if (!isset($_SESSION['student_id'])) { header("Location: ../login.php"); exit; }
require "../includes/db_connect.php";

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT * FROM students WHERE id=$student_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="app">
    <div class="sidebar">
        <h2 class="brand">WMSTAY</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="bookings.php">My Bookings</a>
        <a href="payments.php">My Payments</a>
        <a href="reports.php">Report / Maintenance</a>
        <a href="profile.php" class="active">Profile</a>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="main">

        <h1>My Profile</h1>

        <div class="card">
            <p><b>Name:</b> <?= $student['full_name'] ?></p>
            <p><b>Email:</b> <?= $student['email'] ?></p>
            <p><b>Department:</b> <?= $student['department'] ?></p>
            <p><b>Program:</b> <?= $student['program'] ?></p>
            <p><b>Gender:</b> <?= $student['gender'] ?></p>
        </div>

        <h2>Edit Profile</h2>
        <form class="card" action="update_profile.php" method="POST">
            <div class="form-group">
                <label>Program</label>
                <input type="text" name="program" value="<?= $student['program'] ?>" required>
            </div>
            <div class="form-group">
                <label>Contact</label>
                <input type="text" name="contact" value="<?= $student['contact'] ?>" required>
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" required><?= $student['address'] ?></textarea>
            </div>
            <button class="btn primary">Update</button>
        </form>

    </div>
</div>

</body>
</html>
