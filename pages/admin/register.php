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

        /* Styling for the contact number input group */
        .form-group {
            position: relative;
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

        /* Adjust width to ensure country code and input look good together */
        .form-group .form-control {
            flex: 1;
            /* Allow the input to take up remaining space */
            min-width: 0;
            /* Prevent overflow */
        }
    </style>
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/register.js"></script>
  
</head>

<body>
    <?php
    include '../../includes/topbar.php';
    include '../../includes/sidebar.php';
    include '../../includes/footer.php';
    ?>
    <div id="main-content" class="container mt-5">
        <h2>Registration Form</h2>
        <form method="POST" action="../../actions/confirm_register.php" id="registrationForm" enctype="multipart/form-data">
            <style>
                .form-label.required::after {
                    content: " *";
                    color: red;
                }
            </style>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="firstname" class="form-label required">First Name</label>
                    <input type="text" class="form-control" id="firstname" name="firstname" required>
                </div>
                <div class="col-md-6">
                    <label for="lastname" class="form-label required">Last Name</label>
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
                    <input type="text" class="form-control" id="suffix" name="suffix">
                  
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="birthday" class="form-label required">Birthday</label>
                    <input type="date" class="form-control" id="birthday" name="birthday" required min=""
                        max="2017-12-31" />
                </div>
                <div class="col-md-6">
                    <label for="gender" class="form-label required">Gender</label>
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="">-- Select Gender --</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
               
            </div>
            <div class="row mb-3">
               
                <div class="col-md-6">
                    <label for="address" class="form-label">House#/Purok#/Street/Sitio</label>
                    <input type="text" class="form-control" id="address" name="address">
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
                    <label for="email" class="form-label required">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div id="emailFeedback" class="invalid-feedback"></div>
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label required">Contact Number</label>
                    <div class="form-group d-flex">
                        <span class="border-end country-code px-2">+63</span>
                        <input type="text" class="form-control" id="phone" name="contactnumber" placeholder="" required
                            pattern="\d{10}" maxlength="10" />
                    </div>
                    <div id="contactError" class="invalid-feedback" style="display: none;"></div>
                </div>
            </div>

            <div class="row mb-3">
               

                <div class="col-md-6">
                    <label for="account_number" class="form-label">Account Number</label>
                    <input type="text" class="form-control" id="account_number" name="account_number" readonly>
                </div>
                <input type="hidden" name="role" value="User">
            </div>
            <button type="submit" class="btn btn-primary">Register</button>


        </form>
    </div>
</body>


</html>