<?php
require_once '../includes/security.php';
bfms_require_roles(['Admin', 'Superadmin']);
bfms_require_same_origin();
require_once '../includes/mailer.php';
require_once '../includes/passwords.php';

// Include your database connection
include "../includes/connection.php";

// Function to log activities
function logActivity($conn, $user_id, $action, $performed_by)
{
    $logQuery = "INSERT INTO activity_logs (user_id, action, performed_by) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($logQuery);
    $stmt->bind_param("iss", $user_id, $action, $performed_by);
    $stmt->execute();
    $stmt->close();
}

header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$requestedAccountNumber = trim((string) ($_POST['account_number'] ?? ''));
if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'A valid user ID is required.']);
    exit;
}

$conn->begin_transaction();
try {
    $userStmt = $conn->prepare(
        'SELECT firstname, lastname, email, account_number
         FROM useracc WHERE id = ? AND is_activated = 0 FOR UPDATE'
    );
    $userStmt->bind_param('i', $user_id);
    $userStmt->execute();
    $userStmt->bind_result($firstname, $lastname, $email, $storedAccountNumber);
    $userFound = $userStmt->fetch();
    $userStmt->close();
    if (!$userFound) {
        throw new RuntimeException('The inactive user record was not found.');
    }

    $account_number = $requestedAccountNumber !== '' ? $requestedAccountNumber : trim((string) $storedAccountNumber);
    if ($account_number === '') {
        throw new RuntimeException('An account number is required before activation.');
    }

    $password = bfms_generate_temporary_password();
    $passwordHash = bfms_hash_password_for_database($conn, $password);
    $credentialStmt = $conn->prepare(
        'UPDATE useracc SET account_number = ?, password = ? WHERE id = ? AND is_activated = 0'
    );
    $credentialStmt->bind_param('ssi', $account_number, $passwordHash, $user_id);
    $credentialStmt->execute();
    if ($credentialStmt->affected_rows !== 1) {
        $credentialStmt->close();
        throw new RuntimeException('Credentials were not stored for the expected user.');
    }
    $credentialStmt->close();

    $mail = bfms_create_mailer();
    $loginUrl = bfms_app_url('auth/login.php');
    $mail->addAddress($email, $firstname . ' ' . $lastname);
    $mail->isHTML(true);
    $mail->Subject = 'Registration Successful';
    $mail->Body = "
        <p>Dear $firstname,</p>
        <p>Your account has been successfully activated.</p>
        <p>Login at $loginUrl</p>
        <p><strong>Account Number:</strong> $account_number<br>
        <strong>Temporary Password:</strong> $password</p>
        <p>Change your password after logging in for security.</p>
        <p>Best regards,<br>RAMSTAR</p>
    ";
    $mail->send();
    unset($mail);

    $activateStmt = $conn->prepare('UPDATE useracc SET is_activated = 1 WHERE id = ? AND is_activated = 0');
    $activateStmt->bind_param('i', $user_id);
    $activateStmt->execute();
    if ($activateStmt->affected_rows !== 1) {
        $activateStmt->close();
        throw new RuntimeException('Activation did not update the expected user.');
    }
    $activateStmt->close();

    logActivity($conn, $user_id, 'Activated with account number', $_SESSION['firstname'] . ' ' . $_SESSION['lastname']);
    $conn->commit();
    unset($password);
    echo json_encode(['status' => 'success', 'message' => 'User activated and credentials emailed successfully.']);
} catch (Throwable $e) {
    $conn->rollback();
    unset($password);
    error_log('User activation failed: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'User activation was not completed because credentials could not be delivered.']);
}
