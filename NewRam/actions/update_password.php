<?php
require_once '../includes/security.php';
require_once '../includes/passwords.php';
bfms_require_authenticated();
bfms_require_same_origin();
include "../includes/connection.php";
$errors = array();

$account_number = $_SESSION['account_number']; // Use session account number to identify the user
$session_email = $_SESSION['email']; // Get the email from session

if (isset($_POST['old_pass']) && isset($_POST['Password']) && isset($_POST['PasswordConf'])) {
    $oldPassword = $_POST['old_pass'];
    $Password = $_POST['Password'];
    $PasswordConf = $_POST['PasswordConf'];

    // Check if the old password matches for the logged-in user using account number and session email
    $stmt = $conn->prepare('SELECT password FROM useracc WHERE account_number = ? AND email = ?');
    $stmt->bind_param('ss', $account_number, $session_email);
    $stmt->execute();
    $check_pwd = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $user_data = (string) ($check_pwd['password'] ?? '');

    // Validate if new passwords match
    if ($Password != $PasswordConf) {
        array_push($errors, "Your New Password and Confirm Password do not match.");
    }

    // Validate password strength
    $uppercase = preg_match('@[A-Z]@', $Password);
    $lowercase = preg_match('@[a-z]@', $Password);
    $number = preg_match('@[0-9]@', $Password);
    $specialChars = preg_match('@[^\w]@', $Password);

    if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($Password) < 8) {
        array_push($errors, "Password should be at least 8 characters long, contain at least one uppercase letter, one number, and one special character.");
    }

    // Check if old password is correct
    $unusedUpgrade = null;
    if (!bfms_verify_password($oldPassword, $user_data, $unusedUpgrade)) {
        array_push($errors, "Old password is incorrect.");
    }

    // If no errors, update the password
    if (count($errors) == 0) {
        $newpass = bfms_hash_password_for_database($conn, $Password);
        $stmt = $conn->prepare('UPDATE useracc SET password = ? WHERE account_number = ? AND email = ?');
        $stmt->bind_param('sss', $newpass, $account_number, $session_email);
        $update = $stmt->execute();
        $stmt->close();

        if ($update) {
            echo json_encode(['status' => 'success', 'message' => 'Password has been updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating password.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => implode(", ", $errors)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
}
?>
