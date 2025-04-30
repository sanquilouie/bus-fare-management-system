<?php
session_start();
ob_start();

ini_set('display_errors', 0); // Suppress errors
error_reporting(0);          // Turn off error reporting

include '../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../index.php");
    exit();
}

$currentYear = date('Y');
$currentMonth = date('m');
$currentDay = date('d');
$selectedDayRevenue = 0; // Default value

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedDate = $_POST['date'] ?? date('Y-m-d');
    $selectedYear = date('Y', strtotime($selectedDate));
    $selectedMonth = date('m', strtotime($selectedDate));
    $selectedDay = date('d', strtotime($selectedDate));

    // Query to fetch revenue for a specific day
    $dailyRevenueQuery = "SELECT SUM(fare) AS total_revenue 
                          FROM passenger_logs 
                          WHERE YEAR(timestamp) = '$selectedYear' 
                          AND MONTH(timestamp) = '$selectedMonth'
                          AND DAY(timestamp) = '$selectedDay'"; // Specific day
    $dailyRevenueResult = mysqli_query($conn, $dailyRevenueQuery);

    if ($row = mysqli_fetch_assoc($dailyRevenueResult)) {
        $selectedDayRevenue = $row['total_revenue'] ?? 0;
    }

    header('Content-Type: application/json');
    ob_end_clean(); // Clear any unwanted output
    echo json_encode([
        'selectedDayRevenue' => $selectedDayRevenue
    ]);

    exit;
}

$selectedDayRevenue = $selectedDayRevenue ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Report</title>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>

</head>

<body>
<?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar2.php';
        include '../../includes/footer.php';
    ?>
    <div id="main-content" class="container-fluid mt-5">
        <h2>Revenue Report</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
                <!-- Filter Form -->
                <form id="filterForm" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="date" class="form-label">Select Date</label>
                            <input type="date" id="date" name="date" class="form-control"
                                value="<?php echo "$currentYear-$currentMonth-$currentDay"; ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <button type="button" class="btn btn-danger" id="generatePdfBtn" disabled>Download PDF</button>
                </form>
                <!-- Display Total Revenue -->
                <div class="mt-4" id="revenueDisplay">
                    <h3>Total Revenue</h3>
                    <p>₱<?php echo number_format($selectedDayRevenue, 2); ?></p>
                </div>
                <div id="revenueChartWrapper" style="display: flex; justify-content: flex-start; width: 100%; height: 100%;">
                    <div id="revenueChart" style="width: 100%; max-width: 600px;"></div>
                </div>


            </div>
        </div>
    </div>
    <script>
        // Update the chart using ApexCharts (for a single day)
        function updateChart(dailyRevenue) {
            const labels = ['Revenue'];  // Only one label for the selected day
            const data = [dailyRevenue];

            var options = {
                chart: {
                    type: 'bar',
                    height: 350
                },
                series: [{
                    name: 'Daily Revenue',
                    data: data
                }],
                xaxis: {
                    categories: labels,
                    title: {
                        text: 'Day'
                    }
                },
                yaxis: {
                    title: {
                        text: 'Revenue (₱)'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                fill: {
                    opacity: 0.9
                },
                colors: ['#4bc0c0']
            };

            // Clear previous chart and render a new one
            document.querySelector("#revenueChart").innerHTML = "";
            var chart = new ApexCharts(document.querySelector("#revenueChart"), options);
            chart.render();
        }

        function downloadChartAsPDF() {
        const chartElement = document.querySelector("#revenueChartWrapper");

        // Use html2pdf to generate the PDF from the chart
        const options = {
            filename: 'RevenueChart.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 4, width: 600, height: 350 }, // Adjust size if needed
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        // Apply a wrapper to center the content
        const wrapper = document.createElement('div');
        wrapper.style.textAlign = 'center'; // Center align the content
        wrapper.appendChild(chartElement.cloneNode(true));

        html2pdf().from(wrapper).set(options).save();
    }

    window.onload = function () {
        const initialRevenue = <?php echo json_encode($selectedDayRevenue); ?>;
        updateChart(initialRevenue);
        document.getElementById('generatePdfBtn').disabled = true;
        
        // Attach the download functionality to the button
        document.getElementById('generatePdfBtn').addEventListener('click', function () {
            downloadChartAsPDF();
        });
    };

    // Enable the PDF button after successful form submission
    document.getElementById('filterForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
        })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                document.querySelector('#revenueDisplay p').textContent = `₱${parseFloat(data.selectedDayRevenue).toFixed(2)}`;
                updateChart(data.selectedDayRevenue);

                // Enable the PDF button after data is updated
                document.getElementById('generatePdfBtn').disabled = false;
            })
            .catch(error => console.error('Error updating revenue data:', error));
    });
    </script>
</body>

</html>