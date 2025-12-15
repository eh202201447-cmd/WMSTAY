<?php
session_start();
require __DIR__ . '/db_connect.php';

if (isset($_POST['login'])) {
    $login_id = trim($_POST['login_id']);
    $password = $_POST['password'];

    // 1. Check admin
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? OR email = ?");
    $stmt->bind_param('ss', $login_id, $login_id);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['role'] = $admin['role'];
        $_SESSION['name'] = $admin['name'];
        $_SESSION['user_type'] = 'admin';
        header("Location: /wmstay/admin/dashboard.php");
        exit;
    }

    // 2. Check student (email or student number)
    $stmt = $conn->prepare("SELECT * FROM students WHERE email = ? OR student_number = ?");
    $stmt->bind_param('ss', $login_id, $login_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    if ($student && password_verify($password, $student['password_hash'])) {
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['role'] = 'student';
        $_SESSION['name'] = $student['full_name'];
        $_SESSION['user_type'] = 'student';
        header("Location: /wmstay/student/dashboard.php");
        exit;
    }

    echo "<script>alert('Invalid credentials');window.location='../login.php';</script>";
}