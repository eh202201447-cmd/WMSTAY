<?php
session_start();
require "../includes/guard.php";
requireRole(['admin', 'staff']);
require "../includes/db_connect.php";

$type = $_GET['type'] ?? '';
$semester_id = $_GET['semester_id'] ?? 1;

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $type . '_report.csv"');

$output = fopen('php://output', 'w');

if ($type == 'occupancy') {
    $total_capacity = $conn->query("SELECT SUM(capacity) as cap FROM rooms")->fetch_assoc()['cap'] ?? 0;
    $occupied = $conn->query("SELECT COUNT(*) as occ FROM bookings WHERE semester_id=$semester_id AND status='approved' AND room_id IS NOT NULL")->fetch_assoc()['occ'] ?? 0;
    $occupancy_rate = $total_capacity ? round(($occupied / $total_capacity) * 100, 2) : 0;

    fputcsv($output, ['Metric', 'Value']);
    fputcsv($output, ['Total Capacity', $total_capacity]);
    fputcsv($output, ['Occupied', $occupied]);
    fputcsv($output, ['Occupancy Rate (%)', $occupancy_rate]);
} elseif ($type == 'applications') {
    $applicants = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE semester_id=$semester_id")->fetch_assoc()['cnt'] ?? 0;
    $accepted = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE semester_id=$semester_id AND status='approved'")->fetch_assoc()['cnt'] ?? 0;
    $total_applicants = $conn->query("SELECT COUNT(*) as cnt FROM bookings")->fetch_assoc()['cnt'] ?? 0;
    $total_accepted = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE status='approved'")->fetch_assoc()['cnt'] ?? 0;

    fputcsv($output, ['Metric', 'Semester', 'Total']);
    fputcsv($output, ['Applicants', $applicants, $total_applicants]);
    fputcsv($output, ['Accepted', $accepted, $total_accepted]);
} elseif ($type == 'payments') {
    $paid = $conn->query("SELECT COUNT(*) as cnt FROM payments WHERE semester_id=$semester_id AND status='paid'")->fetch_assoc()['cnt'] ?? 0;
    $unpaid = $conn->query("SELECT COUNT(*) as cnt FROM payments WHERE semester_id=$semester_id AND status='pending' AND (due_date IS NULL OR due_date >= CURDATE())")->fetch_assoc()['cnt'] ?? 0;
    $overdue = $conn->query("SELECT COUNT(*) as cnt FROM payments WHERE semester_id=$semester_id AND status='pending' AND due_date < CURDATE()")->fetch_assoc()['cnt'] ?? 0;
    $total_paid = $conn->query("SELECT COUNT(*) as cnt FROM payments WHERE status='paid'")->fetch_assoc()['cnt'] ?? 0;
    $total_unpaid = $conn->query("SELECT COUNT(*) as cnt FROM payments WHERE status='pending' AND (due_date IS NULL OR due_date >= CURDATE())")->fetch_assoc()['cnt'] ?? 0;
    $total_overdue = $conn->query("SELECT COUNT(*) as cnt FROM payments WHERE status='pending' AND due_date < CURDATE()")->fetch_assoc()['cnt'] ?? 0;

    fputcsv($output, ['Status', 'Semester', 'Total']);
    fputcsv($output, ['Paid', $paid, $total_paid]);
    fputcsv($output, ['Unpaid', $unpaid, $total_unpaid]);
    fputcsv($output, ['Overdue', $overdue, $total_overdue]);
} elseif ($type == 'maintenance') {
    fputcsv($output, ['ID', 'Student', 'Room', 'Title', 'Description', 'Status', 'Created', 'Updated']);
    $reports = $conn->query("
        SELECT mr.*, s.full_name, r.room_number
        FROM maintenance_reports mr
        LEFT JOIN students s ON mr.student_id = s.id
        LEFT JOIN rooms r ON mr.room_id = r.id
        ORDER BY mr.created_at DESC
    ");
    while ($r = $reports->fetch_assoc()) {
        fputcsv($output, [$r['id'], $r['full_name'], $r['room_number'], $r['title'], $r['description'], $r['status'], $r['created_at'], $r['updated_at']]);
    }
}

fclose($output);
?>