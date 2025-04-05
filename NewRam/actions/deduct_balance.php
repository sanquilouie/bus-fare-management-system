<?php
session_start();
include "../includes/connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deduct_balance'])) {
    $account_number = $_POST['deduct_user_account_number']; // Updated to match input name
    $amount = floatval($_POST['deduct_balance']); // Changed to match input name
    $busNumber = isset($_SESSION['bus_number']) ? $_SESSION['bus_number'] : null;
    $conductorId = isset($_SESSION['driver_account_number']) ? $_SESSION['driver_account_number'] : null;

    // Check if the amount is valid and less than or equal to current balance
    if ($amount <= 0) {
        echo json_encode(['error' => 'Invalid amount.']);
        exit;
    }

    // Query to get current balance and user ID
    $query = "SELECT id, balance, points FROM useracc WHERE account_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $account_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($user['balance'] >= $amount) {
            $new_balance = $user['balance'] - $amount;

            // Calculate points to deduct
            $pointsToDeduct = floor($amount * 0.05); // Deduct 5 points for every 100 deducted
            $new_points = $user['points'] - $pointsToDeduct;

            // Ensure points do not go negative
            if ($new_points < 0) {
                $new_points = 0; // Set to 0 if it goes negative
            }

            // Update the user's balance and points
            $updateQuery = "UPDATE useracc SET balance = ?, points = ? WHERE account_number = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("dis", $new_balance, $new_points, $account_number);

            if ($updateStmt->execute()) {
                $logQuery = "INSERT INTO transactions (user_id, account_number, amount, transaction_type,bus_number, conductor_id) VALUES (?, ?, ?, 'Deduct',?,?)";
                $logStmt = $conn->prepare($logQuery);
                $logStmt->bind_param("isdss", $user['id'], $account_number, $amount, $busNumber, $conductorId);
                $logStmt->execute();

                echo json_encode(['success' => 'Balance deducted successfully. New balance is ₱' . number_format($new_balance, 2)]);
            } else {
                echo json_encode(['error' => 'Failed to update balance and points: ' . $updateStmt->error]);
            }
        } else {
            echo json_encode(['error' => 'Insufficient balance.']);
        }
    } else {
        echo json_encode(['error' => 'User  not found.']);
    }
}
?>