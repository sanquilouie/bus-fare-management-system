<?php
// search_user.php
include '../../config/connection.php';
include 'functions.php';

// Set the content type to JSON
header('Content-Type: application/json');

$searchResult = null;
$searchError = null;

if (isset($_POST['search_account'])) {
    $accountNumber = $_POST['account_number'];

    if (!empty($accountNumber)) {
        $searchResult = searchUserByAccount($conn, $accountNumber);
        
        if ($searchResult && mysqli_num_rows($searchResult) > 0) {
            $user = mysqli_fetch_assoc($searchResult);
            // Concatenate the name from firstname, middlename, and lastname
            $fullName = trim($user['firstname'] . ' ' . $user['middlename'] . ' ' . $user['lastname']);
            echo json_encode([
                'success' => "User found: " . htmlspecialchars($fullName),
                'user' => $user
            ]);
        } else {
            // No user found
            $searchError = "No user found with Account Number: " . htmlspecialchars($accountNumber);
        }
    } else {
        // No account number provided
        $searchError = "Please enter an account number.";
    }
}

// If there's an error, send the error message as JSON
if (isset($searchError)) {
    echo json_encode(['error' => $searchError]);
}

// End the script to ensure no additional output is sent
exit;
?>
