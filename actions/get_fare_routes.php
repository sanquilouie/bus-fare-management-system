<?php
// Include your database connection here
include "../includes/connection.php";

// Get the JSON payload from the POST request
$data = json_decode(file_get_contents('php://input'), true);

error_log(print_r($data, true));
// Get the current stop from the incoming request
$currentstop = $data['currentStop'];

$query = "SELECT id, route_name, post, province, regular_fare, discounted_fare, special_fare FROM fare_routes WHERE route_name = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    error_log('MySQL prepare error: ' . $conn->error);
}
$stmt->bind_param("s", $currentstop);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    error_log('MySQL query error: ' . $stmt->error);
}

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'No Location Detected']);
}


$stmt->close();
$conn->close();
?>
