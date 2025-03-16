<?php
include "../includes/connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['load_balance'])) {
    $account_number = $_POST['user_account_number']; // Updated to match input name
    $amount = floatval($_POST['load_balance']); // Changed to match input name

    // Check if the amount is valid
    if ($amount <= 0) {
        echo json_encode(['error' => 'Invalid amount.']);
        exit;
    }

    // Query to get current balance and user ID
    $query = "SELECT id, balance FROM useracc WHERE account_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $account_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $new_balance = $user['balance'] + $amount;

        // Update the user's balance
        $updateQuery = "UPDATE useracc SET balance = ? WHERE account_number = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ds", $new_balance, $account_number);

        if ($updateStmt->execute()) {
            $logQuery = "INSERT INTO transactions (user_id, account_number, amount, transaction_type) VALUES (?, ?, ?, 'Load')";
            $logStmt = $conn->prepare($logQuery);
            $logStmt->bind_param("isd", $user['id'], $account_number, $amount);
            $logStmt->execute();

            echo json_encode(['success' => 'Balance loaded successfully. New balance is ₱' . number_format($new_balance, 2)]);
        } else {
            echo json_encode(['error' => 'Failed to update balance: ' . $updateStmt->error]);
        }
    } else {
        echo json_encode(['error' => 'User not found.']);
    }
}
?>