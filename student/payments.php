<?php
session_start();
if (!isset($_SESSION['student_id'])) { header("Location: ../login.php"); exit; }
require "../includes/db_connect.php";

$student_id = $_SESSION['student_id'];
$payments = $conn->query("SELECT * FROM payments WHERE student_id=$student_id ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Payments</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="app">
    <div class="sidebar">
        <h2 class="brand">WMSTAY</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="bookings.php">My Bookings</a>
        <a href="payments.php" class="active">My Payments</a>
        <a href="reports.php">Report / Maintenance</a>
        <a href="profile.php">Profile</a>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="main">

        <h1>Make a Payment</h1>

        <form action="pay.php" method="POST" class="card">
            <div class="form-group">
                <label>Amount</label>
                <input type="number" step="0.01" name="amount" required>
            </div>

            <div class="form-group">
                <label>Payment Type</label>
                <select name="payment_type" required>
                    <option>Monthly</option>
                    <option>Semester</option>
                </select>
            </div>

            <button class="btn primary">Submit Payment</button>
        </form>

        <h2>Payment History</h2>
        <table class="table">
            <thead><tr><th>Amount</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                <?php while($p = $payments->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['amount'] ?></td>
                    <td><?= $p['payment_type'] ?></td>
                    <td><?= ucfirst($p['status']) ?></td>
                    <td><?= $p['created_at'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>
