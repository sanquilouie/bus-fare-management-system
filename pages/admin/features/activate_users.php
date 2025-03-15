<?php
session_start();
require '../../../libraries/PHPMailer/src/PHPMailer.php';
require '../../../libraries/PHPMailer/src/SMTP.php';
require '../../../libraries/PHPMailer/src/Exception.php';

// Use PHPMailer namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
ob_start(); // Start output buffering
include '../../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../../index.php");
    exit();
}

// Fetch users for activation
$inactiveUsersQuery = "SELECT * FROM useracc WHERE is_activated = 0 ORDER BY created_at DESC";
$inactiveUsersResult = mysqli_query($conn, $inactiveUsersQuery);

// Handle activation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $account_number = $_POST['account_number'] ?? null;

    if ($user_id) {
        // If the user already has an account number, activate the user directly
        if ($account_number) {
            // Activate user with the given account number
            $activateQuery = "UPDATE useracc SET is_activated = 1, account_number = ? WHERE id = ?";
            $stmt = $conn->prepare($activateQuery);
            $stmt->bind_param("si", $account_number, $user_id);

            if ($stmt->execute()) {
                // Sending Email using PHPMailer
                sendActivationEmail($user_id, $account_number); // Send email after assigning account number
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Error activating user.'];
            }
            $stmt->close();
        } else {
            // If no account number provided, just activate the user directly
            $activateQuery = "UPDATE useracc SET is_activated = 1 WHERE id = ?";
            $stmt = $conn->prepare($activateQuery);
            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                $_SESSION['message'] = ['type' => 'success', 'text' => 'User activated without email.'];
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Error activating user.'];
            }
            $stmt->close();
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'User ID is missing.'];
    }

    header("Location: activate_users.php"); // Redirect back to the activation page
    exit;
}

// Function to send activation email
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

    // Generate a default password
    $password = 'REDACTED_LEGACY_PASSWORD';

    // Sending Email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'portfolio@example.invalid';  // Replace with your email
        $mail->Password = 'REDACTED_SMTP_PASSWORD';  // Replace with your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('portfolio@example.invalid', 'Ramstar Bus Transportation');
        $mail->addAddress($email, $firstname . ' ' . $lastname);
        $mail->isHTML(true);
        $mail->Subject = 'Registration Successful';
        $mail->Body = "
            <p>Dear $firstname,</p>
            <p>Your account has been successfully activated!.</p>
            <p>Login to ramstarbus.com</p>
            <p><strong>Account Number:</strong> $account_number<br>
            <strong>Password:</strong>$password</p>
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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activate Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../../assets/css/sidebars.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Use full version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>
    <?php
        include '../../../includes/topbar.php';
        include '../../../includes/sidebar2.php';
        include '../../../includes/footer.php';
    ?>
    <div class="container mt-5">
        <h3>Activate Users</h3>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Firstname</th>
                        <th>Lastname</th>
                        <th>Account Number</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($inactiveUsersResult)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                            <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                            <td>
                                <?php if (!$row['account_number']): ?>
                                    <form method="POST" action="activate_users.php">
                                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                        <input type="text" name="account_number" placeholder="Enter Account Number" required
                                            class="form-control">
                                        <button type="submit" class="btn btn-success mt-2">Activate</button>
                                    </form>
                                <?php else: ?>
                                    <?php echo $row['account_number']; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['account_number']): ?>
                                    <form method="POST" action="activate_users.php">
                                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-success">Activate</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        <?php if (isset($_SESSION['message'])): ?>
            const message = <?php echo json_encode($_SESSION['message']); ?>;
            Swal.fire({
                icon: message.type === 'success' ? 'success' : 'error',
                title: message.type === 'success' ? 'Success' : 'Error',
                text: message.text,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
    });
</script>

</body>
</html>

<?php
ob_end_flush(); // End output buffering
?>