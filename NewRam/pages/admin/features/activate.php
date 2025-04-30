<?php
session_start();
ob_start(); // Start output buffering
include '../../../includes/connection.php';


if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../../index.php");
    exit();
}

// Assuming you have the user id in session
$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];

// Pagination settings
$limit = 10; // Number of entries per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Count total number of users
$totalQuery = "SELECT COUNT(*) as total FROM useracc WHERE is_activated = 1 AND role = 'User'";
$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalUsers = $totalRow['total'];
$totalPages = ceil($totalUsers / $limit);

// Fetch users for the current page
$userQuery = "SELECT id, firstname, middlename, lastname, birthday, age, gender, address, account_number, balance, province, municipality, barangay, is_activated 
              FROM useracc WHERE is_activated = 1 AND role = 'User'
              ORDER BY created_at DESC
              LIMIT $start, $limit";
$userResult = mysqli_query($conn, $userQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Fare and Passengers</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../../assets/css/sidebars.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Use full version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>
    <?php
    include '../../../includes/topbar.php';
    include '../../../includes/sidebar2.php';
    include '../../../includes/footer.php';
    ?>

    <div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
        <h2>Registered Users</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-6 col-xxl-10">
                <form method="POST" action="">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="search" name="search" placeholder="Search users...">
                    </div>
                </form>

                <!-- Table for Displaying Users -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                        <caption>List of users</caption>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Firstname</th>
                                    <th>Middlename</th>
                                    <th>Lastname</th>
                                    <th>Birthday</th>
                                    <th>Age</th>
                                    <th>Gender</th>
                                    <th>Account Number</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody">
                                <?php while ($row = mysqli_fetch_assoc($userResult)): ?>
                                    <tr id="user-row-<?php echo $row['id']; ?>">
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['middlename']); ?></td>
                                        <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                                        <td><?php echo date('F j, Y', strtotime($row['birthday'])); ?></td>
                                        <td><?php echo $row['age']; ?></td>
                                        <td><?php echo htmlspecialchars($row['gender']); ?></td>

                                        <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                                        <td>₱<?php echo number_format($row['balance'], 2); ?></td>
                                        <td><?php echo isset($row['is_activated']) ? ($row['is_activated'] == 1 ? 'Activated' : 'Disabled') : 'N/A'; ?>
                                        </td>                              
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php if ($totalPages > 1): ?>
                        <nav aria-label="User pagination" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
         $(document).ready(function () {
                $('#search').on('keyup', function () {
                    var query = $(this).val();
                    $.ajax({
                        url: '../../../actions/search_users.php',
                        method: 'POST',
                        data: { query: query },
                        success: function (data) {
                            $('#userTableBody').html(data);
                        }
                    });
                });
            });
        </script>
</body>
</html>
<?php
ob_end_flush(); // End output buffering
?>