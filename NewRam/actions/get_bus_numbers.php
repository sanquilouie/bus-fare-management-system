<?php
header('Content-Type: application/json');

include "../includes/connection.php";

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$sql = "SELECT bus_number FROM businfo";
$result = $conn->query($sql);

$busNumbers = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $busNumbers[] = $row['bus_number'];
    }
}

echo json_encode($busNumbers);

$conn->close();
?>
