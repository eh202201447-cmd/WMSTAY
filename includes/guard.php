<?php
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }
}

function requireRole($allowedRoles) {
    requireLogin();
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        echo "<script>alert('Access denied');window.location='../login.php';</script>";
        exit;
    }
}
?>