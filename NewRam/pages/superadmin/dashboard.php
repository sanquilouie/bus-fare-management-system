<?php
session_start();
include '../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../index.php");
    exit();
}


// Fetch user count
$userCountQuery = "SELECT COUNT(*) as userCount FROM useracc";
$userCountResult = mysqli_query($conn, $userCountQuery);
$userCountRow = mysqli_fetch_assoc($userCountResult);
$userCount = $userCountRow['userCount'];

$totalRevenueQuery = "SELECT SUM(amount) as totalRevenue FROM revenue WHERE transaction_type = 'debit'";
$totalRevenueResult = mysqli_query($conn, $totalRevenueQuery);
$totalRevenueRow = mysqli_fetch_assoc($totalRevenueResult);
$totalRevenue = $totalRevenueRow['totalRevenue'] ?? 0;

// Fetch monthly revenue data for the chart
$monthlyRevenueQuery = "SELECT MONTH(transaction_date) AS month, SUM(amount) AS total FROM revenue 
                        WHERE transaction_type = 'debit' 
                        GROUP BY MONTH(transaction_date) 
                        ORDER BY MONTH(transaction_date)";
$monthlyRevenueResult = mysqli_query($conn, $monthlyRevenueQuery);

$months = [];
$revenues = [];

while ($row = mysqli_fetch_assoc($monthlyRevenueResult)) {
    $months[] = date('F', mktime(0, 0, 0, $row['month'], 10)); // Convert month number to name
    $revenues[] = $row['total'] ?? 0;
}

// Assuming you have the user id in session
$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];

// Fetch user data
$query = "SELECT firstname, lastname FROM useracc WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($firstname, $lastname);
$stmt->fetch();
// Fetch today's revenue from passenger_logs fare
$todayRevenueQuery = "SELECT SUM(fare) as todayRevenue FROM passenger_logs 
                      WHERE DATE(timestamp) = CURDATE()";
$todayRevenueResult = mysqli_query($conn, $todayRevenueQuery);
$todayRevenueRow = mysqli_fetch_assoc($todayRevenueResult);
$todayRevenue = $todayRevenueRow['todayRevenue'] ?? 0;

$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];
$bus_number = isset($_SESSION['bus_number']) ? $_SESSION['bus_number'] : null;  // Check if bus number is in session
$conductorac = isset($_SESSION['conductor_name']) ? $_SESSION['conductor_name'] : 'unknown conductor account number';
$driverac = isset($_SESSION['driver_name']) ? $_SESSION['driver_name'] : null;  // Check if driver name is in session


$busCountQuery = "SELECT COUNT(*) AS busCount FROM businfo";
$busCountResult = mysqli_query($conn, $busCountQuery);
$busCount = mysqli_fetch_assoc($busCountResult)['busCount'] ?? 0;

$passengerCountQuery = "SELECT COUNT(*) as totalPassengers 
                        FROM passenger_logs
                        WHERE DATE(timestamp) = CURDATE();";

$passengerCountResult = mysqli_query($conn, $passengerCountQuery);
$totalPassengers = mysqli_fetch_assoc($passengerCountResult)['totalPassengers'] ?? 0;

// Fetch metrics
$userCountQuery = "SELECT COUNT(*) as userCount FROM useracc";
$userCountResult = mysqli_query($conn, $userCountQuery);
$userCount = mysqli_fetch_assoc($userCountResult)['userCount'] ?? 0;

$totalRevenueQuery = "SELECT SUM(amount) as totalRevenue 
                      FROM transactions 
                      WHERE transaction_type = 'Load' 
                      AND bus_number = '$bus_number'
                      AND DATE(transaction_date) = CURDATE()";

$totalRevenueResult = mysqli_query($conn, $totalRevenueQuery);
$totalRevenue = mysqli_fetch_assoc($totalRevenueResult)['totalRevenue'] ?? 0;

// Fetch passenger count for today from the database
$passengerCountQuery = "SELECT COUNT(*) as totalPassengers 
                        FROM passenger_logs
                        WHERE DATE(timestamp) = CURDATE() AND bus_number = '$bus_number'";

$passengerCountResult = mysqli_query($conn, $passengerCountQuery);
$totalPassengers = mysqli_fetch_assoc($passengerCountResult)['totalPassengers'] ?? 0;

// Fetch passenger count by date for the chart
$passengerCountByDateQuery = "SELECT DATE(timestamp) as date, COUNT(*) as total 
                              FROM passenger_logs
                              WHERE bus_number = '$bus_number'
                              GROUP BY DATE(timestamp)";

$passengerCountByDateResult = mysqli_query($conn, $passengerCountByDateQuery);
$passengerData = [];
while ($row = mysqli_fetch_assoc($passengerCountByDateResult)) {
    $passengerData[] = $row;
}

// Fetch revenue by date for chart
$revenueByDateQuery = "SELECT DATE(timestamp) as date, SUM(fare) as total 
                       FROM passenger_logs 
                       GROUP BY DATE(timestamp)";
$revenueByDateResult = mysqli_query($conn, $revenueByDateQuery);
$revenueData = [];
while ($row = mysqli_fetch_assoc($revenueByDateResult)) {
    $revenueData[] = $row;
}


$balanceQuery = "SELECT balance FROM useracc WHERE account_number = ?";
$stmt = $conn->prepare($balanceQuery);
$stmt->bind_param('s', $account_number); // Use 's' for string
$stmt->execute();
$stmt->bind_result($balance);
$stmt->fetch();
$stmt->close(); // Close the statement

// Fetch total fare spent by the user
$totalFareQuery = "SELECT SUM(fare) as totalFare FROM passenger_logs WHERE rfid = ?";
$totalFareStmt = $conn->prepare($totalFareQuery);
$totalFareStmt->bind_param('s', $account_number); // Use 's' for string
$totalFareStmt->execute();
$totalFareStmt->bind_result($totalFare);
$totalFareStmt->fetch();
$totalFareStmt->close(); // Close the statement
$totalFare = $totalFare ?? 0;

// Fetch the total number of trips for the user
$totalTripsQuery = "SELECT COUNT(*) as totalTrips FROM passenger_logs WHERE rfid = ?";
$totalTripsStmt = $conn->prepare($totalTripsQuery);
$totalTripsStmt->bind_param('s', $account_number); // Assuming account_number is used in the rfid field
$totalTripsStmt->execute();
$totalTripsStmt->bind_result($totalTrips);
$totalTripsStmt->fetch();
$totalTripsStmt->close(); // Close the statement

$currentDate = date('Y-m-d'); // Get today's date in 'YYYY-MM-DD' format
$recentTripsQuery = "SELECT * FROM passenger_logs WHERE rfid = ? AND DATE(timestamp) = ?";
$recentTripsStmt = $conn->prepare($recentTripsQuery);
$recentTripsStmt->bind_param('ss', $account_number, $currentDate); // Bind account number and current date
$recentTripsStmt->execute();
$recentTripsResult = $recentTripsStmt->get_result(); // Get the result
$recentTripsStmt->close(); // Close the statement
?>
<!doctype html>
<html lang="en">

<head>
    <title>Admin</title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        /* General Dashboard Styling */
        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .dashboard-item,
        .dashboard-items {
            background-color: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            color: #3e64ff;
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
        include '../../includes/superadmin_sidebar.php';
        include '../../includes/footer.php';
        include '../..//includes/loader.php';
    ?>

    <!-- Page Content -->
    <div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
                <div class="dashboard">
                    <div class="dashboard-item">
                        <i class="fas fa-users"></i>
                        <h3>Registered Users</h3>
                        <p><?php echo $userCount; ?></p>
                    </div>
                    <div class="dashboard-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <h3>Total Load(Today)</h3>
                        <p>₱<?php echo number_format($totalRevenue, 2); ?></p>
                    </div>
                    <div class="dashboard-item">
                        <i class="fas fa-car"></i>
                        <h3>Total Bus</h3>
                        <p><?php echo number_format($busCount); ?></p>
                    </div>
                    <div class="dashboard-item">
                        <i class="fas fa-desktop"></i>
                        <h3>Total Passenger Today(All Bus)</h3>
                        <p><?php echo $totalPassengers; ?></p>
                    </div>
                </div>

                <div id="content" class="p-4">
                    <!-- Revenue Chart Card -->
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="card mt-4">
                                <div class="card-body">
                                    <h4 class="card-title">Revenue Chart</h4>
                                    <div class="chart-container">
                                        <canvas id="revenueChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Today's Revenue Chart Card -->
                        <div class="col-md-6 col-12">
                            <div class="card mt-4">
                                <div class="card-body">
                                    <h4 class="card-title">Today's Revenue</h4>
                                    <div class="chart-container">
                                        <canvas id="todayRevenueChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Revenue and Passenger Count Charts -->
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="card mt-4">
                                <div class="card-body">
                                    <h4 class="card-title">Revenue Trends</h4>
                                    <div class="chart-container" id="revenueCharts"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="card mt-4">
                                <div class="card-body">
                                    <h4 class="card-title">Passenger Count Trends</h4>
                                    <div class="chart-container" id="passengerCharts"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>



        // Revenue chart
        const revenueData = <?php echo json_encode($revenueData); ?>;
        const revenueLabels = revenueData.map(item => item.date);
        const revenueValues = revenueData.map(item => item.total);

        // Set up the Revenue Chart
        const revenueOptions = {
            chart: {
                type: 'line',  // Set the chart type
                height: 400,
            },
            series: [{
                name: 'Revenue (₱)',
                data: revenueValues
            }],
            xaxis: {
                categories: revenueLabels,
            },
            stroke: {
                curve: 'smooth',
                width: 3,
            },
            markers: {
                size: 5,
            },
            title: {
                text: 'Revenue Trends',
                align: 'center',
            },
            yaxis: {
                title: {
                    text: 'Revenue (₱)',
                },
                min: 0,
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return '₱' + val.toFixed(2);  // Format the tooltip to show currency
                    }
                }
            },
        };

        // Initialize and render the Revenue chart
        const revenueCharts = new ApexCharts(document.querySelector("#revenueCharts"), revenueOptions);
        revenueCharts.render();

        // Passenger count chart
        const passengerData = <?php echo json_encode($passengerData); ?>;
        const passengerLabels = passengerData.map(item => item.date);
        const passengerCounts = passengerData.map(item => item.total);

        // Set up the Passenger Count Chart
        const passengerOptions = {
            chart: {
                type: 'bar',
                height: 400,
            },
            series: [{
                name: 'Passengers',
                data: passengerCounts
            }],
            xaxis: {
                categories: passengerLabels,
            },
            stroke: {
                curve: 'smooth',
                width: 3,
            },
            markers: {
                size: 5,
            },
            title: {
                text: 'Passenger Count Trends',
                align: 'center',
            },
            yaxis: {
                title: {
                    text: 'Passenger Count',
                },
                min: 0,
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + ' Passengers';  // Show the number of passengers
                    }
                }
            },
        };

        // Initialize and render the Passenger Count chart
        const passengerCharts = new ApexCharts(document.querySelector("#passengerCharts"), passengerOptions);
        passengerCharts.render();

        // Initialize Revenue Chart
        var ctx = document.getElementById('revenueChart').getContext('2d');
        var revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Monthly Revenue',
                    data: <?php echo json_encode($revenues); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Initialize Today's Revenue Chart
        var todayRevenueCtx = document.getElementById('todayRevenueChart').getContext('2d');
        var todayRevenueChart = new Chart(todayRevenueCtx, {
            type: 'bar',
            data: {
                labels: ['Today'],
                datasets: [{
                    label: 'Today\'s Revenue',
                    data: [<?php echo $todayRevenue; ?>],
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        
    </script>

</body>

</html>