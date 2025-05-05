<?php
require '../libraries/PHPMailer/src/PHPMailer.php';
require '../libraries/PHPMailer/src/SMTP.php';
require '../libraries/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include "../includes/connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $middlename = $_POST['middlename'];
    $suffix = $_POST['suffix'];
    $birthday = $_POST['birthday'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $contactnumber = $_POST['contactnumber'];
    $province = $_POST['province'];
    $municipality = $_POST['municipality'];
    $barangay = $_POST['barangay'];
    $address = $_POST['address'];
    $account_number = $_POST['account_number'];
    $password = 'REDACTED_LEGACY_PASSWORD';

    $hashed_password = md5($password); // Hash the password
    $balance = 0; // Default balance
    $role = "User"; // Default role
    $points = 0; // Default points

    // Validate email and contact number
    $stmt = $conn->prepare("SELECT * FROM useracc WHERE email = ? OR contactnumber = ?");
    $stmt->bind_param("ss", $email, $contactnumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email or contact number already registered.'); window.history.back();</script>";
        exit();
    }

    // Insert new user into the database
    $stmt = $conn->prepare("INSERT INTO useracc (firstname, lastname, middlename, suffix, birthday, age, gender, email, contactnumber, province, municipality, barangay, address, password, balance, role, points) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ssssssssiiiissdsd",
        $firstname,
        $lastname,
        $middlename,
        $suffix,
        $birthday,
        $age,
        $gender,
        $email,
        $contactnumber,
        $province,
        $municipality,
        $barangay,
        $address,
        $hashed_password,
        $balance,
        $role,
        $points
    );

    if ($stmt->execute()) {
        // Send confirmation email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'portfolio@example.invalid'; // Your email
            $mail->Password = 'REDACTED_SMTP_PASSWORD'; // App password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('portfolio@example.invalid', 'Ramstar Zaragoza');
            $mail->addAddress($email, $firstname . ' ' . $lastname);
            $mail->isHTML(true);
            $mail->Subject = 'Registration Received';
            $mail->Body = "
                <p>Hi $firstname,</p>
                <p>Thank you for registering with Ramstar Zaragoza.</p>
                <p>Your account is pending activation. You will receive another email once you get your card in ramstar office or teminals.</p>
                <p>Best regards,<br>Ramstar Zaragoza</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }

        echo "<script>alert('Registration successful!'); window.location.href = 'login.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Registration Form</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
        }

        .register {
            background-color: #cc0000;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s;
        }

        .register:hover {
            background-color: #b30000;
        }

        .country-code {
            background-color: #f8f9fa;
            border-right: 1px solid #ced4da;
            display: flex;
            align-items: center;
            padding: 0.5rem;
            font-weight: bold;
        }

        .form-group .form-control {
            flex: 1;
            min-width: 0;
        }

        .invalid-feedback {
            display: block;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-row {
            margin-top: 20px;
        }

        .form-select {
            background-color: #fff;
        }

        .form-control[readonly] {
            background-color: #f1f1f1;
        }

        .invalid-feedback {
            color: #dc3545;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.25rem rgba(38, 143, 255, 0.25);
        }

        .form-group select,
        .form-group input {
            border-radius: 5px;
        }

        .register-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        header {
            background: linear-gradient(to right, rgb(243, 75, 83), rgb(131, 4, 4));
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        header nav ul {
            display: flex;
            /* Make the <ul> a flex container */
            justify-content: flex-start;
            /* Align items to the left */
            padding: 0;
            margin: 0;
        }

        header nav ul li a {
            color: white;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 30px;
            background: #f1c40f;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
        }


        header nav ul li a:hover {
            background: #e67e22;
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        header nav ul li a:active {
            background: #f1c40f;
            transform: scale(1);
        }
    </style>
    

</head>

<body>
    <header>
        <nav>
            <ul>
                <li><a href="../../index.php">Home</a></li>
            </ul>
        </nav>
    </header>
    <div class="container mt-5 register-container">
        <h2>Registration Form</h2>
        <form method="POST" action="" id="registrationForm" enctype="multipart/form-data">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="firstname" class="form-label">
                        First Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="firstname" name="firstname" required>
                </div>
                <div class="col-md-6">
                    <label for="lastname" class="form-label">
                        Last Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="lastname" name="lastname" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="middlename" class="form-label">Middle Name</label>
                    <input type="text" class="form-control" id="middlename" name="middlename" required>
                </div>
                <div class="col-md-6">
                    <label for="suffix" class="form-label">Suffix</label>
                    <select class="form-select" id="suffix" name="suffix">
                        <option value="">-- Select Suffix --</option>
                        <option value="Jr">Jr.</option>
                        <option value="Sr">Sr.</option>
                        <option value="III">III</option>
                        <option value="IV">IV</option>
                        <option value="V">V</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
            <div class="col-md-6">
                <label for="birthday" class="form-label">
                    Birthday 
                </label>
                <input type="date" class="form-control" id="birthday" name="birthday" />
            </div>
                <div class="col-md-6">
                    <label for="gender" class="form-label">Gender</label>
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="">-- Select Gender --</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="Purok/Sitio/Street">
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

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="email" class="form-label">
                        Email <span class="text-danger">*</span>
                    </label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div id="emailFeedback" class="invalid-feedback"></div>
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">
                        Contact Number <span class="text-danger">*</span>
                    </label>
                    <div class="form-group d-flex">
                        <span class="border-end country-code px-2">+63</span>
                        <input type="text" class="form-control" id="phone" name="contactnumber" placeholder="" maxlength="10" required/>
                    </div>
                    <div id="contactError" class="invalid-feedback" style="display: none;"></div>
                </div>
            </div>
            <div class="form-row mt-4">
                <button type="submit" class="register">Register</button>
            </div>
        </form>
    </div>
</body>
<script>
        document.addEventListener('DOMContentLoaded', function () {
            const nameFields = ['firstname', 'middlename', 'lastname', 'suffix'];

            nameFields.forEach(fieldId => {
                document.getElementById(fieldId).addEventListener('input', function (e) {
                    this.value = this.value.replace(/[^A-Za-z\s-]/g, ''); // Allow only letters, spaces, and hyphens
                });
            });
        });

        document.getElementById("registrationForm").addEventListener("submit", function(e) {
        e.preventDefault(); // Prevent form from submitting

        Swal.fire({
            title: 'Confirm Registration',
            text: "Are you sure you want to submit this form?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, submit it!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                e.target.submit(); // Submit the form if confirmed
            }
        });
    });

    // Set max date so that user must be at least 7 years old
    const birthdayInput = document.getElementById('birthday');
    const today = new Date();
    const year = today.getFullYear() - 10;
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    const maxDate = `${year}-${month}-${day}`;

    birthdayInput.max = maxDate;

        $(document).ready(function () {
            let confirmationShown = false; // To track confirmation dialog
            let rfidScanned = false; // To track if RFID has been scanned

            $('#phone').on('input', function () {
                var contactValue = $(this).val();

                // Allow only digits and limit to 11 characters
                contactValue = contactValue.replace(/[^0-9]/g, ''); // Remove non-numeric characters
                if (contactValue.length > 11) {
                    contactValue = contactValue.substring(0, 11); // Limit to 11 digits
                }
                $(this).val(contactValue); // Update the input value

                // Send AJAX request if the input has exactly 11 characters
                if (contactValue.length === 11) {
                    $.ajax({
                        type: "POST",
                        url: "../actions/check_contact.php",
                        data: { contactnumber: contactValue },
                        dataType: "json",
                        success: function (response) {
                            if (response.exists) {
                                $('#phone').addClass('is-invalid'); // Add invalid class to input
                                // Set error message directly in the existing div
                                $('#contactError').text("This contact number is already registered.").show();
                            } else {
                                $('#phone').removeClass('is-invalid'); // Remove invalid class
                                $('#contactError').hide(); // Hide error message if it exists
                            }
                        },
                        error: function () {
                            console.error("Error checking contact number.");
                        }
                    });
                } else {
                    $('#phone').removeClass('is-invalid');
                    $('#contactError').hide(); // Hide error message if the input is less than 11 characters
                }
            });

            $('#email').on('input', function () {
                var email = $(this).val();

                // Check if email is not empty
                if (email) {
                    $.ajax({
                        url: '../actions/check_email.php', // Path to your PHP script
                        type: 'POST',
                        data: { email: email },
                        dataType: 'json',
                        success: function (response) {
                            if (response.exists) {
                                // Email already exists
                                $('#email').addClass('is-invalid');
                                $('#emailFeedback').remove();
                                $('#email').after('<div id="emailFeedback" class="invalid-feedback">This email is already registered.</div>');
                            } else {
                                // Email does not exist
                                $('#email').removeClass('is-invalid');
                                $('#emailFeedback').remove();
                            }
                        },
                        error: function () {
                            console.error('Error checking email.');
                        }
                    });
                } else {
                    // Reset feedback if email is empty
                    $('#email').removeClass('is-invalid');
                    $('#emailFeedback').remove();
                }
            });
        });

        $(document).ready(function () {
            // Load provinces on page load
            $.ajax({
                url: 'https://psgc.gitlab.io/api/provinces', // API URL for provinces
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    // Populate the province dropdown
                    data.sort((a, b) => a.name.localeCompare(b.name));
                    $.each(data, function (index, province) {
                        $('#province').append($('<option>', {
                            value: province.code,
                            text: province.name
                        }));
                    });
                },
                error: function () {
                    console.error('Error fetching provinces');
                }
            });

            // When a province is selected, fetch municipalities
            $('#province').change(function () {
                const provinceCode = $(this).val();
                $('#municipality').empty().append('<option value="">-- Select Municipality --</option>');
                $('#barangay').empty().append('<option value="">-- Select Barangay --</option>');

                if (provinceCode) {
                    $.ajax({
                        url: 'https://psgc.gitlab.io/api/cities-municipalities', // API URL for municipalities
                        method: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            // Filter municipalities by province code
                            const municipalities = data.filter(municipality => municipality.provinceCode === provinceCode);
                            municipalities.sort((a, b) => a.name.localeCompare(b.name));
                            if (municipalities.length > 0) {
                                $.each(municipalities, function (index, municipality) {
                                    $('#municipality').append($('<option>', {
                                        value: municipality.code,
                                        text: municipality.name
                                    }));
                                });
                            } else {
                                console.warn('No municipalities found for this province.');
                            }
                        },
                        error: function () {
                            console.error('Error fetching municipalities');
                        }
                    });
                }
            });

            // When a municipality is selected, fetch barangays
            $('#municipality').change(function () {
                const municipalityCode = $(this).val();
                $('#barangay').empty().append('<option value="">-- Select Barangay --</option>');

                if (municipalityCode) {
                    // Adjusted barangay API call
                    $.ajax({
                        url: `https://psgc.gitlab.io/api/barangays`, // Ensure this endpoint is correct
                        method: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            // Filter barangays by municipality code
                            const barangays = data.filter(barangay => barangay.municipalityCode === municipalityCode);
                            barangays.sort((a, b) => a.name.localeCompare(b.name));
                            if (barangays.length > 0) {
                                $.each(barangays, function (index, barangay) {
                                    $('#barangay').append($('<option>', {
                                        value: barangay.code,
                                        text: barangay.name
                                    }));
                                });
                            } else {
                                console.warn('No barangays found for this municipality.');
                            }
                        },
                        error: function () {
                            console.error('Error fetching barangays');
                        }
                    });
                }
            });
        });
    </script>
</html>