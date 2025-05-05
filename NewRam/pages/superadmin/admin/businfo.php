<?php
session_start();
include '../../../includes/connection.php';


if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../../index.php");
    exit();
}

$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $busNumber = $_POST['busNumber'];
    $plateNumber = $_POST['plateNumber'];
    $capacity = $_POST['capacity'];
    $status = "Available";
    $registrationDate = date("Y-m-d");
    $regTillDate = $_POST['regTillDate'];
    $busModel = $_POST['busModel'];
    $vehicleColor = $_POST['vehicleColor'];

    // Check if the bus number or plate number already exists
    $queryCheck = "SELECT * FROM businfo WHERE bus_number = ? OR plate_number = ?";
    $stmtCheck = $conn->prepare($queryCheck);
    $stmtCheck->bind_param("ss", $busNumber, $plateNumber);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        // If the bus number or plate number exists, display the message
        $existsMessage = "<span style='color: red;'>Bus number or plate number already exists.</span>";
    } else {
        // If no duplicate, proceed to insert data
        $query = "INSERT INTO businfo (bus_number, plate_number, capacity, statusofbus, registration_date, last_service_date, bus_model, vehicle_color, status)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("ssisissss", $busNumber, $plateNumber, $capacity, $status, $registrationDate, $regTillDate, $busModel, $vehicleColor, $status);

            if ($stmt->execute()) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Bus Information Saved Successfully',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = 'businfo.php';
                        });
                    });
                </script>";
            } else {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Error saving bus information',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
                </script>";
            }
            $stmt->close();
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Error preparing query',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            </script>";
        }
    }

    $conn->close();
}

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
    <link rel="stylesheet" href="../../../assets/css/sidebars.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Use full version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>

        .swal2-popup {
            font-size: 1.1rem !important;
            font-family: 'Arial', sans-serif !important;
        }


    </style>
</head>

<body>
<?php
        include '../../../includes/topbar.php';
        include '../../../includes/superadmin_sidebar.php';
        include '../../../includes/footer.php';
    ?>
<div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
            <h2>Bus Registration</h2>
            <form action="" method="POST" id="busInfoForm">
                
                <div class="mb-3">
                    <label for="busNumber" class="form-label">Bus Number</label>
                    <input type="text" class="form-control" id="busNumber" name="busNumber" required>
                    <div id="busNumberMessage"></div> <!-- Message for Bus Number -->
                </div>

                <div class="mb-3">
                    <label for="plateNumber" class="form-label">Plate Number</label>
                    <input type="text" class="form-control" id="plateNumber" name="plateNumber" required>
                    <div id="plateNumberMessage"></div> <!-- Message for Plate Number -->
                </div>

                <div class="mb-3">
                    <label for="capacity" class="form-label">Bus Capacity</label>
                    <input type="number" class="form-control" id="capacity" name="capacity" required>
                </div>

                <div class="mb-3">
                    <label for="regTillDate" class="form-label">Registered Till Date</label>
                    <input type="date" class="form-control" id="regTillDate" name="regTillDate" required>
                </div>

                <div class="mb-3">
                    <label for="busModel" class="form-label">Bus Model</label>
                    <input type="text" class="form-control" id="busModel" name="busModel" required>
                </div>

                <div class="mb-3">
                    <label for="vehicleColor" class="form-label">Vehicle Color</label>
                    <input type="text" class="form-control" id="vehicleColor" name="vehicleColor" required>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary" id="submitButton">Save Bus Information</button>
                </div>
                
            </form>
        </div>
    </div>
</div>


    <script>
        document.getElementById('busInfoForm').addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent form submission

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to save this bus information?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, save it!',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit(); // Submit the form if confirmed
                }
            });
        });

        $(document).ready(function () {
            // Function to check if bus number or plate number exists
            function checkExistence(field, value) {
                $.ajax({
                    url: '../../../actions/validate.php',
                    method: 'POST',
                    data: field + '=' + value,
                    success: function (response) {
                        var messageElement = $("#" + field + "Message");
                        if (response === "exists") {
                            messageElement.html("<span style='color: red;'>Bus number or plate number already exists.</span>");
                            $('#submitButton').prop('disabled', true);
                        } else {
                            messageElement.html(""); // Clear message if not exists
                            $('#submitButton').prop('disabled', false);
                        }
                    }
                });
            }

            // Event listener for bus number input
            $('#busNumber').on('input', function () {
                var busNumber = $(this).val();
                if (busNumber.length > 0) {
                    checkExistence('busNumber', busNumber);
                } else {
                    $("#busNumberMessage").html(""); // Clear message if input is empty
                }
            });

            // Event listener for plate number input
            $('#plateNumber').on('input', function () {
                var plateNumber = $(this).val();
                if (plateNumber.length > 0) {
                    checkExistence('plateNumber', plateNumber);
                } else {
                    $("#plateNumberMessage").html(""); // Clear message if input is empty
                }
            });
        });

        document.getElementById('busNumber').addEventListener('input', function (e) {
            e.target.value = e.target.value.replace(/[^A-Za-z0-9]/g, '');
        });

        document.getElementById('plateNumber').addEventListener('input', function (e) {
            e.target.value = e.target.value.replace(/[^A-Za-z0-9]/g, '');
        });

    </script>
</body>

</html>