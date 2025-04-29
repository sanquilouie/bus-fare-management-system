<?php
include "../includes/connection.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $transactionNumber = $_POST['id'];
    $newAmount = $_POST['amount'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Step 1: Get the existing transaction
        $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ?");
        $stmt->bind_param("i", $transactionNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            throw new Exception("Transaction not found.");
        }

        $row = $result->fetch_assoc();
        $oldAmount = $row['amount'];
        $userId = $row['account_number'];

        // Step 2: Subtract old amount from user's balance
        $updateOldBalance = $conn->prepare("UPDATE useracc SET balance = balance - ? WHERE account_number = ?");
        $updateOldBalance->bind_param("di", $oldAmount, $userId);
        $updateOldBalance->execute();

        // Step 3: Insert new transaction
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

        if (!$insertStmt->execute()) {
            throw new Exception("Failed to insert new transaction.");
        }

        $newTransactionId = $conn->insert_id;

        // Step 4: Add new amount to balance
        $updateNewBalance = $conn->prepare("UPDATE useracc SET balance = balance + ? WHERE account_number = ?");
        $updateNewBalance->bind_param("di", $newAmount, $userId);
        $updateNewBalance->execute();

        // Step 5: Mark original transaction as "edited"
        $updateOld = $conn->prepare("UPDATE transactions SET status = 'edited' WHERE id = ?");
        $updateOld->bind_param("i", $transactionNumber);
        $updateOld->execute();

        // Commit all steps
        $conn->commit();

        echo json_encode([
            "success" => true,
            "new_transaction_id" => $newTransactionId,
            "new_amount" => $newAmount
        ]);

        // Cleanup
        $stmt->close();
        $updateOldBalance->close();
        $insertStmt->close();
        $updateNewBalance->close();
        $updateOld->close();

    } catch (Exception $e) {
        $conn->rollback(); // Revert changes on error
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }

    $conn->close();
}
?>
