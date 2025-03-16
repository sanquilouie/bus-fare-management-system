<?php
session_start();
include "../includes/connection.php";

// Ensure this script only runs for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the account number and points to convert from the request
    $accountNumber = $_SESSION['account_number']; // Use account number from session
    $pointsToConvert = $_POST['points'];

    // Validate points
    if ($pointsToConvert <= 0) {
        // Return error message as JSON
        echo json_encode(['status' => 'error', 'message' => 'Points must be greater than zero.']);
        exit();
    }

    // Fetch the user's current points to ensure they have enough
    $stmt = $conn->prepare("SELECT points, balance FROM useracc WHERE account_number = ?");
    $stmt->bind_param("s", $accountNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || $user['points'] < $pointsToConvert) {
        // Return error if not enough points
        echo json_encode(['status' => 'error', 'message' => 'Not enough points to convert.']);
        exit();
    }

    // Convert points to pesos (1 point = 1 peso)
    $balanceEquivalent = $pointsToConvert; // Direct 1:1 conversion

    // Update the user's balance and points in the database
    $stmt = $conn->prepare("UPDATE useracc SET balance = balance + ?, points = points - ? WHERE account_number = ?");
    $stmt->bind_param("dds", $balanceEquivalent, $pointsToConvert, $accountNumber);
    $stmt->execute();

    // Fetch the updated balance after conversion
    $stmt = $conn->prepare("SELECT balance FROM useracc WHERE account_number = ?");
    $stmt->bind_param("s", $accountNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $updatedUser = $result->fetch_assoc();
    $newBalance = $updatedUser['balance'];

    // Check if the update was successful
    if ($stmt->affected_rows > 0) {
        // Return success message along with the updated balance as JSON
        echo json_encode([
            'status' => 'success',
            'message' => 'Points converted to balance successfully!',
            'new_balance' => $newBalance
        ]);
    } else {
        // Return error message if the update failed
        echo json_encode(['status' => 'error', 'message' => 'Failed to convert points. Please try again.']);
    }

    $stmt->close();
    exit();
} else {
    // If not a POST request, return error
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}
