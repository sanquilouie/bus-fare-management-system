<?php
require_once '../includes/mailer.php';
require_once '../includes/passwords.php';
require_once '../includes/security.php';

include "../includes/connection.php";
bfms_require_roles(['Admin', 'Superadmin']);
bfms_require_same_origin();

function sendActivationEmail($email, $firstname, $lastname, $account_number, $password)
{
    $mail = bfms_create_mailer();
    $loginUrl = bfms_app_url('auth/login.php');
    $mail->addAddress($email, "$firstname $lastname");
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
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $account_number = trim((string) ($_POST['account_number'] ?? ''));

    header('Content-Type: application/json');

    if ($user_id && $account_number !== '') {
        try {
            $conn->begin_transaction();
            // Step 1: Check if account_number exists for another user
            $checkQuery = "SELECT id FROM useracc WHERE account_number = ? AND id != ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("si", $account_number, $user_id);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Account number already exists.']);
                $checkStmt->close();
                exit;
            }
            $checkStmt->close();

            $userStmt = $conn->prepare(
                "SELECT firstname, lastname, email FROM useracc
                 WHERE id = ? AND is_activated = 0 AND role = 'User' FOR UPDATE"
            );
            $userStmt->bind_param('i', $user_id);
            $userStmt->execute();
            $userStmt->bind_result($firstname, $lastname, $email);
            $userFound = $userStmt->fetch();
            $userStmt->close();
            if (!$userFound) {
                throw new RuntimeException('The inactive user record was not found.');
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

            sendActivationEmail($email, $firstname, $lastname, $account_number, $password);

            $activateStmt = $conn->prepare('UPDATE useracc SET is_activated = 1 WHERE id = ? AND is_activated = 0');
            $activateStmt->bind_param('i', $user_id);
            $activateStmt->execute();
            if ($activateStmt->affected_rows !== 1) {
                $activateStmt->close();
                throw new RuntimeException('Activation did not update the expected user.');
            }
            $activateStmt->close();

            $conn->commit();
            unset($password);
            echo json_encode(['success' => true, 'message' => 'User activated and credentials emailed successfully.']);
        } catch (Throwable $e) {
            $conn->rollback();
            unset($password);
            error_log('User activation failed: ' . $e->getMessage());
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'User activation could not be completed.']);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'User ID or Account Number is missing.']);
    }
}

