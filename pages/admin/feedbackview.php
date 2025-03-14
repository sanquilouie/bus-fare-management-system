<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../index.php");
    exit();
}

// Query to fetch aggregated feedback data (grouped by bus, driver, and conductor)
$aggregatedFeedbackQuery = "
    SELECT 
        bus_number,
        driver_name,
        conductor_name,
        COUNT(*) AS feedback_count,
        AVG(rating) AS average_rating
    FROM passenger_logs
    WHERE feedback IS NOT NULL AND feedback != ''
    GROUP BY bus_number, driver_name, conductor_name
    ORDER BY feedback_count DESC";
$aggregatedFeedbackResult = $conn->query($aggregatedFeedbackQuery);
$aggregatedFeedbacks = [];

while ($row = $aggregatedFeedbackResult->fetch_assoc()) {
    $aggregatedFeedbacks[] = [
        'bus_number' => $row['bus_number'],
        'driver_name' => $row['driver_name'],
        'conductor_name' => $row['conductor_name'],
        'feedback_count' => $row['feedback_count'],
        'average_rating' => $row['average_rating']
    ];
}

?>
<!doctype html>
<html lang="en">

<head>
    <title>Aggregated Feedbacks</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Use full version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 
    <link rel="stylesheet" href="../../assets/css/sidebars.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .recent-trips {
            margin-top: 20px;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #ffffff;
            overflow-y: auto;
            border-left: 1px solid #e0e0e0;
        }


        h2 {
            
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: bold;
            color: transparent;
            /* Make the text color transparent */
            background-image: linear-gradient(to right, #f1c40f, #e67e22);
            background-clip: text;
            -webkit-background-clip: text;
            /* WebKit compatibility */
            -webkit-text-fill-color: transparent;
            /* Ensures only the gradient is visible */
            -webkit-text-stroke: 0.5px black;
            /* Outline effect */
        }

        .pagination {
            justify-content: center;
        }

        .star-rating {
            color: #FFD700;
        }
    </style>
</head>
<body>
<?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar.php';
        include '../../includes/footer.php';
    ?>
    <div id="main-content" class="container mt-4">
        <h2>Feedbacks for Bus, Driver, and Conductor</h2>

        <?php if (!empty($aggregatedFeedbacks)): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Bus Number</th>
                        <th>Driver Name</th>
                        <th>Conductor Name</th>
                        <th>Feedback Count</th>
                        <th>Average Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aggregatedFeedbacks as $feedback): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($feedback['bus_number'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($feedback['driver_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($feedback['conductor_name'] ?? 'N/A'); ?></td>
                            <td><?php echo $feedback['feedback_count']; ?></td>
                            <td>
                                <span class="star-rating">
                                    <?php for ($i = 0; $i < round($feedback['average_rating']); $i++): ?>
                                        &#9733; <!-- Star symbol -->
                                    <?php endfor; ?>
                                </span>
                                (<?php echo number_format($feedback['average_rating'], 1); ?>)
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info text-center">No feedbacks available</div>
        <?php endif; ?>
    </div>
        </div>
</body>
</html>
