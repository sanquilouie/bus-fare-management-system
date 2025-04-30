<?php
include '../includes/connection.php';

$currentYear = date('Y');
$currentMonth = date('n');

$months = [];
$cash_data = [];
$nfc_data = [];

// Build array for months Jan to current month of this year
for ($m = 1; $m <= $currentMonth; $m++) {
    $monthStr = sprintf('%04d-%02d', $currentYear, $m); // Format: 2025-01, 2025-02, etc.
    $months[$monthStr] = ['cash' => 0, 'nfc' => 0];
}

// Fetch monthly totals only for current year
$sql = "
SELECT 
    DATE_FORMAT(remit_date, '%Y-%m') AS remit_month,
    SUM(total_cash) AS total_cash,
    SUM(total_load) AS total_nfc
FROM remit_logs
WHERE YEAR(remit_date) = $currentYear
GROUP BY remit_month
ORDER BY remit_month
";

$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $month = $row['remit_month'];
    if (isset($months[$month])) {
        $months[$month]['cash'] = (float) $row['total_cash'];
        $months[$month]['nfc'] = (float) $row['total_nfc'];
    }
}
$conn->close();

// Build data arrays
foreach ($months as $month => $totals) {
    $date = $month . '-01'; // Use first day of month for x-axis
    $cash_data[] = ["x" => $date, "y" => $totals['cash']];
    $nfc_data[] = ["x" => $date, "y" => $totals['nfc']];
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Remit Logs Monthly Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>
    <h2>Monthly Remittance Revenue: Cash vs NFC</h2>
    <div id="chart"></div>

    <script>
        const chartData = {
            cash: <?php echo json_encode($cash_data); ?>,
            nfc: <?php echo json_encode($nfc_data); ?>
        };

        const options = {
            chart: {
                type: 'bar',
                height: 500,
                zoom: {
                    enabled: true,
                    type: 'x',
                    autoScaleYaxis: true
                }
            },
            plotOptions: {
                bar: {
                    columnWidth: '60%'
                }
            },
            series: [
                { name: 'Cash', data: chartData.cash },
                { name: 'NFC', data: chartData.nfc }
            ],
            xaxis: {
                type: 'datetime',
                labels: {
                    format: 'MMM yyyy'
                },
                title: {
                    text: 'Month'
                }
            },
            yaxis: {
                title: {
                    text: 'Amount (₱)'
                }
            },
            tooltip: {
                x: {
                    format: 'MMM yyyy'
                }
            }
        };

        const chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    </script>
</body>
</html>
