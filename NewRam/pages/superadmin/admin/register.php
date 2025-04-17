<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../index.php");
    exit();
}

$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../../assets/css/sidebars.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Use full version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <title>Registration Form</title>
    </style>
    
</head>

<body>
<?php
        include '../../../includes/topbar.php';
        include '../../../includes/superadmin_sidebar.php';
        include '../../../includes/footer.php';
    ?>
    <div id="main-content" class="container-fluid mt-5">
        <h2>Registration Form</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
                <form method="POST" action="../../../actions/confirm_register.php" id="registrationForm" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" required>
                        </div>
                        <div class="col-md-6">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="middlename" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middlename" name="middlename">
                        </div>
                        <div class="col-md-6">
                            <label for="suffix" class="form-label">Suffix</label>
                            <select class="form-select" id="suffix" name="suffix">
                                <option value="">-- Select Suffix --</option>
                                <option value="Jr">Jr</option>
                                <option value="Sr">Sr</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                                <option value="V">V</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="birthday" class="form-label">Birthday</label>
                            <input type="date" class="form-control" id="birthday" name="birthday" required min=""
                                max="2017-12-31" />
                        </div>

                        <div class="col-md-6">
                            <label for="age" class="form-label">Age</label>
                            <input type="number" class="form-control" id="age" name="age" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">-- Select Gender --</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="province" class="form-label">Province</label>
                            <select class="form-select" id="province" name="province" required>
                                <option value="">-- Select Province --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="municipality" class="form-label">Municipality</label>
                            <select class="form-select" id="municipality" name="municipality" required>
                                <option value="">-- Select Municipality --</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="barangay" class="form-label">Barangay</label>
                            <select class="form-select" id="barangay" name="barangay" required>
                                <option value="">-- Select Barangay --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div id="emailFeedback" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label required">Contact Number</label>
                            <div class="form-group position-relative">
                                <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">+63</span>
                                <input type="text" class="form-control ps-5" id="phone" name="contactnumber" required
                                    pattern="\d{10}" maxlength="10" placeholder="Enter 10-digit number" />
                            </div>
                            <div id="contactError" class="invalid-feedback" style="display: none;"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="account_number" class="form-label">Account Number</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" required>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>               
                </form>
            </div>
        </div>
    </div>
    <script>
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
                        url: "check_contact.php",
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
                        url: '../../../actions/check_email.php', // Path to your PHP script
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

            $('.register').click(function (event) {
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
                            Swal.fire({
                                title: 'Waiting for RFID',
                                text: 'Please scan your RFID tag.',
                                icon: 'info',
                                showConfirmButton: false,
                                allowOutsideClick: false
                            });

                            $('#account_number').removeAttr('readonly').focus();
                        } else {
                            confirmationShown = false; // Reset if cancelled
                        }
                    });
                }
            });

            $('#account_number').on('input', function () {
                var rfidValue = $(this).val();

                if (rfidValue.length === 10) { // Adjust the length if needed
                    Swal.fire({
                        title: 'RFID Scanned!',
                        text: 'Your RFID tag has been successfully scanned. Proceeding with registration...',
                        icon: 'success',
                        confirmButtonText: 'OK',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Only submit the form after the user clicks 'OK'
                            $('form').submit();
                        }
                    });
                }
            });

            // Birthday and age validation
            function calculateAge(birthday) {
                let today = new Date();
                let birthDate = new Date(birthday);
                let age = today.getFullYear() - birthDate.getFullYear();
                let monthDifference = today.getMonth() - birthDate.getMonth();
                if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                return age;
            }

            $('#birthday').change(function () {
                let birthday = $(this).val();
                if (birthday) {
                    let age = calculateAge(birthday);
                    $('#age').val(age);
                }
            });

            // Load provinces on page load
            $.ajax({
                url: 'https://psgc.gitlab.io/api/provinces', // API URL for provinces
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    // Populate the province dropdown
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
                        url: 'https://psgc.gitlab.io/api/municipalities', // API URL for municipalities
                        method: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            // Filter municipalities by province code
                            const municipalities = data.filter(municipality => municipality.provinceCode === provinceCode);

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

        // Calculate the date for 7 years ago
        const today = new Date();
        const sevenYearsAgo = new Date(today.setFullYear(today.getFullYear() - 7));

        // Format the date as YYYY-MM-DD
        const formattedDate = sevenYearsAgo.toISOString().split('T')[0];

        // Set the minimum date in the input field
        document.getElementById("birthday").setAttribute("min", formattedDate);

    </script>
</body>


</html>