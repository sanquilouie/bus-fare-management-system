<?php
session_start();
include '../../includes/connection.php';
// Check if the user is logged in or has appropriate permissions to access this form
if (!isset($_SESSION['account_number'])) {
    $_SESSION['error_message'] = "You must be logged in to access this page.";
    header('Location: ../users/index.php'); // Redirect to the dashboard or login page
    exit();
}

$accountNumber = $_SESSION['account_number']; // Get account number from the session

// Fetch the user's points from the database
$stmt = $conn->prepare("SELECT points FROM useracc WHERE account_number = ?");
$stmt->bind_param("s", $accountNumber); // "s" for string binding
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error_message'] = "User not found.";
    header('Location: ../admin/dashboard.php');
    exit();
}

$availablePoints = $user['points'];

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convert Points to Balance</title>
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
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-8 col-xl-8 col-xxl-8">
                <h2>Convert Points to Balance</h2>
            <div class="card-body">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: '<?php echo $_SESSION['success_message'];
                            unset($_SESSION['success_message']); ?>',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    </script>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: '<?php echo $_SESSION['error_message'];
                            unset($_SESSION['error_message']); ?>',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    </script>
                <?php endif; ?>

                <form id="convertPointsForm">
                    <div class="mb-3">
                        <label class="form-label">Available Points:</label>
                        <p class="form-control-plaintext fw-bold">
                            <?php echo htmlspecialchars($availablePoints); ?>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label for="points" class="form-label">Points to Convert:</label>
                        <input type="number" class="form-control" name="points" id="points" min="1"
                            max="<?php echo htmlspecialchars($availablePoints); ?>" required>
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="inputAllPoints()">Input All Points</button>
                    <button type="button" class="btn btn-primary" onclick="confirmConversion()">Convert Points</button>
                </form>

            </div>
        </div>
    </div>
    </div>
    </div>

    <!-- Modal for Updated Balance -->
    <div class="modal fade" id="updatedBalanceModal" tabindex="-1" aria-labelledby="updatedBalanceLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updatedBalanceLabel">Updated Balance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Your new balance is: <strong id="newBalance">₱<?php echo number_format($balance, 2); ?></strong>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        onclick="reloadPage()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>

        function reloadPage() {
            location.reload();  // This will reload the current page
        }
        function inputAllPoints() {
            var availablePoints = <?php echo $availablePoints; ?>;
            $('#points').val(availablePoints);
        }

        function confirmConversion() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to convert your points!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, convert it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    convertPoints();
                }
            });
        }

        function convertPoints() {
            var pointsToConvert = $('#points').val();
            if (pointsToConvert) {
                $.ajax({
                    url: '../../actions/converting_points.php',
                    type: 'POST',
                    data: { points: pointsToConvert },
                    success: function (response) {
                        var result = JSON.parse(response);
                        if (result.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: result.message,
                                timer: 3000,
                                showConfirmButton: false
                            }).then(() => {
                                $('#newBalance').text(result.new_balance);
                                $('#updatedBalanceModal').modal('show');

                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Conversion Failed',
                                text: result.message,
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Conversion Failed',
                            text: 'There was an issue converting your points. Please try again.',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Input',
                    text: 'Please enter a valid number of points.',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        }
    </script>
</body>