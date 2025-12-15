<?php
require "db_connect.php";

function calculatePriorityScore($student) {
    $score = 0;
    if ($student['is_scholar']) $score += 10;
    $score += $student['distance_km'] * 0.1; // Higher distance, slightly higher priority
    return $score;
}

function allocateRoom($bookingId) {
    global $conn;
    $booking = $conn->query("SELECT * FROM bookings WHERE id=$bookingId")->fetch_assoc();
    if (!$booking) return false;
    $student = $conn->query("SELECT * FROM students WHERE id={$booking['student_id']}")->fetch_assoc();
    if (!$student) return false;

    // Calculate priority if not set
    if ($booking['priority_score'] == 0) {
        $priority = calculatePriorityScore($student);
        $conn->query("UPDATE bookings SET priority_score=$priority WHERE id=$bookingId");
    }

    $gender = $student['gender'];
    $course = $student['course'];
    $year = $student['year_level'];

    // Find available rooms
    $rooms = $conn->query("SELECT * FROM rooms WHERE status='available' AND occupancy < capacity AND (gender_allowed='Any' OR gender_allowed='$gender')");
    $candidates = [];
    while ($room = $rooms->fetch_assoc()) {
        $score = 0;
        if ($room['gender_allowed'] == $gender || $room['gender_allowed'] == 'Any') $score += 10;
        if ($room['room_group'] == $course) $score += 5;
        if (strpos($room['room_group'], (string)$year) !== false) $score += 5;
        $candidates[] = ['room' => $room, 'score' => $score];
    }

    if (empty($candidates)) {
        $conn->query("UPDATE bookings SET allocation_reason='No suitable room available' WHERE id=$bookingId");
        return false;
    }

    // Sort by score desc, then rent asc
    usort($candidates, function($a, $b) {
        if ($a['score'] == $b['score']) {
            return $a['room']['rent_fee'] <=> $b['room']['rent_fee'];
        }
        return $b['score'] <=> $a['score'];
    });

    $chosen = $candidates[0]['room'];

    // Assign
    $conn->query("UPDATE bookings SET room_id={$chosen['id']}, allocation_reason='Auto-assigned: " . ($chosen['room_group'] ? "Group match" : "Gender match") . "' WHERE id=$bookingId");
    $conn->query("UPDATE rooms SET occupancy=occupancy+1 WHERE id={$chosen['id']}");
    // If full, set status
    $new_occ = $chosen['occupancy'] + 1;
    if ($new_occ >= $chosen['capacity']) {
        $conn->query("UPDATE rooms SET status='not available' WHERE id={$chosen['id']}");
    }
    return true;
}

function allocatePendingBookings($semesterId) {
    global $conn;
    $bookings = $conn->query("SELECT id FROM bookings WHERE status='approved' AND room_id IS NULL AND semester_id=$semesterId ORDER BY priority_score DESC");
    $count = 0;
    while ($b = $bookings->fetch_assoc()) {
        if (allocateRoom($b['id'])) $count++;
    }
    return $count;
}
?>