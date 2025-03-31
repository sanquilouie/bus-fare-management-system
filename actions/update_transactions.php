<?php
include "../includes/connection.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $transactionNumber = $_POST['id'];
    $newAmount = $_POST['amount'];

    $stmt = $conn->prepare("UPDATE transactions SET amount = ? WHERE id = ?");
    $stmt->bind_param("di", $newAmount, $transactionNumber);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "new_amount" => $newAmount]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update amount."]);
    }

    $stmt->close();
    $conn->close();
}
?>
