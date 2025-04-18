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

    if ($employeeType === 'Conductor') {
        $accountNumber = $_POST['employeeNumber']; // Manually provided
    } else {
        // 1. Generate unique account number using timestamp and random number
        $prefix = '00';
        $timestamp = time();  // Current timestamp
        $randomNumber = rand(1000, 9999);  // Random number between 1000 and 9999

        // Combine them to form a unique account number
        $accountNumber = $prefix . $timestamp . $randomNumber;
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
    <h2>Employee List</h2>
        <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#employeeModal">
        + Register Employee
        </button>

        <!-- Dropdown Filter -->
        <div class="d-flex justify-content-end mb-3"> 
            <form method="GET">
                <select name="role_filter" class="form-select" onchange="this.form.submit()" style="width: 150px;">
                    <option value="">All Roles</option>
                    <option value="Driver" <?= isset($_GET['role_filter']) && $_GET['role_filter'] === 'Driver' ? 'selected' : '' ?>>Driver</option>
                    <option value="Conductor" <?= isset($_GET['role_filter']) && $_GET['role_filter'] === 'Conductor' ? 'selected' : '' ?>>Conductor</option>
                    <option value="Cashier" <?= isset($_GET['role_filter']) && $_GET['role_filter'] === 'Cashier' ? 'selected' : '' ?>>Cashier</option>
                </select>
            </form>
        </div>

        <!-- Employee Table -->
        <div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Account #</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Role</th>
                <th>Date Hired</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $allowed_roles = ['Driver', 'Conductor', 'Cashier'];
            $filter = isset($_GET['role_filter']) ? $_GET['role_filter'] : 'all';

            $where = "WHERE role IN ('Driver', 'Conductor', 'Cashier')";
            if (in_array($filter, $allowed_roles)) {
                $where = "WHERE role = '" . mysqli_real_escape_string($conn, $filter) . "'";
            }

            $limit = 10;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $start = ($page - 1) * $limit;

            $sql = "SELECT account_number, firstname, middlename, lastname, suffix, email, contactnumber, created_at, role 
                    FROM useracc
                    $where 
                    ORDER BY created_at DESC 
                    LIMIT $start, $limit";
            $result = mysqli_query($conn, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $fullname = $row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname'];
                    if (!empty($row['suffix'])) {
                        $fullname .= ', ' . $row['suffix'];
                    }
                    echo "<tr>
                            <td>{$row['account_number']}</td>
                            <td>" . htmlspecialchars($fullname) . "</td>
                            <td>{$row['email']}</td>
                            <td>{$row['contactnumber']}</td>
                            <td>{$row['role']}</td>
                            <td>" . date('F d, Y', strtotime($row['created_at'])) . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>No records found.</td></tr>";
            }

            // PAGINATION
            $count_sql = "SELECT COUNT(*) AS total FROM useracc $where";
            $count_result = mysqli_query($conn, $count_sql);
            $total = mysqli_fetch_assoc($count_result)['total'];
            $pages = ceil($total / $limit);

            ?>
        </tbody>
    </table>

    <?php if ($pages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <!-- Previous Button -->
            <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>&role_filter=<?= urlencode($filter) ?>" tabindex="-1">Prev</a>
            </li>

            <!-- Page Numbers -->
            <?php 
            // Display a range of pages around the current one
            $range = 2; // Number of pages before and after the current page to display
            $start = max(1, $page - $range);
            $end = min($pages, $page + $range);
            
            for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&role_filter=<?= urlencode($filter) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <!-- Next Button -->
            <li class="page-item <?= $page == $pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>&role_filter=<?= urlencode($filter) ?>">Next</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

</div>    
    </div>
</div>
<div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title w-100 text-center" id="employeeModalLabel">Employee Registration</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php include 'features/regemployee_registration.php'; ?>
                </div>

                </div>
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
    let confirmationShown = false;

    const form = $("form")[0]; // Get the native form DOM element

    $('.btn-primary').click(function (event) {
        event.preventDefault();

        if (!confirmationShown) {
            if (!form.checkValidity()) {
                form.reportValidity(); // Show native validation messages
                return; // Don't proceed if form is invalid
            }

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
                    Swal.fire({
                        title: 'Registration Successful!',
                        text: 'You have been successfully registered.',
                        icon: 'success',
                        confirmButtonColor: '#3085d6',
                        timer: 1000,
                        timerProgressBar: true,
                    }).then(() => {
                        form.submit(); // Safe to submit now
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
            //document.getElementById("driverFields").style.display = "block";
            employeeNumberField.placeholder = "Auto generated";  
            employeeNumberField.value = "";  
            employeeNumberField.readOnly = true;  
        } else if (role === "Conductor") {
            //document.getElementById("conductorFields").style.display = "block";
            employeeNumberField.readOnly = false;
            employeeNumberField.placeholder = "Scan RFID Here";   
            employeeNumberField.value = ""; 
        } else if (role === "Cashier") {
            //document.getElementById("cashierFields").style.display = "block";
            employeeNumberField.placeholder = "Auto generated";  
            employeeNumberField.value = "";  
            employeeNumberField.readOnly = true;  
        }
    });
});
</script>
</body>
</html>