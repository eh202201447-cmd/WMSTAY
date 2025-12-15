<?php
session_start();
require "../includes/guard.php";
requireRole(['admin', 'staff']);
require "../includes/db_connect.php";

if (!isset($_GET['id'], $_GET['status'])) {
    header("Location: reports.php");
    exit;
}

$id = (int)$_GET['id'];
$status = $_GET['status'];

$allowed = ['pending','in-progress','resolved'];
if (!in_array($status, $allowed, true)) {
    header("Location: reports.php");
    exit;
}

$update_sql = "UPDATE maintenance_reports SET status=?, updated_at=NOW()";
if ($status == 'resolved') {
    $update_sql .= ", resolved_at=NOW()";
}
$update_sql .= " WHERE id=?";

$stmt = $conn->prepare($update_sql);
if ($status == 'resolved') {
    $stmt->bind_param("si", $status, $id);
} else {
    $stmt->bind_param("si", $status, $id);
}
$stmt->execute();

echo "<script>alert('Report updated to $status');window.location='reports.php';</script>";