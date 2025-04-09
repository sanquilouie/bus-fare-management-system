<?php
session_start();
include '../../includes/connection.php';
include '../../includes/functions.php'; // Include your functions file

// Assuming you have the user id in session
$account_number = $_SESSION['account_number']; // Fetch account number from session

// Fetch user data based on account_number
$query = "SELECT firstname, lastname, role FROM useracc WHERE account_number = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $account_number); // Use the account number for fetching user data
$stmt->execute();
$stmt->bind_result($firstname, $lastname, $role);
$stmt->fetch();
$stmt->close(); // Close the prepared statement after fetching user data

// Pagination Setup
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get current page from query string
$offset = ($page - 1) * $limit; // Calculate the offset

// Fetch total number of transactions for pagination
$totalQuery = "SELECT COUNT(*) AS total FROM transactions t";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit); // Calculate total pages

// Fetch transactions with pagination
function fetchTransactions($conn, $limit, $offset)
{
    $transactionQuery = "SELECT 
    t.account_number AS user_account_number, 
    CONCAT(u.firstname, ' ', u.lastname) AS user_fullname, 
    t.amount, 
    t.transaction_type, 
    t.transaction_date, 
    CONCAT(c.firstname, ' ', c.lastname) AS loaded_by,
    c.role AS loaded_by_role 
    FROM transactions t
    JOIN useracc u ON t.account_number = u.account_number
    LEFT JOIN useracc c ON BINARY TRIM(t.conductor_id) = BINARY TRIM(c.account_number)
    ORDER BY t.transaction_date DESC LIMIT ? OFFSET ?";

    

    $stmt = $conn->prepare($transactionQuery);
    $stmt->bind_param('ii', $limit, $offset); // Bind limit and offset
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    return $result;
}

$transactions = fetchTransactions($conn, $limit, $offset);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Logs</title>
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

    <!-- Page Content  -->
    <div id="main-content" class="container-fluid mt-5">
        <h2>Transaction Logs</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-6 col-xxl-8">
                <!-- Transactions Table -->
                <table id="transactionTable" class="table table-bordered mt-4">
                    <thead class="thead-light">
                        <tr>
                            <th>Account Number</th>
                            <th>User Name</th>
                            <th>Amount</th>
                            <th>Transaction Type</th>
                            <th>Transaction Time</th>
                            <th>Loaded By</th>
                            <th>Role of Loader</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($transactions) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($transactions)): ?>
                                <tr>
                                    <td><?php echo $row['user_account_number']; ?></td>
                                    <td><?php echo htmlspecialchars($row['user_fullname']); ?></td>
                                    <td><?php echo number_format($row['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($row['transaction_type'])); ?></td>
                                    <td><?php echo date('F-d-Y h:i:s A', strtotime($row['transaction_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['loaded_by']); ?></td>
                                    <td><?php echo htmlspecialchars($row['loaded_by_role']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No transaction records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php if ($page == 1) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php if ($page == $totalPages) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</body>
</html>
