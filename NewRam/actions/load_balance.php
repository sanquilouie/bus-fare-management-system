<?php
session_start();
include "../includes/connection.php";
//include 'functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function loadUserBalance($conn, $userAccountNumber, $balanceToLoad, $rfid)
{
    require_once '../includes/sms_helper.php'; // make sure this is included if not already

    // Set timezone
    date_default_timezone_set('Asia/Manila');

    // Fetch Conductor ID
    $query = "SELECT id FROM useracc WHERE account_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $_SESSION['account_number']);
    $stmt->execute();
    $result = $stmt->get_result();
    $id = $result->fetch_assoc()['id'] ?? null;

    // Fetch session variables
    $busNumber = $_SESSION['bus_number'] ?? 'Unknown Bus Number';
    $conductorId = $_SESSION['account_number'] ?? null;

    // Sanitize
    $userAccountNumber = mysqli_real_escape_string($conn, $userAccountNumber);
    $balanceToLoad = floatval($balanceToLoad);
    $rfid = mysqli_real_escape_string($conn, $rfid);

    // Check if account exists
    $query = "SELECT * FROM useracc WHERE account_number = '$userAccountNumber' AND is_activated = 1 AND role = 'User'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Update balance
        $updateQuery = "UPDATE useracc SET balance = balance + $balanceToLoad WHERE account_number = '$userAccountNumber'";
        if (mysqli_query($conn, $updateQuery)) {
            // Log the transaction
            $logQuery = "INSERT INTO transactions (user_id, account_number, amount, transaction_type, bus_number, conductor_id) VALUES ($id, '$userAccountNumber', $balanceToLoad, 'load', '$busNumber', '$conductorId')";
            mysqli_query($conn, $logQuery);

            // Fetch contact number
            $phoneQuery = $conn->prepare("SELECT contactnumber, balance FROM useracc WHERE account_number = ?");
            $phoneQuery->bind_param("s", $userAccountNumber);
            $phoneQuery->execute();
            $phoneResult = $phoneQuery->get_result();

            if ($phoneResult && $phoneResult->num_rows > 0) {
                $row = $phoneResult->fetch_assoc();
                $phoneNumber = $row['contactnumber'];
                $newBalance = $row['balance'];

                // Compose and send SMS
                $smsMessage = "₱" . number_format($balanceToLoad, 2) . " loaded to your account on " . date('Y-m-d h:i A') . ". New balance: ₱" . number_format($newBalance, 2) . ".";
                sendSMS($phoneNumber, $smsMessage);
            }

            return ['success' => '₱' . number_format($balanceToLoad, 2) . ' loaded successfully.'];
        } else {
            return ['error' => 'Failed to update balance.'];
        }
    } else {
        return ['error' => 'User account not found.'];
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