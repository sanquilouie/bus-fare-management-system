<?php
require_once '../includes/security.php';
bfms_require_roles(['Cashier', 'Conductor', 'Admin', 'Superadmin']);
bfms_require_same_origin();
include "../includes/connection.php";
//include 'functions.php';

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

    if (!is_finite($balanceToLoad) || $balanceToLoad <= 0) {
        return ['error' => 'Load amount must be greater than zero.'];
    }

    // Check if account exists
    $accountStmt = $conn->prepare(
        "SELECT 1 FROM useracc WHERE account_number = ? AND is_activated = 1 AND role = 'User' LIMIT 1"
    );
    $accountStmt->bind_param('s', $userAccountNumber);
    $accountStmt->execute();
    $result = $accountStmt->get_result();

    if (mysqli_num_rows($result) > 0) {
        // Update balance
        $updateStmt = $conn->prepare('UPDATE useracc SET balance = balance + ? WHERE account_number = ?');
        $updateStmt->bind_param('ds', $balanceToLoad, $userAccountNumber);
        if ($updateStmt->execute()) {
            // Log the transaction
            $logStmt = $conn->prepare(
                "INSERT INTO transactions (user_id, account_number, amount, transaction_type, bus_number, conductor_id)
                 VALUES (?, ?, ?, 'load', ?, ?)"
            );
            $logStmt->bind_param('isdss', $id, $userAccountNumber, $balanceToLoad, $busNumber, $conductorId);
            $logStmt->execute();
            $logStmt->close();

            $transactionId = mysqli_insert_id($conn);
            $transactionIdFormatted = date('ymd') . str_pad($transactionId, 3, '0', STR_PAD_LEFT);

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

            echo json_encode([
                'success' => '₱' . number_format($balanceToLoad, 2) . ' loaded successfully.',
                'transactionId' => $transactionIdFormatted,
                'date' => date('Y-m-d'),
                'time' => date('H:i:s'),
                'newBalance' => number_format($newBalance, 2)
            ]);
            exit;
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
