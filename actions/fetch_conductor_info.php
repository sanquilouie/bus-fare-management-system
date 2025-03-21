<?php
session_start();
include "../includes/connection.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['rfid']) || empty($data['rfid'])) {
    echo json_encode(['success' => false, 'message' => 'RFID is required.']);
    exit;
}

$rfid = $data['rfid'];

// First, fetch the conductor name (full name)
$stmt = $conn->prepare("SELECT u.firstname, u.lastname, t.bus_number, t.conductor_id
                        FROM useracc u
                        LEFT JOIN transactions t ON t.conductor_id = u.account_number
                        WHERE u.account_number = ? 
                        ORDER BY t.transaction_date DESC LIMIT 1;");

$stmt->bind_param("s", $rfid);
$stmt->execute();

$stmt->bind_result($firstname, $lastname, $bus_number, $conductor_id);
if ($stmt->fetch()) {
    $conductor_name = trim($firstname . ' ' . $lastname);
    error_log("Fetched conductor_id: " . $conductor_id);
} else {
    echo json_encode(['success' => false, 'message' => 'No conductor found for this RFID.']);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();

// Now, fetch the total load and total fare based on the conductor's bus number
$stmt = $conn->prepare("SELECT 
    (SELECT SUM(amount) FROM transactions WHERE status ='notremitted' AND bus_number = ? AND conductor_id = ? AND DATE(transaction_date) = CURDATE()) AS total_load,
    (SELECT SUM(fare) FROM passenger_logs WHERE status ='notremitted' AND bus_number = ? AND conductor_name = ? AND rfid = 'cash' AND DATE(timestamp) = CURDATE()) as total_fare
FROM DUAL;"); 

$stmt->bind_param("ssss", $bus_number, $rfid, $bus_number, $conductor_name);
$stmt->execute();

$stmt->bind_result($total_load, $total_fare);

if ($stmt->fetch()) {
    echo json_encode([
        'success' => true,
        'conductor_name' => $conductor_name,
        'bus_number' => $bus_number,
        'total_load' => number_format((float) $total_load, 2, '.', ''), // No commas
        'total_fare' => number_format((float) $total_fare, 2, '.', ''),  // No commas
   
        'conductor_id' => $conductor_id
    ]);


} else {
    echo json_encode(['success' => false, 'message' => 'Error fetching total fare and load.']);
}

$stmt->close();
$conn->close();
?>