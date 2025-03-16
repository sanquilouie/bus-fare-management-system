<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../../includes/connection.php';
// Check if the user is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['account_number'])) {
    header("Location: ../../index.php");
    exit();
}

$account_number = $_SESSION['account_number'];

// Pagination Setup
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$items_per_page = 10; // Adjust as needed
$offset = ($page - 1) * $items_per_page;

// Fetch total trips count
$totalTripsQuery = "
    SELECT COUNT(*) 
    FROM passenger_logs 
    WHERE rfid = ?";
$totalTripsStmt = $conn->prepare($totalTripsQuery);
if (!$totalTripsStmt) {
    die("Prepare failed: " . $conn->error);
}
$totalTripsStmt->bind_param('s', $account_number);
$totalTripsStmt->execute();
$totalTripsStmt->bind_result($totalTrips);
$totalTripsStmt->fetch();
$totalTripsStmt->close();
$totalPages = ceil($totalTrips / $items_per_page);

// Fetch recent trips from passenger_logs
$recentTripsQuery = "
    SELECT 
        id, timestamp AS trip_date, 
        fare AS amount, 
        from_route, 
        to_route,
        bus_number, 
        rating, 
        feedback
    FROM passenger_logs 
    WHERE rfid = ? 
    ORDER BY timestamp DESC 
    LIMIT ? OFFSET ?";
$recentTripsStmt = $conn->prepare($recentTripsQuery);
if (!$recentTripsStmt) {
    die("Prepare failed: " . $conn->error);
}
$recentTripsStmt->bind_param('sii', $account_number, $items_per_page, $offset);
$recentTripsStmt->execute();
$recentTripsResult = $recentTripsStmt->get_result();
$recentTrips = [];

while ($row = $recentTripsResult->fetch_assoc()) {
    $recentTrips[] = [
        'id' => $row['id'],
        'trip_date' => $row['trip_date'],
        'amount' => $row['amount'],
        'from_route' => $row['from_route'],
        'to_route' => $row['to_route'],
        'bus_number' => $row['bus_number'],
        'rating' => $row['rating'],
        'feedback' => $row['feedback'],
    ];
}
$recentTripsStmt->close();
?>


<!doctype html>
<html lang="en">

<head>
    <title>Recent Trips</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
</head>

<body>
<?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar2.php';
        include '../../includes/footer.php';
    ?>
    <div id="main-content" class="container mt-5">
    <h2>Your Recent Trips</h2>
    <?php if (!empty($recentTrips)): ?>
        <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Fare Amount</th>
                    <th>Bus Number</th>

                    <th>Rating And Feedback</th>


                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentTrips as $trip): ?>
                    <tr>
                        <td><?php echo date('F j, Y, g:i A', strtotime($trip['trip_date'])); ?></td>
                        <td><?php echo htmlspecialchars($trip['from_route']); ?></td>
                        <td><?php echo htmlspecialchars($trip['to_route']); ?></td>
                        <td>₱<?php echo number_format($trip['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($trip['bus_number'] ?? ''); ?></td>

                        <td>
                            <?php if ($trip['rating'] && $trip['feedback']): ?>
                                <!-- If there's already a rating and feedback, display them -->
                                <p><strong>Rating:</strong> <?php echo $trip['rating']; ?> Stars</p>
                                <p><strong>Feedback:</strong> <?php echo htmlspecialchars($trip['feedback']); ?></p>
                            <?php else: ?>
                                <!-- If there's no rating or feedback, show the form -->
                                <form action="user_feedback.php" method="POST">
                                    <input type="hidden" name="trip_id" value="<?php echo $trip['id']; ?>">

                                    <!-- Rating Section -->
                                    <label for="rating_<?php echo $trip['id']; ?>">Rating:</label>
                                    <select name="rating" id="rating_<?php echo $trip['id']; ?>" required>
                                        <option value="1">1 Star</option>
                                        <option value="2">2 Stars</option>
                                        <option value="3">3 Stars</option>
                                        <option value="4">4 Stars</option>
                                        <option value="5">5 Stars</option>
                                    </select><br>

                                    <!-- Feedback Section -->
                                    <label for="feedback_<?php echo $trip['id']; ?>">Feedback:</label>
                                    <textarea name="feedback" id="feedback_<?php echo $trip['id']; ?>" rows="3"
                                        placeholder="Write your feedback here..." required></textarea><br>

                                    <!-- Submit Button -->
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>
                            <?php endif; ?>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
                            </div>
        <!-- Pagination -->
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php else: ?>
        <div class="alert alert-info text-center">No recent trips</div>
    <?php endif; ?>
    </div>
</body>

</html>