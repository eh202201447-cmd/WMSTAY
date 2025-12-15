<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}
require "../includes/db_connect.php";

if (!isset($_GET['id'], $_GET['action'])) {
    header("Location: payments.php");
    exit;
}

$id = (int)$_GET['id'];
$action = $_GET['action'];

$status = 'pending';
if ($action === 'paid') {
    $status = 'paid';
} elseif ($action === 'rejected') {
    $status = 'rejected';
}

$stmt = $conn->prepare("UPDATE payments SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $id);
$stmt->execute();

echo "<script>alert('Payment updated to $status');window.location='payments.php';</script>";