<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require '../../../../libraries/PHPMailer/src/PHPMailer.php';
require '../../../../libraries/PHPMailer/src/SMTP.php';
require '../../../../libraries/PHPMailer/src/Exception.php';

// Use PHPMailer namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
ob_start(); // Start output buffering
include '../../../../includes/connection.php';

$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Ensure the page is at least 1
$offset = ($page - 1) * $limit;

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../../../index.php");
    exit();
}

// Fetch users for activation
$inactiveUsersQuery = "SELECT * FROM useracc WHERE is_activated = 0 ORDER BY created_at DESC";
$inactiveUsersResult = mysqli_query($conn, $inactiveUsersQuery);
$totalUsersRow = mysqli_fetch_assoc($inactiveUsersResult);
$totalUsers = $totalUsersRow['total'];

// Calculate total pages
$totalPages = ceil($totalUsers / $limit);

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
            
                echo json_encode(["success" => true]); // ✅ Ensure JSON output
            } else {
                echo json_encode(["success" => false, "message" => "Error activating user."]);
            }
            
            $stmt->close();
        } else {
            // If no account number provided, just activate the user directly
            $activateQuery = "UPDATE useracc SET is_activated = 1 WHERE id = ?";
            $stmt = $conn->prepare($activateQuery);
            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "message" => "Error disabling user."]);
            }
            $stmt->close();
        }
    } else {
        echo json_encode(["success" => false, "message" => "User ID is missing."]);
    }
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
    <link rel="stylesheet" href="../../../../assets/css/sidebars.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Use full version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>
    <?php
        include '../../../../includes/topbar.php';
        include '../../../../includes/superadmin_sidebar.php';
        include '../../../../includes/footer.php';
    ?>
    <div id="main-content" class="container-fluid mt-5">
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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody"></tbody>
                    </table>
                </div>
                <nav>
                    <ul class="pagination" id="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</body>
<script>
    $(document).ready(function () {
    function loadUsers(page = 1) {
        $.ajax({
            url: '../../../../actions/fetch_enable_users.php',
            type: 'GET',
            data: { page: page },
            dataType: 'json',
            success: function (response) {
                let users = response.users;
                let totalPages = response.totalPages;
                let currentPage = response.currentPage;
                let tableBody = $("#userTableBody");
                let pagination = $("#pagination");

                // Clear existing data
                tableBody.empty();
                pagination.empty();

                // Populate the user table
                users.forEach(user => {
                    tableBody.append(`
                        <tr>
                            <td>${user.id}</td>
                            <td>${user.firstname}</td>
                            <td>${user.lastname}</td>
                            <td>${user.account_number}</td>
                            <td>
                                <button type="button" class="btn btn-success enable-user" data-user-id="${user.id}">
                                    Activate
                                </button>
                            </td>
                        </tr>
                    `);
                });
                // Previous button
                if (currentPage > 1) {
                    pagination.append(`
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                        </li>
                    `);
                }

                // Numbered page links
                for (let i = 1; i <= totalPages; i++) {
                    pagination.append(`
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `);
                }

                // Next button
                if (currentPage < totalPages) {
                    pagination.append(`
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                        </li>
                    `);
                }
            }
        });
    }

    // Handle pagination click
    $(document).on('click', '.page-link', function (e) {
        e.preventDefault();
        let page = $(this).data('page');
        loadUsers(page);
    });

    // Load the first page initially
    loadUsers();
});
    
$(document).on("click", ".enable-user", function () {
    let userId = $(this).data("user-id");

    Swal.fire({
        title: "Are you sure?",
        text: "This will enable the user!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, enable!",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("activate_users.php", { user_id: userId }, function (response) {
            try {
                let result = JSON.parse(response);
                if (result.success) {
                    Swal.fire("Enabled!", "The user has been enabled.", "success").then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire("Error!", result.message, "error");
                }
            } catch (error) {
                console.error("Invalid JSON response:", response);
                Swal.fire("Error!", "Unexpected response from server.", "error");
            }
        });

        }
    });
});
</script>
</html>

<?php
ob_end_flush(); // End output buffering
?>