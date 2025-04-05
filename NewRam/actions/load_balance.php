<?php
session_start();
include "../includes/connection.php";
//include 'functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function loadUserBalance($conn, $userAccountNumber, $balanceToLoad, $rfid)
{

    //Fetch Conductor ID
    $query = "SELECT id FROM useracc WHERE account_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $_SESSION['account_number']);
    $stmt->execute();
    $result = $stmt->get_result();
    $id = $result->fetch_assoc()['id'] ?? null;

    // Fetch session variables for bus_number and conductor_id
    $busNumber = 'Cashier';
    $conductorId = isset($_SESSION['account_number']) ? $_SESSION['account_number'] : null;

    // Sanitize inputs
    $userAccountNumber = mysqli_real_escape_string($conn, $userAccountNumber);
    $balanceToLoad = floatval($balanceToLoad);
    $rfid = mysqli_real_escape_string($conn, $rfid);

    // Check if the user account exists
    $query = "SELECT * FROM useracc WHERE account_number = '$userAccountNumber'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Update the user's balance
        $updateQuery = "UPDATE useracc SET balance = balance + $balanceToLoad WHERE account_number = '$userAccountNumber'";
        if (mysqli_query($conn, $updateQuery)) {
            // Log the transaction
            $logQuery = "INSERT INTO transactions (user_id, account_number, amount, transaction_type, bus_number, conductor_id) VALUES ($id, '$userAccountNumber', $balanceToLoad, 'load', '$busNumber', '$conductorId')";
            mysqli_query($conn, $logQuery);
            return ['success' => '₱' . number_format($balanceToLoad, 2) . ' loaded successfully.'];
        } else {
            return ['error' => 'Failed to update balance.'];
        }
    } else {
        return ['error' => 'User  account not found.'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userAccountNumber = $_POST['user_account_number'];
    $loadAmount = $_POST['loadAmount'];
    $rfid = $_POST['rfid'];

    // Call the function to load the user balance
    $response = loadUserBalance($conn, $userAccountNumber, $loadAmount, $rfid);
    
    header('Content-Type: application/json'); // Set the content type to JSON
    echo json_encode($response);
}
?>