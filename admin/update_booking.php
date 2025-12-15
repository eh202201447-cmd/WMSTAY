<?php
session_start();
require "../includes/guard.php";
requireRole(['admin', 'staff']);
require "../includes/db_connect.php";

if (!isset($_GET['id'], $_GET['action'])) {
    header("Location: bookings.php");
    exit;
}

$id = (int)$_GET['id'];
$action = $_GET['action'];

$status = 'pending';
if ($action === 'approve') {
    $status = 'approved';
} elseif ($action === 'reject') {
    $status = 'rejected';
}

// Get room id for capacity handling (simple: mark room not available if approved)
$booking = $conn->query("SELECT * FROM bookings WHERE id=$id")->fetch_assoc();
if (!$booking) {
    header("Location: bookings.php");
    exit;
}

$room_id = (int)$booking['room_id'];

$stmt = $conn->prepare("UPDATE bookings SET status=?, updated_at=NOW() WHERE id=?");
$stmt->bind_param("si", $status, $id);
$stmt->execute();

if ($status === 'approved' && $room_id) {
    // Increment occupancy
    $conn->query("UPDATE rooms SET occupancy=occupancy+1 WHERE id=$room_id");
    // Check if full
    $room = $conn->query("SELECT capacity, occupancy+1 as new_occ FROM rooms WHERE id=$room_id")->fetch_assoc();
    if ($room && $room['new_occ'] >= $room['capacity']) {
        $conn->query("UPDATE rooms SET status='not available' WHERE id=$room_id");
    }
}

// Send email notification
require "../includes/mailer.php";
require "../includes/email_templates.php";
$booking = $conn->query("SELECT student_id FROM bookings WHERE id=$id")->fetch_assoc();
if ($booking) {
    $details = $status === 'approved' ? 'Your room has been assigned.' : 'Please check your application.';
    sendApplicationStatusEmail($booking['student_id'], $status, $details);
}

echo "<script>alert('Booking updated to $status');window.location='bookings.php';</script>";
