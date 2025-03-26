<?php
header('Content-Type: application/json');  // Set the content type as JSON

include "../includes/connection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the JSON input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Check if rfid_scan is set in the request
    if (isset($input['rfid_scan'])) {
        $rfid_scan = $input['rfid_scan'];  // Extract the RFID scan

        // Prepare and execute the query
        $stmt = $conn->prepare("SELECT u.firstname, u.lastname, t.bus_number, SUM(t.amount) AS total_load
                                FROM useracc u
                                LEFT JOIN transactions t ON t.conductor_id = u.account_number
                                WHERE u.account_number = ? GROUP BY u.account_number, t.bus_number");
        $stmt->bind_param("s", $rfid_scan);
        $stmt->execute();
        $stmt->bind_result($firstname, $lastname, $bus_number, $total_load);
        
        $response = [];
        if ($stmt->fetch()) {
            // Successfully found the record
            $response = [
                'success' => true,
                'bus_number' => $bus_number,
                'conductor_name' => $firstname . ' ' . $lastname,
                'total_load' => $total_load
            ];
        } else {
            // No record found for the RFID
            $response = [
                'success' => false,
                'message' => 'RFID not found or no data available.'
            ];
        }

        $stmt->close();
    } else {
        // Handle the case where RFID scan is not provided
        $response = [
            'success' => false,
            'message' => 'Invalid request'
        ];
    }

    // Return the response as JSON
    echo json_encode($response);
} else {
    // If it's not a POST request
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
