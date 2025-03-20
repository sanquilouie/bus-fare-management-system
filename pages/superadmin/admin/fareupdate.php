<?php
session_start();
include '../../../includes/connection.php';


if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../.././index.php");
    exit();
}

$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];

// Fetch fare settings from the database
$query = "SELECT * FROM fare_settings LIMIT 1";
$result = $conn->query($query);
$fareSettings = $result->fetch_assoc();

// Update fare settings if the form is submitted
$errorMessage = '';

// Update fare settings if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $baseFare = $_POST['base_fare'];
    $additionalFare = $_POST['additional_fare'];

    // Validate minimum fare values
    if ($baseFare < 10.00 || $additionalFare < 2.00) {
        $errorMessage = "Base fare cannot be less than ₱10.00 and additional fare cannot be less than ₱2.00.";
    } else {
        // Update the fare settings in the database
        $updateQuery = "UPDATE fare_settings SET base_fare = ?, additional_fare = ? WHERE id = 1";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("dd", $baseFare, $additionalFare);
        $stmt->execute();
        $stmt->close();

        // Set session variable for SweetAlert success
        $_SESSION['fare_updated'] = true;

        // Reload the page to reflect updated fare settings
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fare Settings</title>
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
</head>

<body>

    <?php
    include '../../../includes/topbar.php';
    include '../../../includes/superadmin_sidebar.php';
    include '../../../includes/footer.php';

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
        <h2>Fare Settings</h2>
        <div class="row justify-content-center">
            <div class="col-md-8">
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
                        <div class="col-12">
                            <span id="error_message" class="text-danger"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 offset-md-3">
                            <button type="submit" id="save_button" class="btn btn-primary btn-lg w-100">Save Fare
                                Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../js/popper.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <script>
        const baseFareInput = document.getElementById('base_fare');
        const additionalFareInput = document.getElementById('additional_fare');
        const saveButton = document.getElementById('save_button');
        const errorMessage = document.getElementById('error_message');

        function validateInputs() {
            const baseFare = parseFloat(baseFareInput.value);
            const additionalFare = parseFloat(additionalFareInput.value);

            if (isNaN(baseFare) || isNaN(additionalFare)) {
                errorMessage.textContent = "Please enter valid numbers.";
                saveButton.disabled = true;
                return;
            }

            if (baseFare < 12.00 || additionalFare < 2.00) {
                errorMessage.textContent = "Base fare cannot be less than ₱10.00 and additional fare cannot be less than ₱2.00.";
                saveButton.disabled = true;
            } else {
                errorMessage.textContent = "";
                saveButton.disabled = false;
            }
        }

        // Add event listeners to inputs
        baseFareInput.addEventListener('input', validateInputs);
        additionalFareInput.addEventListener('input', validateInputs);

        // Initial validation check
        validateInputs();
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>