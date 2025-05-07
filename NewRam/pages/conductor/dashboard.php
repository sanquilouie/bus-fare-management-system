<?php
session_start();
include '../../includes/connection.php';


if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Conductor' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../index.php");
    exit();
}

$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];
$bus_number = isset($_SESSION['bus_number']) ? $_SESSION['bus_number'] : null;  // Check if bus number is in session
$conductorac = isset($_SESSION['conductor_name']) ? $_SESSION['conductor_name'] : 'unknown conductor account number';
$driverac = isset($_SESSION['driver_name']) ? $_SESSION['driver_name'] : null;  // Check if driver name is in session

// Fetch metrics
$userCountQuery = "SELECT COUNT(*) as userCount FROM useracc";
$userCountResult = mysqli_query($conn, $userCountQuery);
$userCount = mysqli_fetch_assoc($userCountResult)['userCount'] ?? 0;

$totalRevenueQuery = "SELECT SUM(amount) as totalRevenue 
                      FROM transactions 
                      WHERE transaction_type = 'Load' 
                      AND bus_number = '$bus_number'
                      AND status != 'edited'
                      AND DATE(transaction_date) = CURDATE()";

$totalRevenueResult = mysqli_query($conn, $totalRevenueQuery);
$totalRevenue = mysqli_fetch_assoc($totalRevenueResult)['totalRevenue'] ?? 0;

// Fetch passenger count for today from the database
$passengerCountQuery = "SELECT COUNT(*) as totalPassengers 
                        FROM passenger_logs
                        WHERE DATE(timestamp) = CURDATE() AND bus_number = '$bus_number'";

$passengerCountResult = mysqli_query($conn, $passengerCountQuery);
$totalPassengers = mysqli_fetch_assoc($passengerCountResult)['totalPassengers'] ?? 0;

// Monthly Revenue Chart
$currentYear = date('Y');
$conductor_id = $_SESSION['account_number'];
$sql = "
SELECT 
    DATE_FORMAT(remit_date, '%Y-%m-01') AS remit_month,
    SUM(total_cash) AS total_cash,
    SUM(total_load) AS total_nfc
FROM remit_logs
WHERE YEAR(remit_date) = $currentYear AND conductor_id = '$conductor_id'
GROUP BY remit_month
ORDER BY remit_month
";

$result = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

// Monthly Passenger Chart
$sql = "
SELECT 
    DATE_FORMAT(timestamp, '%Y-%m') AS log_month,
    COUNT(*) AS total_entries
FROM passenger_logs
WHERE YEAR(timestamp) = $currentYear AND conductor_id = '$conductor_id'
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
    <title>Conductor Dashboard</title>
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
    body{
        background-color: #f8f9fa;
    }
    .dashboard {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 50px;
    }

    .dashboard-item {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        text-align: center;
        color: #333;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .dashboard-charts {
        grid-column: span 2;
        background-color: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .dashboard-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .dashboard-item i {
        font-size: 36px;
        margin-bottom: 10px;
        color: #3e64ff;
    }

    .dashboard-item h3 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 5px;
        color: #495057;
    }

    .dashboard-item p {
        font-size: 16px;
        font-weight: 500;
        margin: 0;
        color: #212529;
    }
    @media (max-width: 768px) {
        .dashboard {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .dashboard-item {
            flex: 1 1 calc(50% - 20px); /* Ensures 2 items per row */
            max-width: calc(50% - 20px);
            min-height: 120px; /* Set a minimum height */
        }

        .dashboard-charts {
            flex: 1 1 100%;
            max-width: 100%;
        }
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

    <div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
            <div class="dashboard">
                <div class="dashboard-item">
                    <div class="card-body">
                        <h3><i class="fas fa-users"></i></h3>
                        <h4>Total Users</h4>
                        <p class="h2"><?php echo $userCount; ?></p>
                    </div>
                </div>
                    <div class="dashboard-item">
                        <div class="card-body">
                        <h3><i class="fas fa-coins"></i></h3>
                        <h4>Total Load(Today)</h4>
                        <p class="h2">₱<?php echo number_format($totalRevenue, 2); ?></p>
                    </div>
                </div>
                    <div class="dashboard-item">
                        <div class="card-body">
                        <h3><i class="fas fa-bus"></i></h3>
                        <h4>Total Transactions Today</h4>
                        <p class="h2"><?php echo $totalPassengers; ?></p>
                    </div>
                </div>
            </div>
        

    <!-- Revenue Chart -->
    <div class="row">
        <div class="col-md-6 col-12">
            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="card-title">Revenue Trends</h4>
                    <div class="chart-container" id="revenueChart"></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-12">
            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="card-title">Passenger Count Trends</h4>
                    <div class="chart-container" id="passengerChart"></div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
<script src="../../assets/js/chartUtils.js"></script>
<script>
const chartData = <?php echo json_encode($data); ?>;
const chartData2 = <?php echo json_encode($data2); ?>;

//Bar Chart
const cashSeries = chartData.map(item => ({
    x: item.remit_month,
    y: parseFloat(item.total_cash)
}));

const nfcSeries = chartData.map(item => ({
    x: item.remit_month,
    y: parseFloat(item.total_nfc)
}));

const options = generateChartOptions({
    type: 'bar',
    series: [
        { name: 'Total Cash', data: cashSeries },
        { name: 'Total NFC', data: nfcSeries }
    ],
    title: 'Monthly Revenue'
});

//Line Chart
const seriesData = chartData2.map(item => ({
    x: item.log_month,
    y: parseInt(item.total_entries)
}));

const options2 = generateChartOptions({
    type: 'line',
    series: [{
        name: 'Passenger Entries',
        data: seriesData
    }],
    xaxisFormat: 'MMMM',
    title: 'Monthly Passenger Logs'
});

const chart = new ApexCharts(document.querySelector("#revenueChart"), options);
chart.render();

const chart2 = new ApexCharts(document.querySelector("#passengerChart"), options2);
chart2.render();
      
</script>

</body>

</html>