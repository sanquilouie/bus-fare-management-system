<?php
include '../includes/connection.php';

$currentYear = date('Y');

$sql = "
SELECT 
    DATE_FORMAT(timestamp, '%Y-%m') AS log_month,
    COUNT(*) AS total_entries
FROM passenger_logs
WHERE YEAR(timestamp) = $currentYear
GROUP BY log_month
ORDER BY log_month
";

$result = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Passenger Logs</title>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>
    <div id="passengerChart" style="max-width: 700px; margin: auto;"></div>

    <script>
        const chartData = <?php echo json_encode($data); ?>;

        const seriesData = chartData.map(item => ({
            x: item.log_month,
            y: parseInt(item.total_entries)
        }));

        const options = {
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
            title: {
                text: 'Monthly Passenger Log Entries',
                align: 'center'
            },
            markers: {
                size: 4
            }
        };

        const chart = new ApexCharts(document.querySelector("#passengerChart"), options);
        chart.render();
    </script>
</body>
</html>
