<?php

include "../includes/connection.php";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $account_number = $_POST['account_number'] ?? null; // Get the account number

    // Output in JSON format for AJAX responses
    header('Content-Type: application/json');

    if ($user_id && $account_number) {
        try {
            // Update both is_activated and account_number
            $activateQuery = "UPDATE useracc SET is_activated = 1, account_number = ? WHERE id = ?";
            $stmt = $conn->prepare($activateQuery);
            
            if ($stmt === false) {
                throw new Exception("Error preparing statement: " . $conn->error);
            }

            $stmt->bind_param("si", $account_number, $user_id);

            if ($stmt->execute()) {
                // Success
                echo json_encode(['success' => true, 'message' => 'User activated with account number.']);
            } else {
                // Execution error
                throw new Exception("Error executing statement: " . $stmt->error);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            // Catch and return error message
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User ID or Account Number is missing.']);
    }
}

?>