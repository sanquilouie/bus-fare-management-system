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

    // Update the fare settings in the database
    $updateQuery = "UPDATE fare_settings SET base_fare = ?, additional_fare = ?, discount_percentage = ? WHERE id = 1";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ddd", $baseFare, $additionalFare, $discountPercentage); // Bind all three parameters
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
        /* General body styling */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Page heading */
        h1.text-center {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: bold;
            color: transparent;
            background-image: linear-gradient(to right, #f1c40f, #e67e22);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            -webkit-text-stroke: 0.5px black;
        }

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

        /* Submit button */
        form .fare {
            background: linear-gradient(to right, #f1c40f, #e67e22);
            color: white;
            font-size: 16px;
            font-weight: bold;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
        }

        form .fare:hover {
            background: linear-gradient(to right, #f1c40f, #e67e22);
            transform: scale(1.1);
        }

        /* SweetAlert2 styling */
        .swal2-popup {
            font-size: 1.1rem !important;
            font-family: 'Arial', sans-serif !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            h1.text-center {
                font-size: 2rem;
            }

            .container {
                padding: 15px 20px;
            }

            form .btn-primary {
                font-size: 1rem;
                padding: 10px;
            }
        }
    </style>
</head>

<body>

    <?php
   include '../../includes/topbar.php';
   include '../../includes/sidebar.php';
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
    <div id="main-content" class="container mt-5">

        <h1 class="text-center">Fare Settings</h1>

        <form method="POST" class="mt-4">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="base_fare" class="form-label">Base Fare (₱) First 4 Km</label>
                    <input type="number" id="base_fare" name="base_fare" class="form-control" placeholder="14.00"
                        step="0.01" value="<?= htmlspecialchars($fareSettings['base_fare']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="additional_fare" class="form-label">Additional Fare per Km (₱)</label>
                    <input type="number" id="additional_fare" name="additional_fare" class="form-control" step="0.01"
                        value="<?= htmlspecialchars($fareSettings['additional_fare']) ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="discount_percentage" class="form-label">Discount Percentage (%)</label>
                    <input type="number" id="discount_percentage" name="discount_percentage" class="form-control" placeholder="20.00"
                        step="0.01" value="<?= htmlspecialchars($fareSettings['discount_percentage']) ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 offset-md-3">
                    <button type="submit" class="fare btn-lg w-100">Save Fare Settings</button>
                </div>
            </div>
        </form>
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