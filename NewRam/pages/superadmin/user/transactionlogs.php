<?php
session_start();
include '../../../includes/connection.php';
include '../../../includes/functions.php';


// Check if the user is logged in
if (!isset($_SESSION['account_number'])) {
    header("Location: ../.././index.php");
    exit();
}

// Get the logged-in user's account number from session
$accountNumber = $_SESSION['account_number'];
$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];
// Pagination setup
$limit = 10; // Number of transactions per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1; // Current page
$offset = ($page - 1) * $limit; // Offset for the SQL query

// Fetch total number of transactions
$totalQuery = "SELECT COUNT(*) AS total FROM transactions t 
               JOIN useracc u ON t.user_id = u.id 
               WHERE u.account_number = '$accountNumber'";
$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalTransactions = $totalRow['total'];
$totalPages = ceil($totalTransactions / $limit); // Total number of pages

// Fetch transactions for the logged-in user with pagination
function fetchTransactions($conn, $accountNumber, $limit, $offset)
{
    $transactionQuery = "SELECT t.id, u.firstname, u.lastname, u.account_number, t.amount, t.transaction_type, t.transaction_date
                         FROM transactions t 
                         JOIN useracc u ON t.user_id = u.id 
                         WHERE u.account_number = '$accountNumber'
                         ORDER BY t.transaction_date DESC
                         LIMIT $limit OFFSET $offset";
    return mysqli_query($conn, $transactionQuery);
}

$transactions = fetchTransactions($conn, $accountNumber, $limit, $offset);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Load Transaction Logs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../../assets/css/sidebars.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Use full version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>
<?php
        include '../../../includes/topbar.php';
        include '../../../includes/superadmin_sidebar.php';
        include '../../../includes/footer.php';
    ?>
    <div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
        <h2 class="text-center">Load Transaction Logs</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
                <table class="table table-bordered mt-4">
                    <thead class="thead-light">
                        <tr>
                            <th>Account Number</th>
                            <th>User Name</th>
                            <th>Amount</th>
                            <th>Transaction Type</th>
                            <th>Transaction Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($transactions) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($transactions)): ?>
                                <tr>
                                    <td><?php echo $row['account_number']; ?></td>
                                    <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                                    <td><?php echo number_format($row['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($row['transaction_type'])); ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($row['transaction_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No transaction records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <nav>
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=1">First</a></li>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a></li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a></li>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $totalPages; ?>">Last</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</body>

</html>