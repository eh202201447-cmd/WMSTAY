<?php
session_start();
require "../includes/guard.php";
requireRole(['admin']);
require "../includes/db_connect.php";

$edit_room = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_room = $conn->query("SELECT * FROM rooms WHERE id=$id")->fetch_assoc();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM rooms WHERE id=$id");
    echo "<script>alert('Room deleted');window.location='rooms.php';</script>";
    exit;
}

$rooms = $conn->query("SELECT * FROM rooms ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Rooms - WMSTAY</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="app">
    <div class="sidebar">
        <h2 class="brand">WMSTAY Admin</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="bookings.php">Bookings</a>
        <a href="payments.php">Payments</a>
        <a href="rooms.php" class="active">Rooms</a>
        <a href="reports.php">Reports</a>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="main">

        <h1>Rooms Management</h1>

        <div class="card">
            <h2><?= $edit_room ? "Edit Room" : "Add New Room" ?></h2>

            <form action="save_room.php" method="POST" enctype="multipart/form-data">
                <?php if ($edit_room): ?>
                    <input type="hidden" name="id" value="<?= $edit_room['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Room Number</label>
                    <input type="text" name="room_number" required
                           value="<?= $edit_room['room_number'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Room Type</label>
                    <select name="room_type" required>
                        <?php
                        $types = ["Bed Spacer","Single Room","Double Room"];
                        $current = $edit_room['room_type'] ?? '';
                        foreach ($types as $t) {
                            $sel = ($t == $current) ? 'selected' : '';
                            echo "<option $sel>$t</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <?php
                        $stats = ["available","not available"];
                        $currentS = $edit_room['status'] ?? 'available';
                        foreach ($stats as $s) {
                            $sel = ($s == $currentS) ? 'selected' : '';
                            echo "<option value=\"$s\" $sel>" . ucfirst($s) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Rent Fee</label>
                    <input type="number" step="0.01" name="rent_fee" required
                           value="<?= $edit_room['rent_fee'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"><?= $edit_room['description'] ?? '' ?></textarea>
                </div>

                <div class="form-group">
                    <label>Room Image (1 only)</label>
                    <input type="file" name="room_image" accept="image/*">
                    <?php if (!empty($edit_room['image_path'])): ?>
                        <p>Current: <img src="../<?= $edit_room['image_path'] ?>" alt="Room" style="max-width:120px;"></p>
                    <?php endif; ?>
                </div>

                <button class="btn primary" type="submit">
                    <?= $edit_room ? "Update Room" : "Save Room" ?>
                </button>
            </form>
        </div>

        <div class="card">
            <h2>Room List</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Rent</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($r = $rooms->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['room_number']) ?></td>
                        <td><?= htmlspecialchars($r['room_type']) ?></td>
                        <td><?= ucfirst($r['status']) ?></td>
                        <td><?= $r['rent_fee'] ?></td>
                        <td>
                            <?php if ($r['image_path']): ?>
                                <img src="../<?= $r['image_path'] ?>" alt="Room" style="max-width:80px;">
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="btn small" href="rooms.php?edit=<?= $r['id'] ?>">Edit</a>
                            <a class="btn small danger"
                               href="rooms.php?delete=<?= $r['id'] ?>"
                               onclick="return confirm('Delete this room?');">
                               Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
</body>
</html>
