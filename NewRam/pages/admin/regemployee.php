<?php
ob_start();
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../includes/connection.php';
var_dump($_POST);
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

    // Prepare the SQL query
    $query = "INSERT INTO useracc (
        account_number, firstname, middlename, lastname, email, contactnumber, birthday, age, gender,
        province, municipality, barangay, address, password, role, is_activated, driverLicense
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    if ($stmt = $conn->prepare($query)) {
        // Bind parameters
        $stmt->bind_param("sssssssssssssssis", $accountNumber, $firstName, $middleName, $lastName, $email, 
            $phone, $birthday, $age, $gender, $province, $municipality, $barangay, $address, $password, $role, $activated, $license);

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
<div id="main-content" class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-6 col-xxl-8">
            <h2 class="text-center">Employee Registration</h2>
            <form method="POST" action="">
                <div class="row mb-3">
                    <div class="col-md-6">
                    <label for="employeeType" class="form-label required">Employee Type</label>
                        <select class="form-select" id="employeeType" name="employeeType" required>
                            <option value="" disabled selected>Select Role</option>
                            <option value="Conductor">Conductor</option>
                            <option value="Driver">Driver</option>
                            <option value="Cashier">Cashier</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="employeeNumber" class="form-label required">Employee No.</label>
                        <input type="text" class="form-control" id="employeeNumber" name="employeeNumber" placeholder="Auto generated" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="firstName" class="form-label required">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter first name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="middleName" class="form-label required">Middle Name</label>
                        <input type="text" class="form-control" id="middleName" name="middleName" placeholder="Enter Middle name">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="lastName" class="form-label required">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Enter last name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label required">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                        <div id="emailFeedback" class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="row mb-3">
                <div class="col-md-4">
                    <label for="phone" class="form-label required">Phone</label>
                    <div class="form-group position-relative">
                        <input type="text" class="form-control ps-5" id="phone" name="contactnumber" placeholder="" required pattern="\d{10}" maxlength="10" />
                        <span class="position-absolute top-50 start-0 translate-middle-y ps-2 text-muted">+63</span>
                    </div>
                    <div id="contactError" class="invalid-feedback" style="display: none;"></div>
                    </div>
                <div class="col-md-4">
                        <label for="dob" class="form-label required">Date of Birth</label>
                        <input type="date" class="form-control" id="dob" name="dob" required>
                </div>
                <div class="col-md-4">   
                        <label for="gender" class="form-label required">Gender</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="" disabled selected>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                </div>
                </div>

                <div class="row mb-3">   
                        <div class="col-md-6">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" placeholder="Purok#/Street/Sitio"> 
                        </div>
                        <div class="col-md-6">
                            <label for="province" class="form-label">Province</label>
                            <select class="form-select" id="province" name="province">
                                <option value="">-- Select Province --</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="municipality" class="form-label">Municipality</label>
                            <select class="form-select" id="municipality" name="municipality">
                                <option value="">-- Select Municipality --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="barangay" class="form-label">Barangay</label>
                            <select class="form-select" id="barangay" name="barangay">
                                <option value="">-- Select Barangay --</option>
                            </select>
                        </div>
                    </div>

                <!-- Conditional fields for Driver -->
                <div id="driverFields" class="driver-fields" style="display:none;">
                    <div class="row mb-3">
                        <div class="col-md-12">
                                <label for="driverLicense" class="form-label required">Driver's License No.</label>
                                <input type="text" class="form-control" id="driverLicense" name="driverLicense" placeholder="Enter license number">
                        </div>
                    </div>
                </div>

                <!-- Conditional fields for Conductor -->
                <div id="conductorFields" class="conductor-fields" style="display:none;">
                    <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="workExperience" class="form-label required">Work Experience</label>
                            <textarea class="form-control" id="workExperience" rows="2" placeholder="Enter work experience"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Conditional fields for Cashier -->
                <div id="cashierFields" class="cashier-fields" style="display:none;">
                    <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="cashHandlingExperience" class="form-label required">Cash Handling Experience</label>
                            <textarea class="form-control" id="cashHandlingExperience" rows="2" placeholder="Enter experience in cash handling"></textarea>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../assets/js/address_api.js"></script>
<script>

const licenseInput = document.getElementById("driverLicense");

licenseInput.addEventListener("input", function(e) {
  // Remove hyphens only
  let raw = e.target.value.replace(/-/g, "");

  // Limit to 11 characters total
  raw = raw.substring(0, 11);

  // Format: XXX-XX-XXXXX
  let formatted = '';
  if (raw.length > 0) {
    formatted += raw.substring(0, 3);
  }
  if (raw.length > 3) {
    formatted += '-' + raw.substring(3, 5);
  }
  if (raw.length > 5) {
    formatted += '-' + raw.substring(5, 11);
  }

  e.target.value = formatted;
});

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
    var today = new Date();
    today.setFullYear(today.getFullYear() - 18);  // Subtract 18 years
    document.getElementById("dob").setAttribute("max", today.toISOString().split('T')[0]);


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

    $('.btn-primary').click(function (event) {
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
                    // Show success message
                    Swal.fire({
                        title: 'Registration Successful!',
                        text: 'You have been successfully registered.',
                        icon: 'success',
                        confirmButtonColor: '#3085d6',
                        timer: 1000, // The message will stay for 5 seconds
                        timerProgressBar: true,
                    }).then(() => {
                        // After the success message closes, submit the form
                        form.submit();  // Submit the form here
                    });
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
<?php //include '../../actions/swal_success_message.php'; ?>
</body>
</html>