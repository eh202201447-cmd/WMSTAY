<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}
require "../includes/db_connect.php";

$id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$room_number = $_POST['room_number'];
$room_type   = $_POST['room_type'];
$status      = $_POST['status'];
$rent_fee    = $_POST['rent_fee'];
$description = $_POST['description'];

// Handle upload
$image_path = null;
if (!empty($_FILES['room_image']['name'])) {
    $targetDir = "../assets/uploads/rooms/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $fileName = time() . "_" . basename($_FILES["room_image"]["name"]);
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["room_image"]["tmp_name"], $targetFile)) {
        // Save path relative to project root
        $image_path = "assets/uploads/rooms/" . $fileName;
    }
}

if ($id > 0) {
    // Update
    if ($image_path) {
        $stmt = $conn->prepare("UPDATE rooms SET room_number=?, room_type=?, status=?, rent_fee=?, description=?, image_path=? WHERE id=?");
        $stmt->bind_param("sssissi", $room_number, $room_type, $status, $rent_fee, $description, $image_path, $id);
    } else {
        $stmt = $conn->prepare("UPDATE rooms SET room_number=?, room_type=?, status=?, rent_fee=?, description=? WHERE id=?");
        $stmt->bind_param("sssisi", $room_number, $room_type, $status, $rent_fee, $description, $id);
    }
    $stmt->execute();
    echo "<script>alert('Room updated');window.location='rooms.php';</script>";
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO rooms (room_number, room_type, status, rent_fee, description, image_path) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("sssiss", $room_number, $room_type, $status, $rent_fee, $description, $image_path);
    $stmt->execute();
    echo "<script>alert('Room added');window.location='rooms.php';</script>";
}