<?php
// Start the session to access session variables
session_start();

// Ensure content type is set to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include "../includes/connection.php";

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"));

// Check if route_name is set in the POST data
if (isset($data->route_name)) {
    $route_name = $data->route_name;

    // Check if bus_number is set in the session
    if (isset($_SESSION['bus_number'])) {
        $bus_number = $_SESSION['bus_number'];
    } else {
        // If bus_number is not set in the session, return an error
        echo json_encode(['success' => false, 'error' => 'Bus number not found in session']);
        exit;
    }

    // Prepare the SQL query to update the current_stop column
    $sql = "UPDATE businfo SET current_stop = ? WHERE bus_number = ?";

    // Prepare and execute the SQL query
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ss', $route_name, $bus_number);

        if (mysqli_stmt_execute($stmt)) {
            // Send success response back to the client
            echo json_encode(['success' => true]);
        } else {
            // Send error response back to the client
            echo json_encode(['success' => false, 'error' => 'Failed to update route']);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Route name not provided']);
}
?>
