<?php
session_start();
require "../includes/guard.php";
requireRole(['student']);
require "../includes/db_connect.php";

$student_id = $_SESSION['user_id'];
$room_id = $_GET['id'];

$conn->query("INSERT INTO bookings (student_id, room_id, status, semester_id) VALUES ($student_id, NULL, 'pending', 1)"); // Assume semester 1

echo "<script>alert('Room booked! Waiting for admin approval.');window.location='bookings.php';</script>";
