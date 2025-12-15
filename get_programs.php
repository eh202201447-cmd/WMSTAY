<?php
header('Content-Type: application/json');
require 'includes/db_connect.php';

$college_id = (int)$_GET['college_id'];

if ($college_id <= 0) {
    echo json_encode([]);
    exit;
}

$programs = $conn->query("SELECT id, program_name FROM programs WHERE college_id = $college_id ORDER BY program_name");

$result = [];
while ($row = $programs->fetch_assoc()) {
    $result[] = $row;
}

echo json_encode($result);
?>