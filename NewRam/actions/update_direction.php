<?php
session_start();
include "../includes/connection.php";

header('Content-Type: application/json'); // Always send JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['direction'])) {
    $_SESSION['direction'] = $_POST['direction'];
    $bus_number = $_SESSION['bus_number'];

    $query = "UPDATE businfo SET destination = ? WHERE bus_number = ?";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ss", $_SESSION['direction'], $bus_number);
        if ($stmt->execute()) {
            $stmt->close();
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Execute error: ' . $stmt->error
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Prepare error: ' . $conn->error
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
