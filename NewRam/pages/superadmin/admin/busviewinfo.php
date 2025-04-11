<?php
session_start();
include '../../../includes/connection.php';


if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../.././index.php");
    exit();
}

// Assuming the user is logged in and their session is active
$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];

$rowsPerPage = 10; // Number of rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get the current page
$page = max($page, 1); // Ensure page is at least 1
$offset = ($page - 1) * $rowsPerPage; // Calculate the offset

// Get total number of distinct buses
$totalQuery = "SELECT COUNT(DISTINCT bus_number) AS total FROM passenger_logs";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalPages = ceil($totalRow['total'] / $rowsPerPage);

// Main query with pagination
$query = "SELECT 
            pl.bus_number,
            COALESCE(SUM(pl.fare), 0) AS total_fare,
            COALESCE(COUNT(pl.id), 0) AS passenger_count,
            bi.driverName,
            bi.conductorName,
            bi.status
          FROM passenger_logs pl
          LEFT JOIN businfo bi ON pl.bus_number = bi.bus_number
          WHERE DATE(pl.timestamp) = CURDATE()
          GROUP BY pl.bus_number, bi.driverName, bi.conductorName, bi.status
          LIMIT $rowsPerPage OFFSET $offset";

$result = $conn->query($query);
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
    ?>
    <div id="main-content" class="container-fluid mt-5">
        <h2>Bus Fare and Passengers Report for Today</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-8 col-xl-8 col-xxl-8">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-striped">
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
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['status']); ?></td>
                                <td><?= htmlspecialchars($row['bus_number']); ?></td>
                                <td>₱<?= number_format($row['total_fare'], 2); ?></td>
                                <td><?= $row['passenger_count']; ?></td>
                                <td><?= htmlspecialchars($row['driverName']); ?></td>
                                <td><?= htmlspecialchars($row['conductorName']); ?></td> 
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-2">
                    <?php include '../../../includes/pagination.php' ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>