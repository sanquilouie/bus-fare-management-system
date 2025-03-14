<?php 
session_start();
require '../libraries/PHPMailer/src/PHPMailer.php';
require '../libraries/PHPMailer/src/SMTP.php';
require '../libraries/PHPMailer/src/Exception.php';

// Use PHPMailer namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include your database connection
include "../includes/connection.php";

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../index.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log activities
function logActivity($conn, $user_id, $action, $performed_by)
{
    $logQuery = "INSERT INTO activity_logs (user_id, action, performed_by) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($logQuery);
    $stmt->bind_param("iss", $user_id, $action, $performed_by);
    $stmt->execute();
    $stmt->close();
}

// Activate user action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id']; // Get the user ID from the POST data
    $account_number = isset($_POST['account_number']) ? $_POST['account_number'] : ''; // Get the account number if provided
    $password = 'REDACTED_LEGACY_PASSWORD'; // Set a default password or generate one

    $response = array(); // Initialize response array

    if ($user_id) {
        if (!empty($account_number)) {
            // If an account number is provided, update it along with activation
            $activateQuery = "UPDATE useracc SET is_activated = 1, account_number = ? WHERE id = ?";
            $stmt1 = $conn->prepare($activateQuery);
            if ($stmt1) {
                $stmt1->bind_param("si", $account_number, $user_id);
                if ($stmt1->execute()) {
                    logActivity($conn, $user_id, 'Activated with account number', $_SESSION['firstname'] . ' ' . $_SESSION['lastname']);
                    $response['status'] = 'success';
                    $response['message'] = ' User activated and email has been sent successfully!';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Failed to activate user with account number.';
                }
                $stmt1->close();
            }
        } else {
            // If no account number is provided, just activate the user
            $activateQuery = "UPDATE useracc SET is_activated = 1 WHERE id = ?";
            $stmt2 = $conn->prepare($activateQuery);
            if ($stmt2) {
                $stmt2->bind_param("i", $user_id);
                if ($stmt2->execute()) {
                    logActivity($conn, $user_id, 'Activated without account number', $_SESSION['firstname'] . ' ' . $_SESSION['lastname']);
                    $response['status'] = 'success';
                    $response['message'] = 'User activated successfully!!';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Failed to activate user without account number.';
                }
                $stmt2->close();
            }
        }

        // Fetch user details for email notification
        $userQuery = "SELECT firstname, lastname, email FROM useracc WHERE id = ?";
        $stmt3 = $conn->prepare($userQuery);
        if ($stmt3) {
            $stmt3->bind_param("i", $user_id);
            $stmt3->execute();
            $stmt3->bind_result($firstname, $lastname, $email);
            $stmt3->fetch();
            $stmt3->close();
        }

        // Send email if activated with account number
        if (!empty($account_number)) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'portfolio@example.invalid';
                $mail->Password = 'REDACTED_SMTP_PASSWORD';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('portfolio@example.invalid', 'Ramstar Bus Transportation');
                $mail->addAddress($email, $firstname . ' ' . $lastname);
                $mail->isHTML(true);
                $mail->Subject = 'Registration Successful';
                $mail->Body = "
                    <p>Dear $firstname,</p>
                    <p>Your account has been successfully activated!.</p>
                    <p>Login to ramstarbus.com<p>
                    <p><strong>Account Number:</strong> $account_number<br>
                    <strong>Password:</strong>$password</p>
                    <p>Change your password after logging in for security.</p>
                    <p>Best regards,<br>RAMSTAR</p>
                ";

                $mail->send();
            } catch (Exception $e) {
                error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
        }

    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to activate user.';
    }

    // Return response as JSON
    echo json_encode($response);
}
?>
