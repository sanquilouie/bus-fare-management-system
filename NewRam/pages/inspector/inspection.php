<?php
session_start();
include '../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Inspector' && $_SESSION['role'] != 'Superadmin')) {
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
    <title>Bus Fare and Passengers</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
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
        h2 {    
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
    </style>
</head>
<body>
    <?php
    include '../../includes/topbar.php';
    include '../../includes/sidebar2.php';
    include '../../includes/footer.php';
    ?>

    <div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>">
        <div class="row justify-content-center">
            <h2>Bus Inspection</h2>
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
                <div class="mb-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by Bus Number">
                </div>

                <div id="busTableContainer"></div>
            </div>
        </div>
    </div>

    <!-- JS Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Live Search Script -->
    <script>
    $(document).ready(function () {
        function fetchData(query = '') {
            $.ajax({
                url: '../../actions/search_bus.php',
                method: 'GET',
                data: { search: query },
                success: function (data) {
                    $('#busTableContainer').html(data);
                }
            });
        }

        fetchData(); // initial load

        $('#searchInput').on('keyup', function () {
            const query = $(this).val();
            fetchData(query);
        });
    });

    $(document).on('click', '.inspect-btn', function () {
        const busNumber = $(this).data('bus');
        const driver = $(this).data('driver');
        const conductor = $(this).data('conductor');
        const passengers = $(this).data('passengers');

        Swal.fire({
            title: 'Inspect Bus',
            html: `
                <p><strong>Bus No:</strong> ${busNumber}</p>
                <p><strong>Passengers:</strong> ${passengers}</p>
                <p><strong>Driver:</strong> ${driver}</p>
                <p><strong>Conductor:</strong> ${conductor}</p>

                <label for="issue" style="display:block; margin-top: 15px; margin-bottom: 5px; font-weight: 600;">Select Driver Violation:</label>
                <select id="driver_issue" class="swal2-select" style="width: 80%; padding: 8px; font-size: 14px;">
                    <option value="" disabled selected>-- Select an issue --</option>
                    <option>None</option>
                    <option>Reckless Driving</option>
                    <option>Seatbelt Violation</option>
                    <option>Driver Misconduct</option>
                    <option>Unlicensed Driving</option>
                    <option>Driving Negligence</option>
                </select>

                <label for="issue" style="display:block; margin-top: 15px; margin-bottom: 5px; font-weight: 600;">Select Conductor Violation:</label>
                <select id="conductor_issue" class="swal2-select" style="width: 80%; padding: 8px; font-size: 14px;">
                    <option value="" disabled selected>-- Select an issue --</option>
                    <option>None</option>
                    <option>Collecting Fare Without Ticket</option>
                    <option>Improper Pickup/Dropoff</option>
                    <option>Late Ticket Issuance</option>
                    <option>Unauthorized Free Fare</option>
                    <option>Invalid Discount</option>
                </select>

                <label for="remarks" style="display:block; margin-top: 15px; margin-bottom: 5px; font-weight: 600;">Remarks:</label>
                <textarea id="remarks" class="swal2-textarea" placeholder="Additional remarks..." style="width: 80%; min-height: 80px; padding: 8px; font-size: 14px; resize: vertical;"></textarea>
                `,

            showCancelButton: true,
            confirmButtonText: 'Submit Inspection',
            preConfirm: () => {
                const driver_issue = $('#driver_issue').val();
                const conductor_issue = $('#conductor_issue').val();
                const remarks = $('#remarks').val();

                if (!driver_issue) {
                    Swal.showValidationMessage('Please select a driver violation');
                    return false;
                }
                if (!conductor_issue) {
                    Swal.showValidationMessage('Please select a conductor violation');
                    return false;
                }

                // Optionally send the data to the backend here
                return { busNumber, passengers, driver, conductor, driver_issue, conductor_issue, remarks };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // TODO: Submit to server with AJAX
                console.log(result.value);

                // Example:
                $.post('../../actions/mark_inspection.php', {
                    bus_no: result.value.busNumber,
                    passengers: result.value.passengers,
                    driver: result.value.driver,
                    conductor: result.value.conductor,
                    driver_issue: result.value.driver_issue,
                    conductor_issue: result.value.conductor_issue,
                    remarks: result.value.remarks
                }, function (response) {
                    Swal.fire('Success', 'Inspection has been recorded.', 'success').then(() => {
                        location.reload(); // refresh to show updated status
                    });
                });
            }
        });
    });

    </script>
</body>
</html>
