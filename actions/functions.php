<?php
// functions.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
function fetchUserCount($conn)
{
    $userCountQuery = "SELECT COUNT(*) as userCount FROM useracc";
    $userCountResult = mysqli_query($conn, $userCountQuery);
    return mysqli_fetch_assoc($userCountResult)['userCount'];
}

function searchUserByAccount($conn, $accountNumber)
{
    $accountNumber = mysqli_real_escape_string($conn, $accountNumber);
    $searchQuery = "SELECT * FROM useracc WHERE account_number LIKE '%$accountNumber%'";
    return mysqli_query($conn, $searchQuery);
}

function fetchUserByRFID($conn, $rfidCode)
{
    $rfidCode = mysqli_real_escape_string($conn, $rfidCode);
    $userQuery = "SELECT * FROM useracc WHERE rfid_code = '$rfidCode'";
    return mysqli_query($conn, $userQuery);
}


function convertPointsToPesos($conn, $userAccountNumber, $pointsToConvert)
{
    $userAccountNumber = mysqli_real_escape_string($conn, $userAccountNumber);

    // Fetch the user's points
    $userQuery = "SELECT points FROM useracc WHERE account_number = '$userAccountNumber'";
    $userResult = mysqli_query($conn, $userQuery);

    if ($userRow = mysqli_fetch_assoc($userResult)) {
        $currentPoints = $userRow['points'];

        // Ensure the user has enough points
        if ($currentPoints >= $pointsToConvert) {
            // Calculate pesos from points (10 points = 0.10 pesos)
            $pesosConverted = $pointsToConvert / 10.0;

            // Update points and balance
            $updatePointsQuery = "UPDATE useracc SET points = points - ?, balance = balance + ? WHERE account_number = ?";
            $stmt = $conn->prepare($updatePointsQuery);
            $stmt->bind_param("ids", $pointsToConvert, $pesosConverted, $userAccountNumber);

            if ($stmt->execute()) {
                // Optionally, you could log this conversion as a transaction
                // Insert conversion transaction record
                $userQuery = "SELECT id FROM useracc WHERE account_number = '$userAccountNumber'";
                $userResult = mysqli_query($conn, $userQuery);
                $userRow = mysqli_fetch_assoc($userResult);
                $userId = $userRow['id'];

                $insertConversionTransactionQuery = "INSERT INTO transactions (user_id, account_number, amount, transaction_type) VALUES (?, ?, ?, 'Convert Points to Pesos')";
                $conversionTransactionStmt = $conn->prepare($insertConversionTransactionQuery);
                $conversionTransactionStmt->bind_param("isd", $userId, $userAccountNumber, $pesosConverted);
                $conversionTransactionStmt->execute();

                return true; // Conversion successful
            }
        }
    }
    return false; // Not enough points or user not found
}
function loadUserBalance($conn, $userAccountNumber, $balanceToLoad)
{
    // Fetch session variables for bus_number and conductor_id
    session_start();
    $busNumber = isset($_SESSION['bus_number']) ? $_SESSION['bus_number'] : null;
    $conductorId = isset($_SESSION['driver_account_number']) ? $_SESSION['driver_account_number'] : null;

    // Sanitize inputs
    $userAccountNumber = mysqli_real_escape_string($conn, $userAccountNumber);

    // Fetch the user ID
    $userQuery = "SELECT id FROM useracc WHERE account_number = '$userAccountNumber'";
    $userResult = mysqli_query($conn, $userQuery);

    if ($userRow = mysqli_fetch_assoc($userResult)) {
        $userId = $userRow['id'];

        // Calculate points earned (1 peso = 10 points)
        $pointsEarned = $balanceToLoad * 0.05;

        // Update user balance
        $updateBalanceQuery = "UPDATE useracc SET balance = balance + ? WHERE account_number = ?";
        $stmt = $conn->prepare($updateBalanceQuery);
        $stmt->bind_param("ds", $balanceToLoad, $userAccountNumber);

        if ($stmt->execute()) {
            // Update points
            $updatePointsQuery = "UPDATE useracc SET points = points + ? WHERE account_number = ?";
            $pointsStmt = $conn->prepare($updatePointsQuery);
            $pointsStmt->bind_param("is", $pointsEarned, $userAccountNumber);

            // Insert transaction record
            $insertTransactionQuery = "INSERT INTO transactions (user_id, account_number, amount, transaction_type, bus_number, conductor_id) 
                                       VALUES (?, ?, ?, 'Load', ?, ?)";
            $transactionStmt = $conn->prepare($insertTransactionQuery);
            $transactionStmt->bind_param("isdss", $userId, $userAccountNumber, $balanceToLoad, $busNumber, $conductorId);

            return $pointsStmt->execute() && $transactionStmt->execute();
        }
    }
    return false;
}
?>