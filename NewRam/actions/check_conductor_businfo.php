<?php
session_start();
include "../includes/connection.php";

header('Content-Type: application/json');

if (!isset($_SESSION['account_number'])) {
    echo json_encode(['success' => false, 'error' => 'No account number in session.']);
    exit;
}

$accountNumber = $_SESSION['account_number'];

// Prepared statement to check existence in businfo
$stmt = $conn->prepare("SELECT * FROM businfo WHERE conductorID = ?");
$stmt->bind_param("s", $accountNumber);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'It seems that this account\'s trips has already been remitted. Please re-login.']);
}
?>
