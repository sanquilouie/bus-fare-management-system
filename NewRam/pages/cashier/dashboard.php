<?php
session_start();
include '../../includes/connection.php';


if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Cashier' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../index.php");
    exit();
}

// Fetch user count
$userCountQuery = "SELECT COUNT(*) as userCount FROM useracc";
$userCountResult = mysqli_query($conn, $userCountQuery);
$userCountRow = mysqli_fetch_assoc($userCountResult);
$userCount = $userCountRow['userCount'];

$totalRevenueQuery = "SELECT SUM(amount) AS totalRevenue FROM transactions WHERE transaction_type = 'Load'";
$totalRevenueResult = mysqli_query($conn, $totalRevenueQuery);
$totalRevenue = mysqli_fetch_assoc($totalRevenueResult)['totalRevenue'] ?? 0;

// Fetch today's revenue
$todayRevenueQuery = "SELECT SUM(amount) as todayRevenue FROM transactions WHERE transaction_type = 'Load' AND DATE(transaction_date) = CURDATE()";
$todayRevenueResult = mysqli_query($conn, $todayRevenueQuery);
$todayRevenueRow = mysqli_fetch_assoc($todayRevenueResult);
$todayRevenue = $todayRevenueRow['todayRevenue'] ?? 0;

// Assuming you have the user id in session
$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];

$pastMonthsLoadQuery = "
    SELECT 
        MONTHNAME(transaction_date) as month, 
        SUM(amount) as totalLoad 
    FROM transactions 
    WHERE transaction_type = 'Load' 
    AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) 
    GROUP BY YEAR(transaction_date), MONTH(transaction_date)
    ORDER BY transaction_date ASC";
$pastMonthsLoadResult = mysqli_query($conn, $pastMonthsLoadQuery);

$pastMonths = [];
$totalLoads = [];
while ($row = mysqli_fetch_assoc($pastMonthsLoadResult)) {
    $pastMonths[] = $row['month'];
    $totalLoads[] = $row['totalLoad'];
}

$monthsJson = json_encode($pastMonths);
$loadsJson = json_encode($totalLoads);

$currentYear = date('Y');

$sql = "
SELECT 
    DATE_FORMAT(transaction_date, '%Y-%m') AS log_month,
    SUM(amount) AS total_entries
FROM transactions
WHERE YEAR(transaction_date) = $currentYear
GROUP BY log_month
ORDER BY log_month
";

$result2 = mysqli_query($conn, $sql);

$data2 = [];
while ($row = mysqli_fetch_assoc($result2)) {
    $data2[] = $row;
}
?>

<!doctype html>
<html lang="en">

<head>
    <title>Cashier Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
        /* Dashboard Grid */
        .dashboard {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .dashboard-item {
            background-color: #007bff;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
            color: white;
        }

        .dashboard-item i {
            font-size: 40px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar2.php';
        include '../../includes/footer.php';
        include '../..//includes/loader.php';
    ?>

    <!-- Page Content -->
    <div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="card mt-4">
                            <div class="card-body">
                                <h4 class="card-title">Monthly Total Load</h4>
                                <div class="chart-container" id="passengerChart"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="card mt-4">
                            <div class="card-body">
                                <h4 class="card-title">Today's Total Load</h4>
                                <div class="chart-container" id="revenueChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>  
        </div>
    </div>
    <script src="../../assets/js/chartUtils.js"></script>
    <script>
        const todayRevenue = <?php echo json_encode($todayRevenue); ?>;
        const chartData2 = <?php echo json_encode($data2); ?>;

        //Bar Chart
        const options = generateChartOptions({
            type: 'bar',
            //title: "Today's Total Load",
            series: [{
                name: "Revenue",
                data: [{
                    x: "Today",
                    y: parseFloat(todayRevenue)
                }]
            }]
        });

        //Line Chart
        const seriesData = chartData2.map(item => ({
            x: item.log_month,
            y: parseInt(item.total_entries)
        }));

        const options2 = generateChartOptions({
            type: 'line',
            series: [{
                name: 'Total Load',
                data: seriesData
            }],
            xaxisFormat: 'MMMM',
            //title: 'Monthly Passenger Logs'
        });

        const chart = new ApexCharts(document.querySelector("#revenueChart"), options);
        chart.render();

        const chart2 = new ApexCharts(document.querySelector("#passengerChart"), options2);
        chart2.render();
    </script>
</body>

</html>
