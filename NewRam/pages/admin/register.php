<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../index.php");
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
    <script src="/NewRam/assets/js/NFCScanner.js"></script>

    <title>Registration Form</title>
    <style>
        body{
            background-color: #f8f9fa;
        }
        
        .register {
            background-color: #cc0000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .register:hover {
            background-color: #b30000;
        }
        .form-group {
            position: relative;
        }
        .country-code {
            background-color: #f8f9fa;
            border-right: 1px solid #ced4da;
            display: flex;
            align-items: center;
            padding: 0.5rem;
            font-weight: bold;
        }

        #phone {
            padding-left: 0.5rem;
        }

        .form-group .form-control {
            flex: 1;
            min-width: 0;
        }
        .form-label.required::after {
                    content: " *";
                    color: red;
         }
         #loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255,255,255,0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    z-index: 9999;
    font-family: sans-serif;
}

.spinner {
    border: 6px solid #f3f3f3;
    border-top: 6px solid #3498db;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    margin-bottom: 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
    </style>
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/register.js"></script>
  
</head>

<body>
    <?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar2.php';
        include '../../includes/footer.php';
    ?>
    <div id="loader" style="display: none;">
        <div class="spinner"></div>
        <p>Sending confirmation email, please wait...</p>
    </div>
    <div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
                <h2>User Registration</h2>
                <form method="POST" action="../../actions/confirm_register.php" id="registrationForm" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="firstname" class="form-label required">First Name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" required>
                        </div>
                        <div class="col-md-4">
                            <label for="lastname" class="form-label required">Last Name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" required>
                        </div>
                        <div class="col-md-4">
                            <label for="middlename" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middlename" name="middlename">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <label for="birthday" class="form-label required">Birthday</label>
                            <input type="date" class="form-control" id="birthday" name="birthday" />
                        </div>
                        <div class="col-md-4">
                            <label for="gender" class="form-label required">Gender</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">-- Select Gender --</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">   
                        <div class="col-md-12">
                            <label for="address" class="form-label">House#/Purok#/Street/Sitio</label>
                            <input type="text" class="form-control" id="address" name="address">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="province" class="form-label">Province</label>
                            <select class="form-select" id="province" name="province">
                                <option value="">-- Select Province --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="municipality" class="form-label">Municipality</label>
                            <select class="form-select" id="municipality" name="municipality">
                                <option value="">-- Select Municipality --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="barangay" class="form-label">Barangay</label>
                            <select class="form-select" id="barangay" name="barangay">
                                <option value="">-- Select Barangay --</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3"> 
                        <div class="col-md-4">
                            <label for="account_number" class="form-label">Account Number</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" required>
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label required">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div id="emailFeedback" class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label for="phone" class="form-label">Contact Number</label>
                            <div class="form-group position-relative">
                                <input type="text" class="form-control ps-5" id="phone" name="contactnumber" placeholder=""
                                    pattern="\d{10}" maxlength="10" />
                                <span class="position-absolute top-50 start-0 translate-middle-y ps-2 text-muted">+63</span>
                            </div>
                            <div id="contactError" class="invalid-feedback" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <input type="hidden" name="role" value="User">
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Loader
        const form = document.querySelector("form");
        form.addEventListener("submit", function () {
            document.getElementById("loader").style.display = "flex";
        });

        // Set max date to today minus 10 years (birthday restriction)
        const birthdayInput = document.getElementById('birthday');
        const today = new Date();
        const tenYearsAgo = new Date(today.getFullYear() - 10, today.getMonth(), today.getDate());
        birthdayInput.max = tenYearsAgo.toISOString().split('T')[0];
    });
</script>

</body>
</html>