<?php
session_start();
include '../../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../../index.php");
    exit();
}

// Assuming the user is logged in and their session is active
$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];

// Variables for today
$today = date('Y-m-d');

// Query to get all bus numbers
$busQuery = "SELECT DISTINCT bus_number FROM passenger_logs";

if ($stmt = $conn->prepare($busQuery)) {
    $stmt->execute();
    $stmt->bind_result($busNumber);
    $buses = [];

    // Fetch all bus numbers
    while ($stmt->fetch()) {
        $buses[] = $busNumber;
    }
    $stmt->close();
} else {
    echo "<script>alert('Error preparing query for bus numbers');</script>";
}

// Fetch total fare and passenger count for each bus
$busData = [];

foreach ($buses as $bus) {
    // Query to get total fare for today for the bus
    $fareQuery = "SELECT SUM(fare) AS total_fare
                  FROM passenger_logs
                  WHERE bus_number = ? AND DATE(timestamp) = ?";

    if ($stmt = $conn->prepare($fareQuery)) {
        $stmt->bind_param("ss", $bus, $today);
        $stmt->execute();
        $stmt->bind_result($totalFare);
        $stmt->fetch();
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing query for total fare');</script>";
        continue;
    }

    // Query to get the number of passengers who boarded the bus today
    $passengerQuery = "SELECT COUNT(*) AS passenger_count
                       FROM passenger_logs
                       WHERE bus_number = ? AND DATE(timestamp) = ?";

    if ($stmt = $conn->prepare($passengerQuery)) {
        $stmt->bind_param("ss", $bus, $today);
        $stmt->execute();
        $stmt->bind_result($passengerCount);
        $stmt->fetch();
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing query for passenger count');</script>";
        continue;
    }
 // Query to get driver and conductor information
 $driverConductorQuery = "SELECT driverName, conductorName, status 
 FROM businfo
 WHERE bus_number = ?";

if ($stmt = $conn->prepare($driverConductorQuery)) {
$stmt->bind_param("s", $bus);
$stmt->execute();
$stmt->bind_result($driverName, $conductorName, $status);
$stmt->fetch();
$stmt->close();
} else {
echo "<script>alert('Error preparing query for driver and conductor info');</script>";
$driverName = 'N/A';
$conductorName = 'N/A';
$status = 'Unknown';
}

// Store the data for each bus
$busData[] = [
'status' => $status,
'bus_number' => $bus,
'total_fare' => $totalFare,
'passenger_count' => $passengerCount,
'driverName' => $driverName,
'conductorName' => $conductorName
];
}
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
    <link rel="stylesheet" href="../../../assets/css/sidebars.css">

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
    include '../../../includes/topbar.php';
    include '../../../includes/sidebar2.php';
    include '../../../includes/footer.php';
    ?>
    <div id="main-content" class="container-fluid mt-5">
        <h2>Bus Fare and Passengers Report for Today</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-8 col-xl-8 col-xxl-8">
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
                        <?php foreach ($busData as $data): ?>
                            <tr>
                            <td><?php echo htmlspecialchars($data['status']); ?></td>
                                <td><?php echo htmlspecialchars($data['bus_number']); ?></td>
                                <td>₱<?php echo number_format($data['total_fare'], 2); ?></td>
                                <td><?php echo $data['passenger_count']; ?></td>
                                <td><?php echo htmlspecialchars($data['driverName']); ?></td>
                                <td><?php echo htmlspecialchars($data['conductorName']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>