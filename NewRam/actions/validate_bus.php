<?php
session_start();
header('Content-Type: application/json');

include "../includes/connection.php";

$input = json_decode(file_get_contents("php://input"), true);
$busNumber = $input['busNumber'] ?? null;

if (!$busNumber) {
    echo json_encode(['valid' => false]);
    exit;
}

// 🧠 Query to check if busNumber exists in businfo
$stmt = $conn->prepare("SELECT COUNT(*) FROM businfo WHERE bus_number = ? AND conductorID IS NOT NULL AND conductorID != ''");
$stmt->bind_param("s", $busNumber);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

echo json_encode(['valid' => $count > 0]);
