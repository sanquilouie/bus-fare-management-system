<?php
session_start();
include "../includes/connection.php";
$errors = array();

// Make sure the user is logged in and has a session for account number and email
if (!isset($_SESSION['account_number']) || !isset($_SESSION['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$account_number = $_SESSION['account_number']; // Use session account number to identify the user
$session_email = $_SESSION['email']; // Get the email from session

if (isset($_POST['old_pass']) && isset($_POST['Password']) && isset($_POST['PasswordConf'])) {
    $old_pass = mysqli_real_escape_string($conn, md5($_POST['old_pass']));
    $Password = mysqli_real_escape_string($conn, $_POST['Password']);
    $PasswordConf = mysqli_real_escape_string($conn, $_POST['PasswordConf']);

    // Check if the old password matches for the logged-in user using account number and session email
    $sql = mysqli_query($conn, "SELECT password FROM useracc WHERE account_number = '{$account_number}' AND email = '{$session_email}'");
    $check_pwd = mysqli_fetch_array($sql);
    $user_data = $check_pwd['password'];

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
    if ($old_pass != $user_data) {
        array_push($errors, "Old password is incorrect.");
    }

    // If no errors, update the password
    if (count($errors) == 0) {
        $newpass = md5($Password);
        $update = mysqli_query($conn, "UPDATE useracc SET password='{$newpass}' WHERE account_number='{$account_number}' AND email='{$session_email}'");

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