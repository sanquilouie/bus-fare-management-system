<?php
include "../includes/connection.php";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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

<div class="container mt-5">
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
            $sql = "SELECT bus_number, plate_number, destination, current_stop FROM businfo WHERE status = ' In Transit'";
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

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>
