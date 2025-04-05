<?php
session_start();
include '../../includes/connection.php';
require '../../libraries/fpdf/fpdf.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Cashier' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../index.php");
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../../index.php");
    exit();
}

// Fetch user data
$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];

// Initialize variables
$dailyRevenue = [];
$totalRevenue = 0;
$showWholeMonth = false; // Initialize the variable to prevent the undefined variable warning

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedDate = isset($_POST['selected_date']) ? $_POST['selected_date'] : date('Y-m-d');
    $showWholeMonth = isset($_POST['show_whole_month']); // This will now be set to true or false based on the checkbox

    $selectedMonth = date('m', strtotime($selectedDate));
    $selectedYear = date('Y', strtotime($selectedDate));
    $selectedDay = date('d', strtotime($selectedDate));

    if ($showWholeMonth) {
        $sql = "SELECT DAY(transaction_date) AS day, SUM(amount) AS total_revenue
                FROM transactions
                WHERE transaction_type = 'Load' AND MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?
                GROUP BY DAY(transaction_date)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $selectedMonth, $selectedYear);
    } else {
        $sql = "SELECT SUM(amount) AS total_revenue
                FROM transactions
                WHERE transaction_type = 'Load' AND DATE(transaction_date) = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $selectedDate);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($showWholeMonth) {
        $dailyRevenue = array_fill(1, 31, 0); // Default to 0 for all days
        while ($row = $result->fetch_assoc()) {
            $dailyRevenue[(int) $row['day']] = (float) $row['total_revenue'];
        }
    } else {
        $dailyRevenue[(int) $selectedDay] = 0; // Default to 0 for the selected day
        if ($row = $result->fetch_assoc()) {
            $dailyRevenue[(int) $selectedDay] = (float) $row['total_revenue'];
        }
    }

    $stmt->close();
}

// Calculate total revenue
$totalRevenue = array_sum($dailyRevenue);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAMSTAR - Daily Revenue Report</title>
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
</head>

<body>
    <?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar2.php';
        include '../../includes/footer.php';
    ?>
    
    <div id="main-content" class="container-fluid mt-5">
        <h2>Daily Revenue Report</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-6 col-xxl-8">
                <form method="POST" class="mb-4">
                    <div class="form-group">
                        <label for="selected_date">Select Date:</label>
                        <input type="date" id="selected_date" name="selected_date" class="form-control"
                            value="<?php echo htmlspecialchars($selectedDate); ?>">
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" id="show_whole_month" name="show_whole_month" class="form-check-input" <?php echo $showWholeMonth ? 'checked' : ''; ?>>
                        <label for="show_whole_month" class="form-check-label">Show Whole Month</label>
                    </div>
                    <div class="text-center mt-2">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                </form>
                <p>Total Revenue: <strong><?php echo number_format($totalRevenue, 2); ?></strong></p>
                <div id="chart"></div>
            </div>
        </div>
    </div>    
    <script>
        window.onload = function () {
            const dailyRevenue = <?php echo json_encode(array_values($dailyRevenue)); ?>;
            updateChart(dailyRevenue);
        };

        function updateChart(dailyRevenue) {
            const categories = <?php echo json_encode($showWholeMonth ? range(1, 31) : [$selectedDay]); ?>;

            const options = {
                chart: {
                    type: 'bar',
                    height: 350
                },
                series: [{
                    name: 'Revenue',
                    data: dailyRevenue
                }],
                xaxis: {
                    categories: categories
                },
                title: {
                    text: 'Daily Revenue',
                    align: 'center'
                },
                dataLabels: {
                    enabled: true
                },
                tooltip: {
                    shared: true,
                    intersect: false
                }
            };

            const chart = new ApexCharts(document.querySelector("#chart"), options);
            chart.render();
        }
    </script>
</body>
</html>