<?php
include '../includes/connection.php';
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
    <link rel="stylesheet" href="../assets/css/sidebars.css">

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
    <style>
        body {
         background: url('../assets/images/newbus2.jpg') no-repeat center center fixed;
         background-size: cover;
         font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
         margin: 0;
         padding: 0;
         display: flex;
         flex-direction: column;
         min-height: 100vh;
      }
        header {
            background: linear-gradient(to right, rgb(243, 75, 83), rgb(131, 4, 4));
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        header nav ul {
            display: flex;
            /* Make the <ul> a flex container */
            justify-content: flex-start;
            /* Align items to the left */
            padding: 0;
            margin: 0;
            margin-left: 5px;
        }

        header nav ul li a {
            color: white;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 30px;
            background: #f1c40f;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
        }


        header nav ul li a:hover {
            background: #e67e22;
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        header nav ul li a:active {
            background: #f1c40f;
            transform: scale(1);
        }
    </style>
</head>
<body>
<header>
    <nav>
        <ul>
            <li><a href="../../index.php">Home</a></li>
        </ul>
    </nav>
</header>
<div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
            <h2>Bus Information - In Transit</h2>
            <p class="text-center"><strong>Updating in <span id="timer">5</span> seconds...</strong></p>

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
