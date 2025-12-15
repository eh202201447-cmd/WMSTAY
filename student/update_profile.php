<?php
session_start();
if (!isset($_SESSION['student_id'])) { header("Location: ../login.php"); exit; }
require "../includes/db_connect.php";

$student_id = $_SESSION['student_id'];

$program = $_POST['program'];
$contact = $_POST['contact'];
$address = $_POST['address'];

$stmt = $conn->prepare("UPDATE students SET program=?, contact=?, address=? WHERE id=?");
$stmt->bind_param("sssi", $program, $contact, $address, $student_id);
$stmt->execute();

echo "<script>alert('Profile updated!');window.location='profile.php';</script>";
