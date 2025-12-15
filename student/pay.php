<?php
session_start();
if (!isset($_SESSION['student_id'])) { header("Location: ../login.php"); exit; }

require "../includes/db_connect.php";

$student_id = $_SESSION['student_id'];
$amount = $_POST['amount'];
$type   = $_POST['payment_type'];

$query = $conn->prepare("INSERT INTO payments (student_id, amount, payment_type, status) VALUES (?, ?, ?, 'pending')");
$query->bind_param("ids", $student_id, $amount, $type);
$query->execute();

echo "<script>alert('Payment submitted for verification');window.location='payments.php';</script>";
