<?php
session_start();
include '../../../includes/connection.php';


if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Conductor' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../.././index.php");
    exit();
}


// Fetch the remit logs from the database
$query = "SELECT * FROM remit_logs ORDER BY remit_date DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remittance Logs</title>
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
        <h2>Remittance Logs</h2>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <?php if ($result->num_rows > 0): ?>
                    <table class="table table-bordered mt-4">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Bus No</th>
                                <th>Conductor ID</th>
                                <th>Total Load</th>
                                <th>Total Cash</th>
                                <th>Total Deductions</th>
                                <th>Net Amount</th>
                                <th>Remit Date</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['bus_no']; ?></td>
                                    <td><?php echo $row['conductor_id']; ?></td>
                                    <td><?php echo number_format($row['total_load'], 2); ?></td>
                                    <td><?php echo number_format($row['total_cash'], 2); ?></td>
                                    <td><?php echo number_format($row['total_deductions'], 2); ?></td>
                                    <td><?php echo number_format($row['net_amount'], 2); ?></td>
                                    <td><?php echo $row['remit_date']; ?></td>
                                    <td><?php echo $row['created_at']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p>No remittance logs found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>