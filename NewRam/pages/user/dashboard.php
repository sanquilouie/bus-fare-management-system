<?php
session_start();
include '../../includes/connection.php';

// Check if the user is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['account_number']) || $_SESSION['role'] != 'User') {
    header("Location: ../../index.php");
    exit();
}

// Fetch account number from the session
$account_number = $_SESSION['account_number'];

// Fetch user balance
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
    <title>User Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
    .recent-trips {
            margin-top: 20px;
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
    <div id="main-content" class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
                <div class="dashboard">
                    <div class="dashboard-item">
                        <i class="fas fa-wallet"></i>
                        <h3>Your Balance</h3>
                        <p>₱<?php echo number_format($balance, 2); ?></p>
                    </div>
                    <div class="dashboard-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <h3>Total Fare Spent</h3>
                        <p>₱<?php echo number_format($totalFare, 2); ?></p>
                    </div>
                    <div class="dashboard-item">
                        <i class="fas fa-car"></i>
                        <h3>Recent Trips</h3>
                        <p><?php echo number_format($totalTrips); ?> Trips</p>
                    </div>
                </div>
                <div class="recent-trips">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Recent Trip History (Today)</h5>
                            <?php if ($recentTripsResult->num_rows > 0): ?>
                                <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>From Route</th>
                                            <th>To Route</th>
                                            <th>Fare</th>
                                            <th>Conductor</th>
                                            <th>Bus Number</th>
                                            <th>Transaction Number</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($trip = $recentTripsResult->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($trip['from_route']); ?></td>
                                                <td><?php echo htmlspecialchars($trip['to_route']); ?></td>
                                                <td>₱<?php echo number_format($trip['fare'], 2); ?></td>
                                                <td><?php echo htmlspecialchars($trip['conductor_name']); ?></td>
                                                <td><?php echo htmlspecialchars($trip['bus_number']); ?></td>
                                                <td><?php echo htmlspecialchars($trip['transaction_number']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                        </div>
                            <?php else: ?>
                                <p>No recent trips available for today.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function loadContent(page) {
            $.ajax({
                url: page,
                method: 'GET',
                success: function (data) {
                    $('#main-content').html(data);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Failed to load content:', textStatus, errorThrown);
                    alert('Failed to load content: ' + errorThrown);
                }
            });
        }
    </script>

</body>

</html>