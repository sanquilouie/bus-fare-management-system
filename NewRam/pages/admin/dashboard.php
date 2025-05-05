<?php
session_start();
include '../../includes/connection.php';

// Restrict access to Admin and Superadmin roles
if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../index.php");
    exit();
}

if (isset($_SESSION['firstname']) && isset($_SESSION['lastname'])) {
    $firstname = $_SESSION['firstname'];
    $lastname = $_SESSION['lastname'];
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'Guest';
} else {
    // Handle case where session variables are not set
    $firstname = 'Guest';
    $lastname = '';
    $role = 'Guest';
}

// Fetch user count
$userCountQuery = "SELECT COUNT(*) AS userCount FROM useracc";
$userCountResult = mysqli_query($conn, $userCountQuery);
$userCount = mysqli_fetch_assoc($userCountResult)['userCount'] ?? 0;

// Fetch total revenue
$totalRevenueQuery = "
    SELECT 
        SUM(total_load) AS totalLoad, 
        SUM(total_cash) AS totalCash 
    FROM remit_logs 
    WHERE MONTH(remit_date) = MONTH(CURRENT_DATE()) 
      AND YEAR(remit_date) = YEAR(CURRENT_DATE())
";

$totalRevenueResult = mysqli_query($conn, $totalRevenueQuery);
$row = mysqli_fetch_assoc($totalRevenueResult);

$totalLoad = $row['totalLoad'] ?? 0;
$totalCash = $row['totalCash'] ?? 0;
$totalRevenue = $totalLoad + $totalCash;


// Fetch total bus count
$busCountQuery = "SELECT COUNT(*) AS busCount FROM businfo";
$busCountResult = mysqli_query($conn, $busCountQuery);
$busCount = mysqli_fetch_assoc($busCountResult)['busCount'] ?? 0;

// Monthly Revenue Chart
$currentYear = date('Y');
$sql = "
SELECT 
    DATE_FORMAT(remit_date, '%Y-%m-01') AS remit_month,
    SUM(total_cash) AS total_cash,
    SUM(total_load) AS total_nfc
FROM remit_logs
WHERE YEAR(remit_date) = $currentYear
GROUP BY remit_month
ORDER BY remit_month
";

$result = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

$sql = "
SELECT 
    DATE_FORMAT(timestamp, '%Y-%m') AS log_month,
    COUNT(*) AS total_entries
FROM passenger_logs
WHERE YEAR(timestamp) = $currentYear
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
    <title>Admin Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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

    <!-- Main Content -->
    <div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
                <div class="dashboard">
                    <div class="dashboard-item" onclick="window.location.href='features/activate.php';">
                        <i class="fas fa-users fa-2x"></i>
                        <h3>Registered Users</h3>
                        <p><?php echo $userCount; ?></p>
                    </div>
                    <div class="dashboard-item">
                        <i class="fas fa-desktop fa-2x"></i>
                        <h3>Total Terminals</h3>
                        <p>3</p>
                    </div>
                    <div class="dashboard-item" onclick="window.location.href='features/revenue.php';">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                        <h3>Total Revenue</h3>
                        <p>₱<?php echo number_format($totalRevenue, 2); ?></p>
                    </div>
                    <div class="dashboard-item" onclick="window.location.href='features/busviewinfo.php';">
                        <i class="fas fa-car fa-2x"></i>
                        <h3>Total Buses</h3>
                        <p><?php echo $busCount; ?></p>
                    </div>
                </div>
                <div class="dashboard">
                    <div class="dashboard-charts">
                        <h3>Monthly Revenue Chart</h3>
                        <div id="revenueChart"></div>
                    </div>
                    <div class="dashboard-charts" onclick="window.location.href='revenue.php';">
                        <h3>Monthly Passenger Chart</h3>
                        <div id="passengerChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
const chartData = <?php echo json_encode($data); ?>;

const cashSeries = chartData.map(item => ({
    x: item.remit_month,
    y: parseFloat(item.total_cash)
}));

const nfcSeries = chartData.map(item => ({
    x: item.remit_month,
    y: parseFloat(item.total_nfc)
}));

const options = {
    chart: {
        type: 'bar',  // <-- changed from 'line' to 'bar'
        zoom: {
            enabled: true,
            type: 'x',
            autoScaleYaxis: true
        },
        toolbar: {
            show: true,
            tools: {
                zoom: true,
                zoomin: true,
                zoomout: true,
                reset: true
            }
        }
    },
    series: [
        {
            name: 'Total Cash',
            data: cashSeries
        },
        {
            name: 'Total NFC',
            data: nfcSeries
        }
    ],
    xaxis: {
        type: 'datetime',
        labels: {
            format: 'MMMM'  // Shows "January", "February", etc.
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '60%',
            dataLabels: {
                position: 'top'
            }
        }
    }
};

const chartData2 = <?php echo json_encode($data2); ?>;

        const seriesData = chartData2.map(item => ({
            x: item.log_month,
            y: parseInt(item.total_entries)
        }));

        const options2 = {
            chart: {
                type: 'line',
                zoom: {
                    enabled: true,
                    type: 'x',
                    autoScaleYaxis: true
                },
                toolbar: {
                    show: true
                }
            },
            series: [{
                name: 'Passenger Entries',
                data: seriesData
            }],
            xaxis: {
                type: 'datetime',
                labels: {
                    format: 'MMMM'
                }
            },
            stroke: {
                curve: 'smooth'
            },
            markers: {
                size: 4
            }
        };

const chart2 = new ApexCharts(document.querySelector("#passengerChart"), options2);
chart2.render();

const chart = new ApexCharts(document.querySelector("#revenueChart"), options);
chart.render();
</script>

</body>

</html>