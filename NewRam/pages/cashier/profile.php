<?php
ob_start();
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../includes/connection.php';


// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize POST data
    $accountNumber = $_SESSION['account_number']; // primary key
    $firstName     = $_POST['firstName'];
    $middleName    = $_POST['middleName'];
    $lastName      = $_POST['lastName'];
    $email         = $_POST['email'];
    $phone         = $_POST['contactnumber'];
    $birthday      = $_POST['dob'];
    $province      = $_POST['province'];
    $municipality  = $_POST['municipality'];
    $barangay      = $_POST['barangay'];
    $address       = $_POST['address'];
    $gender        = $_POST['gender'];

    // Update query
    $stmt = $conn->prepare("UPDATE useracc 
        SET firstname=?, middlename=?, lastname=?, email=?, contactnumber=?, birthday=?, province=?, municipality=?, barangay=?, address=?, gender=? 
        WHERE account_number=?");

    $stmt->bind_param(
        "ssssssssssss",
        $firstName,
        $middleName,
        $lastName,
        $email,
        $phone,
        $birthday,
        $province,
        $municipality,
        $barangay,
        $address,
        $gender,
        $accountNumber
    );

    if ($stmt->execute()) {
        $_SESSION['profile_update_success'] = true;
        echo "Profile updated successfully!";
    } else {
        echo "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
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
    $conn->close();

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
<div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashierr') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
    <div class="row justify-content-center">
        <h2>User Profile</h2>
        <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
            <form method="POST" action="">
                <div class="row mb-3">
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
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="" disabled <?= empty($user['gender']) ? 'selected' : ''; ?>>Select Gender</option>
                        <option value="Male" <?= $user['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?= $user['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?= $user['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                </div>
                <div class="row mb-3">   
                    <div class="col-md-12">
                        <label for="address" class="form-label">Address<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" name="address" placeholder="Purok#/Street/Sitio" required> 
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
                        <select class="form-select" id="municipality" value="<?= htmlspecialchars($user['municipality'] ?? '') ?>" name="municipality" required> 
                            <option value="">-- Select Municipality --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="barangay" class="form-label">Barangay<span class="text-danger">*</span></label>
                        <select class="form-select" id="barangay"  value="<?= htmlspecialchars($user['barangay'] ?? '') ?>"name="barangay" required>
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
<script>
<?php if (isset($_SESSION['profile_update_success']) && $_SESSION['profile_update_success']) { ?>
        Swal.fire({
            title: "Success!",
            text: "Profile updated successfully!",
            icon: "success",
            confirmButtonText: "Okay"
        }).then(function() {
            // Optional: Redirect to another page after closing the alert
            window.location.href = "profile.php"; // Example redirect
        });
        <?php unset($_SESSION['profile_update_success']); // Clear the session flag ?>
    <?php } ?>

    $(document).ready(function () {
    // Load provinces on page load
    $(document).ready(function () {
    // Load provinces on page load
    $.ajax({
        url: 'https://psgc.gitlab.io/api/provinces',
        method: 'GET',
        dataType: 'json',
        success: function (data) {
            data.sort((a, b) => a.name.localeCompare(b.name));
            $.each(data, function (index, province) {
                $('#province').append($('<option>', {
                    value: province.code,
                    text: province.name
                }));
            });

            const selectedProvince = '<?= htmlspecialchars(str_pad($user["province"] ?? "", 9, "0", STR_PAD_LEFT)) ?>';
            if (selectedProvince) {
                $('#province').val(selectedProvince).trigger('change');
            }
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
                url: 'https://psgc.gitlab.io/api/cities-municipalities',
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    const municipalities = data.filter(m => m.provinceCode === provinceCode);
                    municipalities.sort((a, b) => a.name.localeCompare(b.name));
                    $.each(municipalities, function (index, municipality) {
                        $('#municipality').append($('<option>', {
                            value: municipality.code,
                            text: municipality.name
                        }));
                    });

                    const selectedMunicipality = '<?= htmlspecialchars(str_pad($user["municipality"] ?? "", 9, "0", STR_PAD_LEFT)) ?>';
                    if (selectedMunicipality) {
                        $('#municipality').val(selectedMunicipality).trigger('change');
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
            $.ajax({
                url: 'https://psgc.gitlab.io/api/barangays',
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    const barangays = data.filter(b => b.municipalityCode === municipalityCode);
                    barangays.sort((a, b) => a.name.localeCompare(b.name));
                    $.each(barangays, function (index, barangay) {
                        $('#barangay').append($('<option>', {
                            value: barangay.code,
                            text: barangay.name
                        }));
                    });

                    const selectedBarangay = '<?= htmlspecialchars(str_pad($user["barangay"] ?? "", 9, "0", STR_PAD_LEFT)) ?>';
                    if (selectedBarangay) {
                        $('#barangay').val(selectedBarangay);
                    }
                },
                error: function () {
                    console.error('Error fetching barangays');
                }
            });
        }
    });
});


    // (keep the rest of your code for municipalities and barangays here)
});

    </script>
</body>
</html>