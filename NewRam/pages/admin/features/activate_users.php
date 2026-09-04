<?php
require_once '../../../includes/security.php';
require_once '../../../includes/mailer.php';
require_once '../../../includes/passwords.php';
bfms_require_roles(['Admin', 'Superadmin']);
ob_start(); // Start output buffering
include '../../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../../index.php");
    exit();
}

// Fetch users for activation
$inactiveUsersQuery = "SELECT id, firstname, lastname, account_number
                       FROM useracc WHERE is_activated = 0 AND role = 'User' ORDER BY created_at DESC";
$inactiveUsersResult = mysqli_query($conn, $inactiveUsersQuery);

// Handle activation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    bfms_require_same_origin();
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $account_number = trim((string) ($_POST['account_number'] ?? ''));

    if (!$user_id) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'A valid user ID is required.'];
    } elseif ($account_number === '') {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'An account number is required before activation.'];
    } else {
        $conn->begin_transaction();
        try {
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
            $_SESSION['message'] = ['type' => 'success', 'text' => 'User activated and credentials emailed successfully.'];
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('User activation failed: ' . $e->getMessage());
            $_SESSION['message'] = ['type' => 'error', 'text' => 'User activation was not completed because credentials could not be delivered.'];
        } finally {
            unset($password);
        }
    }

    header("Location: activate_users.php"); // Redirect back to the activation page
    exit;
}

// Function to send activation email
function sendActivationEmail($email, $firstname, $lastname, $account_number, $password)
{
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
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activate Accounts</title>
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
    <script src="/NewRam/assets/js/NFCScanner.js"></script>
</head>

<body>
<?php
    include '../../../includes/topbar.php';
    include '../../../includes/sidebar2.php';
    include '../../../includes/footer.php';
    ?>
    <div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
        <h2>Activate Users</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
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
                            <?php 
                            if (mysqli_num_rows($inactiveUsersResult) > 0) {
                                while ($row = mysqli_fetch_assoc($inactiveUsersResult)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                                        <td>
                                            <?php if (!$row['account_number']): ?>
                                                <!-- If no account number, show "No Account Number" text -->
                                                No Account Number
                                            <?php else: ?>
                                                <?php echo $row['account_number']; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!$row['account_number']): ?>
                                                <!-- If no account number, show a form to enter one -->
                                                <form method="POST" action="activate_users.php">
                                                    <button type="button" class="btn btn-success" onclick="askForAccountNumber(<?php echo $row['id']; ?>)">Activate</button>
                                                </form>
                                            <?php else: ?>
                                                <!-- If there's an account number, show the Activate button -->
                                                <form method="POST" action="activate_users.php">
                                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="account_number" value="<?php echo $row['account_number']; ?>">
                                                    <button type="button" class="btn btn-success activate-btn">Activate</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile;
                            } else {
                                // If no rows are found, display "No Data to display"
                                echo '<tr><td colspan="5" class="text-center">No Data to display</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <script>
    function askForConfirmationAndActivate(event, form) {
        event.preventDefault(); 

        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to activate this user.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, activate!',
            cancelButtonText: 'No, cancel',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const activateButtons = document.querySelectorAll('.activate-btn');

        activateButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                const form = this.closest('form');
                askForConfirmationAndActivate(event, form);
            });
        });
    });


        function askForAccountNumber(userId) {
            Swal.fire({
                title: 'Enter Account Number',
                input: 'text',
                inputLabel: 'Account Number',
                inputPlaceholder: 'Enter account number here',
                showCancelButton: true,
                confirmButtonText: 'Activate',
                cancelButtonText: 'Cancel',
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to enter an account number!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const accountNumber = result.value;

                    const formData = new FormData();
                    formData.append('user_id', userId);
                    formData.append('account_number', accountNumber);

                    fetch('../../../actions/activate_users_noaccountnumber.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('User Activated', 'The user has been activated with the account number.', 'success')
                            .then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message || 'There was an error activating the user.', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Something went wrong with the activation process.', 'error');
                    });
                }
            });
    }
    </script>

</body>

</html>

<?php
ob_end_flush();
?>
