<?php
session_start();
require "../includes/guard.php";
requireRole(['student']);
require "../includes/db_connect.php";

$student_id = $_SESSION['user_id'];

// Fetch all AVAILABLE rooms (added by admin)
$rooms = $conn->query("
    SELECT * FROM rooms
    WHERE status = 'available'
    ORDER BY id DESC
");

// Fetch this student's bookings with room info
$mybookings = $conn->query("
    SELECT b.*, r.room_number, r.room_type, r.rent_fee
    FROM bookings b
    LEFT JOIN rooms r ON b.room_id = r.id
    WHERE b.student_id = $student_id
    ORDER BY b.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Bookings - WMSTAY</title>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="../assets/script.js" defer></script>
</head>
<body>
<div class="app">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2 class="brand">WMSTAY</h2>
        <a href="/wmstay/student/dashboard.php">Dashboard</a>
        <a href="/wmstay/student/bookings.php" class="active">My Bookings</a>
        <a href="/wmstay/student/payments.php">My Payments</a>
        <a href="/wmstay/student/reports.php">Report / Maintenance</a>
        <a href="/wmstay/student/profile.php">Profile</a>
        <a href="/wmstay/logout.php">Logout</a>
    </div>

    <!-- MAIN -->
    <div class="main">
        <div class="page-header">
            <h1 class="page-title">My Bookings</h1>
            <button id="menuToggle" class="btn small">☰</button>
        </div>

        <!-- ============= AVAILABLE ROOMS (Top card like screenshot) ============= -->
        <div class="card">
            <h2 class="section-title">
                <span class="section-icon">🏨</span> Available Rooms
            </h2>

            <div class="room-scroll">
                <?php if ($rooms->num_rows == 0): ?>
                    <p>No available rooms right now.</p>
                <?php else: ?>
                    <?php while($r = $rooms->fetch_assoc()): ?>
                        <div class="room-card">
                            <div class="room-image">
                                <?php if (!empty($r['image_path'])): ?>
                                    <img src="/wmstay/<?= htmlspecialchars($r['image_path']) ?>" alt="Room">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/400x250?text=Room" alt="Room">
                                <?php endif; ?>
                            </div>
                            <div class="room-body">
                                <!-- title line: 14 Double Room -->
                                <h3 class="room-title">
                                    <?= htmlspecialchars($r['room_number']) ?> <?= htmlspecialchars($r['room_type']) ?>
                                </h3>
                                <!-- smaller line under title: double room -->
                                <p class="room-type"><?= htmlspecialchars($r['room_type']) ?></p>

                                <!-- rent + availability, like screenshot -->
                                <p class="room-price">
                                    <?= number_format($r['rent_fee'], 2) ?> /month
                                </p>
                                <p class="room-status">Available</p>

                                <!-- Book Now button -->
                                <a class="btn primary book-btn"
                                   href="/wmstay/student/book_room.php?id=<?= $r['id'] ?>">
                                    Book Now
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============= MY BOOKINGS (cards like screenshot) ============= -->
        <div class="card">
            <h2 class="section-title">
                <span class="section-icon">📅</span> My Bookings
            </h2>

            <?php if ($mybookings->num_rows == 0): ?>
                <p>You don't have any bookings yet.</p>
            <?php else: ?>
                <?php while($b = $mybookings->fetch_assoc()): 
                    $statusClass = 'status-pending';
                    if ($b['status'] === 'approved') $statusClass = 'status-approved';
                    if ($b['status'] === 'rejected') $statusClass = 'status-rejected';
                ?>
                    <div class="booking-card">
                        <!-- Status chip on the right -->
                        <span class="status-badge <?= $statusClass ?>">
                            <?= ucfirst($b['status']) ?>
                        </span>

                        <p><b>Room:</b> <?= htmlspecialchars($b['room_number']) ?> <?= htmlspecialchars($b['room_type']) ?></p>
                        <p>Booked on: <?= htmlspecialchars($b['created_at']) ?></p>
                        <p><b>Monthly Rent:</b> <?= number_format($b['rent_fee'], 2) ?></p>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

    </div>
</div>
</body>
</html>