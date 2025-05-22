<?php
session_start();
include '../../includes/connection.php';


if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../index.php");
    exit();
}

$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];

// Handle Bus Status Toggle
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggleBusId'])) {
    $busId = $_POST['toggleBusId'];
    $newStatus = $_POST['newStatus'];

    $stmt = $conn->prepare("UPDATE businfo SET statusofbus = ? WHERE bus_id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
    $stmt->bind_param("si", $newStatus, $busId);
    $stmt->execute();
    $stmt->close();

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                text: 'Bus status updated successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = window.location.href;
            });
        });
    </script>";
}

// Handle New Bus Registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bus_number']) && !isset($_POST['toggleBusId'])) {
    $bus_number = $_POST['bus_number'];
    $plate_number = $_POST['plate_number'];
    $capacity = $_POST['capacity'];
    $statusofbus = 'active';
    $last_service_date = $_POST['last_service_date'];
    $bus_model = $_POST['bus_model'];
    $vehicle_color = $_POST['vehicle_color'];
    $status = 'available';

    $stmt = $conn->prepare("INSERT INTO businfo (bus_number, plate_number, capacity, statusofbus, last_service_date, bus_model, vehicle_color, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssss", $bus_number, $plate_number, $capacity, $statusofbus, $last_service_date, $bus_model, $vehicle_color, $status);
    
    if ($stmt->execute()) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    text: 'Bus information saved successfully!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = window.location.href;
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    text: 'Error saving bus information!',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        </script>";
    }
    $stmt->close();
}
?>
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
?>
<div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
            <h2>Bus Management</h2>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registerBusModal">
                Register Bus
            </button>

    <!-- Register Bus Modal -->
    <div class="modal fade" id="registerBusModal" tabindex="-1" aria-labelledby="registerBusModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form action="" method="POST" id="busInfoForm">
            <div class="modal-header">
              <h5 class="modal-title" id="registerBusModalLabel">Register Bus</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label for="bus_number" class="form-label">Bus Number</label>
                    <input type="text" class="form-control" name="bus_number" required>
                </div>
                <div class="col-md-6">
                    <label for="plate_number" class="form-label">Plate Number</label>
                    <input type="text" class="form-control" name="plate_number" required>
                </div>
                <div class="col-md-6">
                    <label for="capacity" class="form-label">Capacity</label>
                    <input type="number" class="form-control" name="capacity" required>
                </div>
                <div class="col-md-6">
                    <label for="last_service_date" class="form-label">Registered Till</label>
                    <input type="date" class="form-control" name="last_service_date" required>
                </div>
                <div class="col-md-6">
                    <label for="bus_model" class="form-label">Bus Model</label>
                    <input type="text" class="form-control" name="bus_model" required>
                </div>
                <div class="col-md-6">
                    <label for="vehicle_color" class="form-label">Vehicle Color</label>
                    <input type="text" class="form-control" name="vehicle_color" required>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="submitButton">Save Bus Information</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Bus List Table -->
    <div class="table-responsive mt-4">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Bus Number</th>
                    <th>Plate Number</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Registered Till</th>
                    <th>Model</th>
                    <th>Color</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $sql = "SELECT * FROM businfo";
                $result = $conn->query($sql);
                $i = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$i}</td>
                        <td>{$row['bus_number']}</td>
                        <td>{$row['plate_number']}</td>
                        <td>{$row['capacity']}</td>
                        <td>" . ucfirst($row['statusofbus']) . "</td>
                        <td>{$row['last_service_date']}</td>
                        <td>{$row['bus_model']}</td>
                        <td>{$row['vehicle_color']}</td>
                        <td>
                            <form method='POST' style='display:inline;'>
                                <input type='hidden' name='toggleBusId' value='{$row['bus_id']}'>
                                <input type='hidden' name='newStatus' value='" . ($row['statusofbus'] == 'active' ? 'inactive' : 'active') . "'>
                                <button type='submit' class='btn btn-sm " . ($row['statusofbus'] == 'active' ? 'btn-success' : 'btn-danger') . "'>
                                    " . ($row['statusofbus'] == 'active' ? 'Activated' : 'Under Maintenance') . "
                                </button>
                            </form>
                        </td>
                    </tr>";
                    $i++;
                }
            ?>
            </tbody>
        </table>
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
                    url: '../../actions/validate.php',
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