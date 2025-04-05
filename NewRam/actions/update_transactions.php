<?php
include "../includes/connection.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $transactionNumber = $_POST['id'];
    $newAmount = $_POST['amount'];

    // Get the existing transaction details
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ?");
    $stmt->bind_param("i", $transactionNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        // Insert a new transaction instead of updating
        $insertStmt = $conn->prepare("
            INSERT INTO transactions (user_id, account_number, amount, transaction_type, bus_number, conductor_id, transaction_date, status)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
        ");
        $insertStmt->bind_param(
            "issssss",
            $row['user_id'],
            $row['account_number'],
            $newAmount,
            $row['transaction_type'],
            $row['bus_number'],
            $row['conductor_id'],
            $row['status']
        );

        if ($insertStmt->execute()) {
            $newTransactionId = $conn->insert_id;  // Capture the new transaction ID

            // Mark the previous transaction as "edited"
            $updateOld = $conn->prepare("UPDATE transactions SET status = 'edited' WHERE id = ?");
            $updateOld->bind_param("i", $transactionNumber);
            $updateOld->execute();
            $updateOld->close();

            echo json_encode(["success" => true, "new_transaction_id" => $newTransactionId, "new_amount" => $newAmount]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to insert new transaction."]);
        }

        $insertStmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Transaction not found."]);
    }

    $stmt->close();
    $conn->close();
}
?>
