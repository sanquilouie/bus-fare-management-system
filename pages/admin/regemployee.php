<?php
ob_start();
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    $birthday = $_POST['birthday'];
    $address = $_POST['address'];
    $gender = $_POST['gender'];
    $license = $_POST['driverLicense'] ?? null; // Use null coalescing for optional field

    // Generate employee number
    if ($employeeType === 'Conductor') {
        $accountNumber = $_POST['employeeNumber']; // User input for Conductor
    } else {
        $prefix = '00123456';  // Fixed prefix
        $lastEmployeeQuery = "SELECT account_number FROM useracc ORDER BY id DESC LIMIT 1";
        $result = $conn->query($lastEmployeeQuery);

        if ($result && $result->num_rows > 0) {
            $lastEmployee = $result->fetch_assoc();
            $lastEmployeeNumber = isset($lastEmployee['account_number']) ? substr($lastEmployee['account_number'], strlen($prefix)) : 0;
            $newEmployeeNumber = str_pad((int)$lastEmployeeNumber + 1, 0, '0', STR_PAD_LEFT);
        } else {
            $newEmployeeNumber = '78';  // If no employees exist yet
        }

        $accountNumber = $prefix . $newEmployeeNumber;
    }

    // Calculate age
    $age = date_diff(date_create($birthday), date_create('today'))->y;

    // Password generation
    $pass = 'ramstarbus123';
    $password = md5($pass); // Consider using password_hash() for better security

    // Set default role and activation status
    $role = $employeeType;
    $activated = 1;
    $createdAt = date('Y-m-d H:i:s');

    // Prepare the SQL query
    $query = "INSERT INTO useracc (
        account_number, firstname, middlename, lastname, email, contactnumber, birthday, age, gender,
        address, password, role, created_at, is_activated, driverLicense
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    if ($stmt = $conn->prepare($query)) {
        // Bind parameters
        $stmt->bind_param("ssssssssssssiss", $accountNumber, $firstName, $middleName, $lastName, $email, 
            $phone, $birthday, $age, $gender, $address, $password, $role, $createdAt, $activated, $license);

        // Execute the query
        if ($stmt->execute()) {
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
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Use full version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 
    <link rel="stylesheet" href="../../assets/css/sidebars.css">
    <style>
        h2{
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: bold;
            color: transparent;
            /* Make the text color transparent */
            background-image: linear-gradient(to right, #f1c40f, #e67e22);
            background-clip: text;
            -webkit-background-clip: text;
            /* WebKit compatibility */
            -webkit-text-fill-color: transparent;
            /* Ensures only the gradient is visible */
            -webkit-text-stroke: 0.5px black;
            /* Outline effect */
        }
      /* Country code styling */
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
        .primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body>
    <?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar.php';
        include '../../includes/footer.php';
    ?>
<div id="main-content" class="container mt-5">
    <h2 class="text-center mb-4">Employee Registration</h2>

    <form method="POST" action="">
        <div class="row mb-3">
            <label for="employeeType" class="col-sm-2 col-form-label">Employee Type</label>
            <div class="col-sm-10">
                <select class="form-select" id="employeeType" name="employeeType" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="Conductor">Conductor</option>
                    <option value="Driver">Driver</option>
                    <option value="Cashier">Cashier</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <label for="employeeNumber" class="col-sm-2 col-form-label">Employee Number</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="employeeNumber" name="employeeNumber" placeholder="Auto generated" readonly>
            </div>
        </div>
        <div class="row mb-3">
            <label for="firstName" class="col-sm-2 col-form-label">First Name</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter first name" required>
            </div>
        </div>

        <div class="row mb-3">
            <label for="middleName" class="col-sm-2 col-form-label">Middle Name</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="middleName" name="middleName" placeholder="Enter Middle name">
            </div>
        </div>

        <div class="row mb-3">
            <label for="lastName" class="col-sm-2 col-form-label">Last Name</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Enter last name" required>
            </div>
        </div>

        <div class="row mb-3">
            <label for="email" class="col-sm-2 col-form-label">Email</label>
            <div class="col-sm-10">
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                <div id="emailFeedback" class="invalid-feedback"></div>
            </div>
        </div>

        <div class="row mb-3">
            <label for="phone" class="col-sm-2 col-form-label">Phone</label>
            <div class="col-sm-10">
            <div class="form-group d-flex">
                        <span class="border-end country-code px-2">+63</span>
                        <input type="text" class="form-control" id="phone" name="contactnumber" placeholder="" required
                            pattern="\d{11}" maxlength="11" />
                    </div>
                    <div id="contactError" class="invalid-feedback" style="display: none;"></div>
            </div>
        </div>

        <div class="row mb-3">
            <label for="dob" class="col-sm-2 col-form-label">Date of Birth</label>
            <div class="col-sm-10">
                <input type="date" class="form-control" id="dob" name="birthday" required>
            </div>
        </div>

        <div class="row mb-3">
            <label for="gender" class="col-sm-2 col-form-label">Gender</label>
            <div class="col-sm-10">
                <select class="form-select" id="gender" name="gender" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <label for="address" class="col-sm-2 col-form-label">Address</label>
            <div class="col-sm-10">
                <textarea class="form-control" id="address" rows="3" name="address" placeholder="Enter address" required></textarea>
            </div>
        </div>

        <!-- Conditional fields for Driver -->
        <div id="driverFields" class="driver-fields" style="display:none;">
            <div class="row mb-3">
                <label for="driverLicense" class="col-sm-2 col-form-label">Driver's License No.</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="driverLicense" name="driverLicense" placeholder="Enter license number">
                </div>
            </div>
        </div>

        <!-- Conditional fields for Conductor -->
        <div id="conductorFields" class="conductor-fields" style="display:none;">
            <div class="row mb-3">
                <label for="workExperience" class="col-sm-2 col-form-label">Work Experience</label>
                <div class="col-sm-10">
                    <textarea class="form-control" id="workExperience" rows="3" placeholder="Enter work experience"></textarea>
                </div>
            </div>
        </div>

        <!-- Conditional fields for Cashier -->
        <div id="cashierFields" class="cashier-fields" style="display:none;">
            <div class="row mb-3">
                <label for="cashHandlingExperience" class="col-sm-2 col-form-label">Cash Handling Experience</label>
                <div class="col-sm-10">
                    <textarea class="form-control" id="cashHandlingExperience" rows="3" placeholder="Enter experience in cash handling"></textarea>
                </div>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="btn primary">Register</button>
        </div>
    </form>
</div>


<script>

function checkSubmitButton() {
    var emailValid = !$('#email').hasClass('is-invalid');
    var contactValid = !$('#phone').hasClass('is-invalid');

    // Enable the register button only if both are valid
    var registerButton = $('.primary');
    if (emailValid && contactValid) {
        registerButton.prop('disabled', false);
    } else {
        registerButton.prop('disabled', true);
    }
}

$(document).ready(function () {
  let registerButton = $('.btn-primary');
  $('#phone').on('input', function () {
    var contactValue = $(this).val().replace(/[^0-9]/g, '').substring(0, 11);
    $(this).val(contactValue);

    // Make sure to disable the button initially
    var registerButton = $('.primary');
    registerButton.prop('disabled', true);

    if (contactValue.length === 11) {
        $.ajax({
            type: "POST",
            url: "../../actions/check_contact.php", // Make sure this file checks contact number
            data: { contactnumber: contactValue },
            dataType: "json",
            success: function (response) {
                if (response.exists) {
                    $('#phone').addClass('is-invalid');
                    $('#contactError').text("This contact number is already registered.").show();
                } else {
                    $('#phone').removeClass('is-invalid');
                    $('#contactError').hide();
                }
                checkSubmitButton(); // Check if both email and contact are valid
            },
            error: function () {
                console.error("Error checking contact number.");
            }
        });
    } else {
        $('#phone').removeClass('is-invalid');
        $('#contactError').hide();
        checkSubmitButton(); // Re-check submit status
    }
});

    $('#email').on('input', function () {
    var email = $(this).val();
    var registerButton = $('.primary');
    registerButton.prop('disabled', true); // Disable register button initially

    if (email) {
        $.ajax({
            url: '../../actions/check_email.php', // File to check email availability
            type: 'POST',
            data: { email: email },
            dataType: 'json',
            success: function (response) {
                if (response.exists) {
                    $('#email').addClass('is-invalid');
                    $('#emailFeedback').remove();
                    $('#email').after('<div id="emailFeedback" class="invalid-feedback">This email is already registered.</div>');
                } else {
                    $('#email').removeClass('is-invalid');
                    $('#emailFeedback').remove();
                }
                checkSubmitButton(); // Check if both email and contact are valid
            },
            error: function () {
                console.error('Error checking email.');
            }
        });
    } else {
        $('#email').removeClass('is-invalid');
        $('#emailFeedback').remove();
        checkSubmitButton(); // Re-check submit status
    }
});

    $(document).ready(function () {
    let confirmationShown = false; // To track confirmation dialog

    // Define the form element
    const form = $("form"); // or use $('#yourFormId') if your form has an ID

    $('.primary').click(function (event) {
        event.preventDefault(); // Prevent the default form submission

        if (!confirmationShown) {
            confirmationShown = true;

            Swal.fire({
                title: 'Confirm Registration?',
                text: "Are you sure you want to register?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#cc0000',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, register!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Now, submit the form
                }
            });
        }
    });
});
    // JavaScript to show/hide additional fields based on selected role
    document.getElementById("employeeType").addEventListener("change", function() {
        var role = this.value;
        var employeeNumberField = document.getElementById("employeeNumber");
        
        // Hide all fields first
        document.getElementById("driverFields").style.display = "none";
        document.getElementById("conductorFields").style.display = "none";
        document.getElementById("cashierFields").style.display = "none";
        
        // Enable/disable the employee number field and show specific fields based on role
        if (role === "Driver") {
            document.getElementById("driverFields").style.display = "block";
            employeeNumberField.placeholder = "Auto generated";  
            employeeNumberField.value = "";  
            employeeNumberField.readOnly = true;  
        } else if (role === "Conductor") {
            document.getElementById("conductorFields").style.display = "block";
            employeeNumberField.readOnly = false;
            employeeNumberField.placeholder = "Scan RFID Here";   
            employeeNumberField.value = ""; 
        } else if (role === "Cashier") {
            document.getElementById("cashierFields").style.display = "block";
            employeeNumberField.placeholder = "Auto generated";  
            employeeNumberField.value = "";  
            employeeNumberField.readOnly = true;  
        }
    });
});
</script>
<?php include '../../actions/swal_success_message.php'; ?>
</body>
</html>