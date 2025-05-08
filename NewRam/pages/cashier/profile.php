<?php
ob_start();
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../../libraries/PHPMailer/src/PHPMailer.php';
require '../../libraries/PHPMailer/src/SMTP.php';
require '../../libraries/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../../includes/connection.php';


// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form values and sanitize inputs
    $employeeType = $_POST['employeeType'];
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $phone = $_POST['contactnumber'];
    $birthday = $_POST['dob'];
    $province = $_POST['province'];
    $municipality = $_POST['municipality'];
    $barangay = $_POST['barangay'];
    $address = $_POST['address'];
    $gender = $_POST['gender'];
    $accountNumber = $_POST['employeeNumber'];


    if (isset($_POST['update_id']) && isset($_POST['new_status'])) {
        $update_id = mysqli_real_escape_string($conn, $_POST['update_id']);
        $new_status = (int) $_POST['new_status'];

        $update_sql = "UPDATE useracc SET is_activated = $new_status WHERE account_number = '$update_id'";
        mysqli_query($conn, $update_sql);

        // Optional: redirect to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
        exit;
    }

    // Calculate age
    $age = date_diff(date_create($birthday), date_create('today'))->y;

    // Password generation
    $pass = 'ramstarbus123';
    $password = md5($pass); // Consider using password_hash() for better security

    // Set default role and activation status
    $role = $employeeType;
    $activated = 1;

    // Prepare the SQL query
    $query = "INSERT INTO useracc (
        account_number, firstname, middlename, lastname, email, contactnumber, birthday, age, gender,
        province, municipality, barangay, address, password, role, is_activated
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    if ($stmt = $conn->prepare($query)) {
        // Bind parameters
        $stmt->bind_param("sssssssssssssssi", $accountNumber, $firstName, $middleName, $lastName, $email, 
            $phone, $birthday, $age, $gender, $province, $municipality, $barangay, $address, $password, $role, $activated);

        // Execute the query
        if ($stmt->execute()) {

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
                    $mail->addAddress($email, $firstName . ' ' . $lastName);
                    $mail->isHTML(true);
                    $mail->Subject = 'Registration Received';
                    $mail->Body = "
                        <p>Dear $firstName,</p>
                        <p>Congratulations! You are officially hired for the position of $role at our company.</p>
                        <p>We look forward to wor;king with you and are excited to have you on the team.</p>
                        <p><strong>Account Number:</strong>  $accountNumber
                        <br><strong>Default Password:</strong> ramstarbus123</p>
                        <p>To get started, please log in to our company portal using the link below:</p>
                        <p>https://ramstarzaragosa.site/</p>
                        <p>Best regards,
                        <br>Ramstar Bus Transportation</p>
                    ";
    
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                }

            $_SESSION['success'] = 'Employee registered successfully!';
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $_SESSION['error'] = 'Error during registration!';
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = 'Error preparing the SQL query!';
    }

    // Close connection
    $conn->close();
}


$employeeNumber = $_SESSION['account_number'];

    // Assuming $conn is your MySQLi connection
    $stmt = $conn->prepare("SELECT * FROM useracc WHERE account_number = ?");
    $stmt->bind_param("s", $employeeNumber); // 's' = string
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); // This $user array is what you use in the form
    } else {
        echo "User not found.";
    }

    $stmt->close();

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../assets/css/sidebars.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Use full version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
 
    
    <style>
      .country-code {
            background-color: #f8f9fa;
            /* Light background */
            border-right: 1px solid #ced4da;
            /* Border to separate from input */
            display: flex;
            align-items: center;
            /* Center vertically */
            padding: 0.5rem;
            /* Padding around the text */
            font-weight: bold;
            /* Bold text for emphasis */
        }

        /* Contact number input field */
        #phone {
            padding-left: 0.5rem;
            /* Padding to align text with country code */
        }
    </style>
    
</head>
<body>
    <?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar2.php';
        include '../../includes/footer.php';
    ?>
<div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
    <div class="row justify-content-center">
        <h2>User Profile</h2>
        <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
            <form method="POST" action="">
                <div class="row mb-3">
                    <div class="col-md-6">
                    <label for="employeeType" class="form-label required">Employee Type<span class="text-danger">*</span></label>
                        <select class="form-select" id="employeeType" name="employeeType" required>
                            <option value="" disabled selected>Select Role</option>
                            <option value="Conductor">Conductor</option>
                            <option value="Driver">Driver</option>
                            <option value="Cashier">Cashier</option>
                            <option value="Inspector">Inspector</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="employeeNumber" class="form-label required">Employee No.<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="employeeNumber" value="<?= htmlspecialchars($user['account_number'] ?? '') ?>" name="employeeNumber" placeholder="Scan NFC Here" required disabled>
                        <div id="employeeNumberFeedback" class="invalid-feedback"></div>
                    </div>

                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="firstName" class="form-label">First Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="firstName" value="<?= htmlspecialchars($user['firstname'] ?? '') ?>" name="firstName" placeholder="Enter first name" required pattern="[A-Za-z\s]+" title="Letters only">
                    </div>
                    <div class="col-md-4">
                        <label for="middleName" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="middleName" value="<?= htmlspecialchars($user['middlename'] ?? '') ?>" name="middleName" placeholder="Enter Middle name" pattern="[A-Za-z\s]+" title="Letters only">
                    </div>
                    <div class="col-md-4">
                        <label for="lastName" class="form-label">Last Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="lastName" value="<?= htmlspecialchars($user['lastname'] ?? '') ?>" name="lastName" placeholder="Enter last name" required pattern="[A-Za-z\s]+" title="Letters only">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email<span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" name="email" placeholder="Enter email" required>
                        <div id="emailFeedback" class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone<span class="text-danger">*</span></label>
                        <div class="form-group position-relative">
                            <input type="text" class="form-control ps-5" id="phone" value="<?= htmlspecialchars($user['contactnumber'] ?? '') ?>" name="contactnumber" placeholder="" required pattern="\d{10}" maxlength="10" required/>
                            <span class="position-absolute top-50 start-0 translate-middle-y ps-2 text-muted">+63</span>
                        </div>
                        <div id="contactError" class="invalid-feedback" style="display: none;"></div>
                    </div>
                </div>

                <div class="row mb-3">
                    
                <div class="col-md-6">
                        <label for="dob" class="form-label">Date of Birth</label>
                        <input type="date" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>" class="form-control" id="dob" name="dob">
                </div>
                <div class="col-md-6">   
                        <label for="gender" class="form-label">Gender<span class="text-danger">*</span></label>
                        <select class="form-select" id="gender" value="<?= htmlspecialchars($user['gender'] ?? '') ?>" name="gender" required>
                            <option value="" disabled selected>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                </div>
                </div>
                <div class="row mb-3">   
                    <div class="col-md-12">
                        <label for="address" class="form-label">Address<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="Purok#/Street/Sitio" required> 
                    </div>
                </div>
                <div class="row mb-3">         
                    <div class="col-md-4">
                        <label for="province" class="form-label">Province<span class="text-danger">*</span></label>
                        <select class="form-select" id="province" name="province" required>
                            <option value="">-- Select Province --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="municipality" class="form-label">Municipality<span class="text-danger">*</span></label>
                        <select class="form-select" id="municipality" name="municipality" required> 
                            <option value="">-- Select Municipality --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="barangay" class="form-label">Barangay<span class="text-danger">*</span></label>
                        <select class="form-select" id="barangay" name="barangay" required>
                            <option value="">-- Select Barangay --</option>
                        </select>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="../../assets/js/address_api.js"></script>
</body>
</html>