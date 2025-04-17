<?php
include '../../includes/connection.php';
session_start();


if (!isset($_SESSION['account_number'])) {
    $_SESSION['error_message'] = "You must be logged in to access this page.";
    header('Location: ../users/index.php'); // Redirect to the dashboard or login page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Information</title>
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
    <script>
        // Countdown timer function
        let countdown = 5;
        function updateTimer() {
            if (countdown > 0) {
                document.getElementById("timer").innerText = countdown;
                countdown--;
            } else {
                countdown = 5; // reset the countdown
                location.reload(); // refresh the page to reload the table
            }
        }

        setInterval(updateTimer, 1000); // update the timer every second
    </script>
</head>
<body>
<?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar2.php';
        include '../../includes/footer.php';
    ?>
<div id="main-content" class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
            <h2>Bus Information - In Transit</h2>
            <p>Updating in <span id="timer">5</span> seconds...</p>

            <!-- Bus Info Table -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Bus Number</th>
                        <th>Plate Number</th>
                        <th>Destination</th>
                        <th>Current Stop</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query to get bus info where status = 'In Transit'
                    $sql = "SELECT bus_number, plate_number, destination, current_stop FROM businfo WHERE status = 'assigned'";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        // Output data of each row
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["bus_number"] . "</td>";
                            echo "<td>" . $row["plate_number"] . "</td>";
                            echo "<td>" . $row["destination"] . "</td>";
                            echo "<td>" . $row["current_stop"] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No buses in transit at the moment.</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>
