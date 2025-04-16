<?php
// check_driver_assigned.php
include "../includes/connection.php";

$data = json_decode(file_get_contents("php://input"), true);
$account_number = $data['account_number'] ?? '';

$response = ['assigned' => false];

if ($account_number) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM businfo WHERE driverID = ?");
    $stmt->bind_param("s", $account_number);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $response['assigned'] = true;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
