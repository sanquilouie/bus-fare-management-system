<?php
// functions.php
function fetchUserCount($conn) {
    $userCountQuery = "SELECT COUNT(*) as userCount FROM useracc";
    $userCountResult = mysqli_query($conn, $userCountQuery);
    return mysqli_fetch_assoc($userCountResult)['userCount'];
}

function searchUserByAccount($conn, $accountNumber) {
    $accountNumber = mysqli_real_escape_string($conn, $accountNumber);
    $searchQuery = "SELECT * FROM useracc WHERE account_number LIKE '%$accountNumber%'";
    return mysqli_query($conn, $searchQuery);
}

function fetchUserByRFID($conn, $rfidCode) {
    $rfidCode = mysqli_real_escape_string($conn, $rfidCode);
    $userQuery = "SELECT * FROM useracc WHERE rfid_code = '$rfidCode'";
    return mysqli_query($conn, $userQuery);
}
function loadUserBalance($conn, $userAccountNumber, $balanceToLoad) {
    $userAccountNumber = mysqli_real_escape_string($conn, $userAccountNumber);
    
    // Fetch the user ID
    $userQuery = "SELECT id FROM useracc WHERE account_number = '$userAccountNumber'";
    $userResult = mysqli_query($conn, $userQuery);
    
    if ($userRow = mysqli_fetch_assoc($userResult)) {
        $userId = $userRow['id'];

        // Update balance
        $updateBalanceQuery = "UPDATE useracc SET balance = balance + ? WHERE account_number = ?";
        $stmt = $conn->prepare($updateBalanceQuery);
        $stmt->bind_param("ds", $balanceToLoad, $userAccountNumber); // 'd' for double, 's' for string
        
        if ($stmt->execute()) {
            // Insert transaction record
            $insertTransactionQuery = "INSERT INTO transactions (user_id, account_number, amount, transaction_type) VALUES (?, ?, ?, 'Load')";
            $transactionStmt = $conn->prepare($insertTransactionQuery);
            $transactionStmt->bind_param("isd", $userId, $userAccountNumber, $balanceToLoad); // 'i' for integer, 's' for string, 'd' for double
            
            return $transactionStmt->execute();
        }
    }
    return false;

}
?>
