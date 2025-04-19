<?php
require '../libraries/PHPMailer/src/PHPMailer.php';
require '../libraries/PHPMailer/src/SMTP.php';
require '../libraries/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Include your database connection
include "../includes/connection.php";

// Function to validate suffix
function getSuffix($suffix)
{
    $valid_suffixes = ['Jr', 'Sr', 'III', 'IV', 'V'];
    return in_array($suffix, $valid_suffixes) ? $suffix : '';
}

// Initialize flags
$registration_successful = false;
$error_message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $account_number = htmlspecialchars($_POST['account_number']);
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $middlename = htmlspecialchars($_POST['middlename']);
    $suffix = getSuffix(htmlspecialchars($_POST['suffix']));
    $birthday = htmlspecialchars($_POST['birthday']);

    $gender = htmlspecialchars($_POST['gender']);
    $email = htmlspecialchars($_POST['email']);
    $contactnumber = preg_replace('/\D/', '', $_POST['contactnumber']);
    $province_id = intval($_POST['province']);
    $municipality_id = intval($_POST['municipality']);
    $barangay_id = intval($_POST['barangay']);
    $address = htmlspecialchars($_POST['address']);
    $account_number = htmlspecialchars($_POST['account_number']);
    $role = 'User'; // Default role
    // Generate a random password
    $password = 'REDACTED_LEGACY_PASSWORD';

    // Calculate the age
    $birthday_date = new DateTime($birthday);
    $today = new DateTime();
    $age = $today->diff($birthday_date)->y;

    $hashed_password = md5($password);  // Use password_hash for security
    $balance = 0.00; // Default balance

    // Only proceed if no errors encountered
    if (empty($error_message)) {
        // Database insertion
        try {
            $stmt = $conn->prepare("
                INSERT INTO useracc 
                (account_number, firstname, lastname, middlename, suffix, birthday, age, gender, email, contactnumber, province, municipality, barangay, address, password, balance, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
    
            $stmt->bind_param(
                "sssssssisiiisssds",
                $account_number,
                $firstname,
                $lastname,
                $middlename,
                $suffix,
                $birthday,
                $age,
                $gender,
                $email,
                $contactnumber,
                $province_id,
                $municipality_id,
                $barangay_id,
                $address,
                $hashed_password,
                $balance,
                $role
            );
    
            if ($stmt->execute()) {
                $registration_successful = true;
    
                // Send confirmation email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'portfolio@example.invalid';
                    $mail->Password = 'REDACTED_SMTP_PASSWORD'; // App password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
    
                    $mail->setFrom('portfolio@example.invalid', 'Ramstar Bus Transportation');
                    $mail->addAddress($email, $firstname . ' ' . $lastname);
                    $mail->isHTML(true);
                    $mail->Subject = 'Registration Received';
                    $mail->Body = "
                        <p>Hi $firstname,</p>
                        <p>Thank you for registering with Ramstar Bus Transportation.</p>
                        <p>Your account has been successfully activated. You can now log in and start using your account.</p>
                        <p>Your default password is: ramstar</p>
                        <p>Best regards,<br>Ramstar Bus Transportation</p>
                    ";
    
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                }
            } else {
                $error_message = "Database error: " . $stmt->error;
            }
    
            $stmt->close();
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    
        $conn->close();
    }
     
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <!-- Include SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>


<body>
    
    <?php if ($registration_successful): ?>
        <script>
            Swal.fire({
                title: 'Registration Successful!',
                text: 'Success registering the account.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '/NewRam/pages/admin/register.php';
            });
        </script>
    <?php elseif ($error_message): ?>
        <script>
            Swal.fire({
                title: 'Error!',
                text: '<?php echo htmlspecialchars($error_message); ?>',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>
    <?php endif; ?>
    

</body>
</html>
