<?php
session_start();
include '../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../index.php");
    exit();
}

$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];

// Fetch fare settings from the database
$query = "SELECT * FROM fare_settings LIMIT 1";
$result = $conn->query($query);
$fareSettings = $result->fetch_assoc();

// Update fare settings if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $baseFare = $_POST['base_fare'];
    $additionalFare = $_POST['additional_fare'];
    $discountPercentage = $_POST['discount_percentage']; // Get the discount percentage
    $specialPercentage = $_POST['special_percentage'];

    // Update the fare settings in the database
    $updateQuery = "UPDATE fare_settings SET base_fare = ?, additional_fare = ?, discount_percentage = ?, special_percentage = ? WHERE id = 1";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("dddd", $baseFare, $additionalFare, $discountPercentage, $specialPercentage); // Bind all three parameters
    $stmt->execute();
    $stmt->close();

    // Set session variable for SweetAlert success
    $_SESSION['fare_updated'] = true;

    // Reload the page to reflect updated fare settings
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$conn->close();
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
        /* Form labels */
        form .form-label {
            font-size: 1.1rem;
            font-weight: 600;
            color: blue;
        }

        /* Form inputs */
        form .form-control {
            border: 2px solid #f1c40f;
            border-radius: 5px;
            padding: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease-in-out;
            background-color: rgba(255, 255, 255, 0.8);
            color: #333;
        }

        form .form-control:focus {
            border-color: #e67e22;
            box-shadow: 0 0 5px rgba(230, 126, 34, 0.5);
        }

        /* SweetAlert2 styling */
        .swal2-popup {
            font-size: 1.1rem !important;
            font-family: 'Arial', sans-serif !important;
        }
    </style>
</head>

<body>

    <?php
   include '../../includes/topbar.php';
   include '../../includes/sidebar2.php';
   include '../../includes/footer.php';

    // Check if fare settings were updated and show SweetAlert
    if (isset($_SESSION['fare_updated']) && $_SESSION['fare_updated']) {
        echo '<script>
                Swal.fire({
                    title: "Success!",
                    text: "Fare settings have been updated.",
                    icon: "success",
                    confirmButtonText: "Okay"
                });
              </script>';
        unset($_SESSION['fare_updated']);
    }
    ?>
    <div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
                <h2>Fare Settings</h2>
                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="base_fare" class="form-label">Base Fare (₱) First 4 Km</label>
                            <input type="number" id="base_fare" name="base_fare" class="form-control" placeholder="14.00"
                                step="0.01" value="<?= htmlspecialchars($fareSettings['base_fare']) ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label for="additional_fare" class="form-label">Additional Fare per Km (₱)</label>
                            <input type="number" id="additional_fare" name="additional_fare" class="form-control" step="0.01"
                                value="<?= htmlspecialchars($fareSettings['additional_fare']) ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label for="discount_percentage" class="form-label">Discount Percentage (%)</label>
                            <input type="number" id="discount_percentage" name="discount_percentage" class="form-control" placeholder="20.00"
                                step="0.01" value="<?= htmlspecialchars($fareSettings['discount_percentage']) ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label for="special_percentage" class="form-label">Special Percentage (%)</label>
                            <input type="number" id="special_percentage" name="special_percentage" class="form-control" placeholder="50.00"
                                step="0.01" value="<?= htmlspecialchars($fareSettings['special_percentage']) ?>" required>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Save Fare Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent the form from submitting immediately

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to update the fares?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, save it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If confirmed, submit the form
                    e.target.submit();
                }
            });
        });
    </script>
    
</body>

</html>