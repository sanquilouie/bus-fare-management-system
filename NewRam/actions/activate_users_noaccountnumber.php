<?php
require '../libraries/PHPMailer/src/PHPMailer.php';
require '../libraries/PHPMailer/src/SMTP.php';
require '../libraries/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include "../includes/connection.php";
session_start();

function sendActivationEmail($user_id, $account_number)
{
    global $conn;

    // Fetch user details
    $userQuery = "SELECT firstname, lastname, email FROM useracc WHERE id = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($firstname, $lastname, $email);
    $stmt->fetch();
    $stmt->close();

    // Default password (optional: you may want to generate or fetch this dynamically)
    $password = 'REDACTED_LEGACY_PASSWORD';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'portfolio@example.invalid';
        $mail->Password = 'REDACTED_SMTP_PASSWORD'; // Consider securing this using env/config
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('portfolio@example.invalid', 'Ramstar Zaragoza');
        $mail->addAddress($email, "$firstname $lastname");
        $mail->isHTML(true);
        $mail->Subject = 'Registration Successful';
        $mail->Body = "
            <p>Dear $firstname,</p>
            <p>Your account has been successfully activated!</p>
            <p>Login to ramstarzaragosa.site</p>
            <p><strong>Account Number:</strong> $account_number<br>
            <strong>Password:</strong> $password</p>
            <p>Change your password after logging in for security.</p>
            <p>Best regards,<br>RAMSTAR</p>
        ";

        $mail->send();
        $_SESSION['message'] = ['type' => 'success', 'text' => 'User activated and email sent successfully.'];
    } catch (Exception $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'User activated, but email could not be sent.'];
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $account_number = $_POST['account_number'] ?? null;

    header('Content-Type: application/json');

    if ($user_id && $account_number) {
        try {
            $activateQuery = "UPDATE useracc SET is_activated = 1, account_number = ? WHERE id = ?";
            $stmt = $conn->prepare($activateQuery);

            if ($stmt === false) {
                throw new Exception("Error preparing statement: " . $conn->error);
            }

            $stmt->bind_param("si", $account_number, $user_id);

            if ($stmt->execute()) {
                $stmt->close();

                // Send the activation email after successful update
                sendActivationEmail($user_id, $account_number);

                echo json_encode(['success' => true, 'message' => 'User activated and email sent successfully.']);
            } else {
                throw new Exception("Error executing statement: " . $stmt->error);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User ID or Account Number is missing.']);
    }
}
