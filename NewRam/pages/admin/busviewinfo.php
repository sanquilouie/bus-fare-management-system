<?php
session_start();
include '../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../index.php");
    exit();
}

// Assuming the user is logged in and their session is active
$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];


$sql = "
SELECT 
    b.status,
    b.bus_number,
    p.driver_name AS driver_name,
    p.conductor_name AS conductor_name,
    IFNULL(SUM(p.fare), 0) AS total_fare,
    COUNT(p.id) AS total_passengers
FROM 
    businfo b
LEFT JOIN 
    passenger_logs p 
    ON b.bus_number = p.bus_number 
    AND DATE(p.timestamp) = CURDATE()
GROUP BY 
    b.bus_number, b.status, b.driverName, b.conductorName
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Fare and Passengers</title>
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
        h2 {    
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: bold;
            color: transparent;
            /* Make the text color transparent */
            background-image: linear-gradient(to right, #f1c40f, #e67e22);
            background-clip: text;
            -webkit-background-clip: text;
            /* WebKit compatibility */
            -webkit-text-fill-color: transparent;
            /* Ensures only the gradient is visible */
            -webkit-text-stroke: 0.5px black;
            /* Outline effect */
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
        <h2>Bus Fare and Passengers Report for Today</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Bus Number</th>
                        <th>Total Fare Collected Today</th>
                        <th>Number of Passengers</th>
                        <th>Driver</th>
                        <th>Conductor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Display rows
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['bus_number']) . "</td>";
                            echo "<td>₱" . number_format($row['total_fare'], 2) . "</td>";
                            echo "<td>" . $row['total_passengers'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['driver_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['conductor_name']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No data found.</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>

            </div>
        </div>
    </div>
</body>
</html>