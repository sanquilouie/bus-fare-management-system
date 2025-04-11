<?php
session_start();
ob_start(); // Start output buffering
include '../../../includes/connection.php';


if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../.././index.php");
    exit();
}

// Assuming you have the user id in session
$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];

// Fetch user data
$query = "SELECT firstname, lastname FROM useracc WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($firstname, $lastname);
$stmt->fetch();

function logActivity($conn, $user_id, $action, $performed_by)
{
    $logQuery = "INSERT INTO activity_logs (user_id, action, performed_by) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($logQuery);
    $stmt->bind_param("iss", $user_id, $action, $performed_by);
    $stmt->execute();
    $stmt->close();
}


// Fetch user count
$userActivateCountQuery = "SELECT COUNT(*) as userActivateCount FROM useracc WHERE is_activated = 0"; // Count only non-activated users
$userActivateCountResult = mysqli_query($conn, $userActivateCountQuery);
$userActivateCountRow = mysqli_fetch_assoc($userActivateCountResult);
$userActivateCount = $userActivateCountRow['userActivateCount'];

// Fetch user count
$userCountQuery = "SELECT COUNT(*) as userCount FROM useracc WHERE is_activated = 1";
$userCountResult = mysqli_query($conn, $userCountQuery);
$userCountRow = mysqli_fetch_assoc($userCountResult);
$userCount = $userCountRow['userCount'];

// Fetch recently registered users
$recentUsersQuery = "SELECT * FROM useracc ORDER BY created_at DESC"; // Fetch non-activated users
$recentUsersResult = mysqli_query($conn, $recentUsersQuery);
$userQuery = "SELECT id, firstname, middlename, lastname, birthday, age, gender, address, account_number, balance, province, municipality, barangay, is_activated 
              FROM useracc";

$userResult = mysqli_query($conn, $userQuery);

if (isset($_POST['fetch_users'])) {
    // Initialize the query
    $userQuery = "SELECT id, firstname, middlename, lastname, birthday, age, gender, address, account_number, balance, province, municipality, barangay, is_activated FROM useracc WHERE 1=1";

    // Check if search term is set
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $_POST['search'];
        $userQuery .= " AND (firstname LIKE ? OR middlename LIKE ? OR lastname LIKE ?)";
        $searchTerm = "%$search%";
    }

    // Check if status filter is set
    if (isset($_POST['statusFilter']) && $_POST['statusFilter'] !== '') {
        $statusFilter = $_POST['statusFilter'];
        $userQuery .= " AND is_activated = ?";
    }

    // Prepare the statement
    $stmt = $conn->prepare($userQuery);

    // Bind parameters
    if (isset($search)) {
        if (isset($statusFilter)) {
            $stmt->bind_param('ssi', $searchTerm, $searchTerm, $statusFilter);
        } else {
            $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
        }
    } else if (isset($statusFilter)) {
        $stmt->bind_param('i', $statusFilter);
    }

    $stmt->execute();
    $userResult = $stmt->get_result();

    // Generate the table rows
    $output = '';
    while ($row = $userResult->fetch_assoc()) {
        $output .= '<tr id="user-row-' . $row['id'] . '">';
        $output .= '<td>' . $row['id'] . '</td>';
        $output .= '<td>' . htmlspecialchars($row['firstname']) . '</td>';
        $output .= '<td>' . htmlspecialchars($row['middlename']) . '</td>';
        $output .= '<td>' . htmlspecialchars($row['lastname']) . '</td>';
        $output .= '<td>' . date('F j, Y', strtotime($row['birthday'])) . '</td>';
        $output .= '<td>' . $row['age'] . '</td>';
        $output .= '<td>' . htmlspecialchars($row['gender']) . '</td>';
        $output .= '<td>' . htmlspecialchars($row['address']) . '</td>';
        $output .= '<td>' . htmlspecialchars($row['province']) . '</td>';
        $output .= '<td>' . htmlspecialchars($row['municipality']) . '</td>';
        $output .= '<td>' . htmlspecialchars($row['barangay']) . '</td>';
        $output .= '<td>' . htmlspecialchars($row['account_number']) . '</td>';
        $output .= '<td>₱' . number_format($row['balance'], 2) . '</td>';
        $output .= '<td>' . ($row['is_activated'] == 1 ? 'Activated' : 'Disabled') . '</td>';
        $output .= '<td>
                        <form id="actionForm' . $row['id'] . '" method="POST">
                            <input type="hidden" name="user_id" value="' . $row['id'] . '">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#actionModal"
                                onclick="prepareActions(' . $row['id'] . ', ' . $row['is_activated'] . ')">
                                Action
                            </button>
                        </form>
                    </td>';
        $output .= '</tr>';
    }

    // Return the generated rows
    echo $output;
    exit(); // Stop further execution after handling AJAX request
}

//Table Pagination
$rowsPerPage = 10; // Number of rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get the current page
$offset = ($page - 1) * $rowsPerPage; // Calculate the offset

// Fetch total row count
$totalQuery = "SELECT COUNT(*) AS total FROM useracc";
$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalPages = ceil($totalRow['total'] / $rowsPerPage);

// Fetch paginated data
$userQuery = "SELECT id, firstname, middlename, lastname, birthday, age, gender, address, account_number, balance, province, municipality, barangay, is_activated 
              FROM useracc 
              LIMIT $rowsPerPage OFFSET $offset";
$userResult = mysqli_query($conn, $userQuery);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAMSTAR - Admin Dashboard</title>
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

    <!-- Page Content  -->

    <div id="main-content" class="container-fluid mt-5">
        <h2>Accounts</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-8 col-xl-8 col-xxl-8">
                <h3> Registered Users Awaiting Activation: <?php echo $userActivateCount; ?> Registered Users: <?php echo $userCount; ?> </h3>
                <form method="POST" action="">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="search" name="search" placeholder="Search users...">
                        <select class="form-select" id="statusFilter" name="statusFilter">
                            <option value="">All Users</option>
                            <option value="1">Activated</option>
                            <option value="0">Disabled</option>
                        </select>
                    </div>
                </form>
                <!-- Feedback Message -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-info">
                        <?php
                        echo $_SESSION['message'];
                        unset($_SESSION['message']); // Clear message after displaying
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Table for Displaying Users -->
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Firstname</th>
                                    <th>Middlename</th>
                                    <th>Lastname</th>
                                    <th>Birthday</th>
                                    <th>Age</th>
                                    <th>Gender</th>
                                    <th>Address</th>
                                    <th>Province</th>
                                    <th>Municipality</th>
                                    <th>Barangay</th>
                                    <th>Account Number</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody">
                            <?php while ($row = mysqli_fetch_assoc($userResult)): ?>
                            <tr id="user-row-<?php echo $row['id']; ?>">
                                <td><?= $row['id']; ?></td>
                                <td><?= $row['firstname']; ?></td>
                                <td><?= $row['middlename']; ?></td>
                                <td><?= $row['lastname']; ?></td>
                                <td><?= $row['birthday']; ?></td>
                                <td><?= $row['age']; ?></td>
                                <td><?= $row['gender']; ?></td>
                                <td><?= $row['address']; ?></td>
                                <td id="province-<?php echo $row['id']; ?>">
                                    <?php echo htmlspecialchars($row['province']); ?>
                                </td>
                                <td id="municipality-<?php echo $row['id']; ?>">
                                    <?php echo htmlspecialchars($row['municipality']); ?>
                                </td>
                                <td id="barangay-<?php echo $row['id']; ?>">
                                    <?php echo htmlspecialchars($row['barangay']); ?>
                                </td>
                                <td><?= $row['account_number']; ?></td>
                                <td><?= $row['balance']; ?></td>
                                <td><?= $row['is_activated'] ? 'Yes' : 'No'; ?></td>
                                <td>
                                    <form id="actionForm<?php echo $row['id']; ?>" method="POST">
                                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#actionModal"
                                            onclick="prepareActions(<?php echo $row['id']; ?>, <?php echo $row['is_activated']; ?>)">
                                            Action
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-2">
                        <?php include '../../../includes/pagination.php' ?>
                    </div>
                    <!-- Action Modal -->
                    <div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="actionModalLabel">User Actions</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <button id="activateBtn" class="btn btn-success btn-sm"
                                        onclick="confirmActivate()">Activate</button>
                                    <button id="disableBtn" class="btn btn-danger btn-sm"
                                        onclick="confirmDisable()">Disable</button>
                                    <button id="transferFundsBtn" class="btn btn-warning btn-sm"
                                        onclick="confirmTransferDisable()">Transfer
                                        Funds</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function () {
                // Function to fetch users based on search and filter
                function fetchUsers() {
                    var search = $('#search').val();
                    var statusFilter = $('#statusFilter').val();

                    $.ajax({
                        url: '', // Current file
                        method: 'POST',
                        data: {
                            search: search,
                            statusFilter: statusFilter,
                            fetch_users: true // Indicate that we want to fetch users
                        },
                        success: function (data) {
                            $('#userTableBody').html(data); // Update the table body with the response
                        }
                    });
                }

                // Trigger fetch on search input change
                $('#search').on('keyup', function () {
                    fetchUsers();
                });

                // Trigger fetch on dropdown change
                $('#statusFilter').on('change', function () {
                    fetchUsers();
                });
            });
            function prepareActions(userId, isActivated) {
                // Store the user ID in the modal
                $('#actionModal').data('userId', userId);

                // Enable or disable the buttons based on the user's status
                if (isActivated == 1) {
                    $('#activateBtn').prop('disabled', true); // Disable 'Activate' if the user is already activated
                    $('#disableBtn').prop('disabled', false); // Enable 'Disable' if the user is activated
                } else {
                    $('#activateBtn').prop('disabled', false); // Enable 'Activate' if the user is not activated
                    $('#disableBtn').prop('disabled', true);
                    $('#transferFundsBtn').prop('disabled', true); // Enable 'Disable' if the user is not activated
                }
            }

            $(document).ready(function () {
                var provinceData = {};
                var municipalityData = {};
                var barangayData = {};

                // Function to fetch provinces
                function fetchProvinces() {
                    $.getJSON('https://psgc.gitlab.io/api/provinces/', function (data) {
                        $.each(data, function (index, province) {
                            provinceData[province.code] = province.name; // Map province code to name
                        });
                        updateProvinceNames();
                    }).fail(function () {
                        console.error('Failed to fetch province data');
                    });
                }

                // Function to update province names in the table
                function updateProvinceNames() {
                    $('tbody tr').each(function () {
                        var rowId = $(this).attr('id').split('-')[2];
                        var provinceCode = $('#province-' + rowId).text().trim();

                        // Ensure the province code has 9 digits
                        if (provinceCode.length === 8) {
                            provinceCode = '0' + provinceCode; // Prepend '0' if it has 8 digits
                        }

                        // Update province name based on code
                        if (provinceData[provinceCode]) {
                            $('#province-' + rowId).text(provinceData[provinceCode]);
                        } else {
                            $('#province-' + rowId).text('N/A');
                        }
                    });
                }

                // Function to fetch municipalities
                function fetchMunicipalities() {
                    $.getJSON('https://psgc.gitlab.io/api/municipalities/', function (data) {
                        $.each(data, function (index, municipality) {
                            municipalityData[municipality.code] = municipality.name; // Map municipality code to name
                        });
                        updateMunicipalityNames();
                    }).fail(function () {
                        console.error('Failed to fetch municipality data');
                    });
                }

                // Function to update municipality names in the table
                function updateMunicipalityNames() {
                    $('tbody tr').each(function () {
                        var rowId = $(this).attr('id').split('-')[2];
                        var municipalityCode = $('#municipality-' + rowId).text().trim();

                        // Ensure the municipality code has 9 digits
                        if (municipalityCode.length === 8) {
                            municipalityCode = '0' + municipalityCode; // Prepend '0' if it has 8 digits
                        }

                        // Update municipality name based on code
                        if (municipalityData[municipalityCode]) {
                            $('#municipality-' + rowId).text(municipalityData[municipalityCode]);
                        } else {
                            $('#municipality-' + rowId).text('N/A');
                        }
                    });
                }

                // Function to fetch barangays
                function fetchBarangays() {
                    $.getJSON('https://psgc.gitlab.io/api/barangays/', function (data) {
                        $.each(data, function (index, barangay) {
                            barangayData[barangay.code] = barangay.name; // Map barangay code to name
                        });
                        updateBarangayNames();
                    }).fail(function () {
                        console.error('Failed to fetch barangay data');
                    });
                }

                // Function to update barangay names in the table
                function updateBarangayNames() {
                    $('tbody tr').each(function () {
                        var rowId = $(this).attr('id').split('-')[2];
                        var barangayCode = $('#barangay-' + rowId).text().trim();

                        // Ensure the barangay code has 9 digits
                        if (barangayCode.length === 8) {
                            barangayCode = '0' + barangayCode; // Prepend '0' if it has 8 digits
                        }

                        // Update barangay name based on code
                        if (barangayData[barangayCode]) {
                            $('#barangay-' + rowId).text(barangayData[barangayCode]);
                        } else {
                            $('#barangay-' + rowId).text('N/A');
                        }
                    });
                }

                // Fetch all data
                fetchProvinces();
                fetchMunicipalities();
                fetchBarangays();
            });

            function confirmActivate(userId) {
                var userId = $('#actionModal').data('userId'); // Get stored user ID
                $('#actionForm').find('input[name="user_id"]').val(userId);

                // Move focus to the body or another visible element before closing modal
                document.activeElement.blur();

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to activate this user?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, activate it!',
                    cancelButtonText: 'No, cancel!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../../../actions/activate_user.php',
                            method: 'POST',
                            data: { user_id: userId },
                            success: function (response) {
                                const result = JSON.parse(response);
                                if (result.status === 'success') {
                                    Swal.fire('Success!', 'User has been activated', 'success').then(() => {
                                        $('#actionModal').modal('hide'); // Hide modal after confirmation
                                        $('body').focus(); // Move focus to body after closing modal
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', result.message, 'error');
                                }
                            },
                            error: function () {
                                Swal.fire('Error!', 'There was an error activating the user.', 'error');
                            }
                        });
                    }
                });
            }



            function confirmTransferDisable(userId) {
                $('#actionModal').modal('hide');
                var userId = $('#actionModal').data('userId'); // Get the stored user ID

                // Set the user_id in the hidden form input for submitting
                $('#actionForm').find('input[name="user_id"]').val(userId);

                // Submit the form to activate the user
                $('#actionForm').submit();

                Swal.fire({
                    title: 'Enter New Account Number',
                    input: 'text',
                    inputLabel: 'New Account Number for Fund Transfer',
                    inputPlaceholder: 'Enter account number...',
                    showCancelButton: true,
                    confirmButtonText: 'Confirm',
                    cancelButtonText: 'Cancel',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Please enter a new account number!';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const newAccountNumber = result.value;
                        $.ajax({
                            url: '../../../actions/transfer_and_disabled.php',
                            method: 'POST',
                            data: { user_id: userId, new_account_number: newAccountNumber },
                            success: function (response) {
                                const result = JSON.parse(response);
                                if (result.success) {
                                    $('#userTableBody').html(result.tableData);
                                    Swal.fire('Disabled!', 'User  has been disabled and funds transferred.', 'success').then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', result.message, 'error');
                                }
                            },
                            error: function () {
                                Swal.fire('Error!', 'There was an error disabling the user.', 'error');
                            }
                        });
                    }
                });
            }

            function confirmDisable(userId) {
                var userId = $('#actionModal').data('userId'); // Get the stored user ID

                // Set the user_id in the hidden form input for submitting
                $('#actionForm').find('input[name="user_id"]').val(userId);

                // Submit the form to activate the user
                $('#actionForm').submit();
                Swal.fire({
                    title: 'Disabled Account?',
                    showCancelButton: true,
                    confirmButtonText: 'Confirm',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../../../actions/disable_user_only.php',
                            method: 'POST',
                            data: { user_id: userId },
                            success: function (response) {
                                const result = JSON.parse(response);
                                if (result.success) {
                                    Swal.fire('Disabled!', 'User  has been disabled', 'success').then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', result.message, 'error');
                                }
                            },
                            error: function () {
                                Swal.fire('Error!', 'There was an error disabling the user.', 'error');
                            }
                        });
                    }
                });
            }

            $(document).ready(function () {
                $('#search').on('keyup', function () {
                    var query = $(this).val();
                    $.ajax({
                        url: 'search_users.php',
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