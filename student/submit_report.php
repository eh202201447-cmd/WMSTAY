<?php
session_start();
require "../includes/guard.php";
requireRole(['student']);

require "../includes/db_connect.php";

$title = $_POST['title'];
$description = $_POST['description'];
$student_id = $_SESSION['user_id'];

// Get room_id from approved booking
$booking = $conn->query("SELECT room_id FROM bookings WHERE student_id=$student_id AND status='approved' AND room_id IS NOT NULL LIMIT 1")->fetch_assoc();
$room_id = $booking ? $booking['room_id'] : NULL;

$stmt = $conn->prepare("INSERT INTO maintenance_reports (student_id, room_id, title, description, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param("iisss", $student_id, $room_id, $title, $description);
$stmt->execute();

echo "<script>alert('Report submitted');window.location='reports.php';</script>";
