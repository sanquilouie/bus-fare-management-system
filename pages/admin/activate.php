<?php
session_start();
ob_start(); // Start output buffering
include '../../includes/connection.php';
include '../../includes/topbar.php';
include '../../includes/sidebar.php';
include '../../includes/footer.php';


if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../index.php");
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


// Activate user action
if (isset($_POST['activate_user'])) {
    $user_id = $_POST['user_id']; // Get the user ID from the form

    if ($user_id) {
        $activateQuery = "UPDATE useracc SET is_activated = 1 WHERE id = ?";
        $stmt = $conn->prepare($activateQuery);
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                logActivity($conn, $user_id, 'Activated', $firstname . ' ' . $lastname);
                $_SESSION['message'] = "User  activated successfully.";
            } else {
                // Error executing statement
                $_SESSION['message'] = "Error activating user.";
            }
            $stmt->close();
        } else {
            // Error preparing statement
            $_SESSION['message'] = "Error preparing statement.";
        }
    } else {
        $_SESSION['message'] = "User ID is missing.";
    }
    header("Location: users.php"); // Redirect to refresh the page
    exit;
}

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAMSTAR - Admin Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/sidebars.css">
    <style>
        .sidebar {
            width: 250px;
            background-color: #ffffff;
            padding: 20px;
            color: #001f3f;
            border-right: 1px solid #e0e0e0;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #ffffff;
            overflow-y: auto;
            border-left: 1px solid #e0e0e0;
        }

        h3 {
            margin-bottom: 20px;
            font-size: 24px;
        }


        .table-responsive {
            overflow-x: auto;
            /* Enable horizontal scrolling */
            -webkit-overflow-scrolling: touch;
            /* Smooth scrolling on iOS */
        }

        .table {
            min-width: 800px;
            /* Set a minimum width for the table */
        }

        .table-container {
            max-height: 520px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .clickable-count {
    text-decoration: none; /* Remove underline */
    color: #007bff; /* Bootstrap primary color */
    font-weight: bold; /* Make the text bold */
    transition: color 0.3s, background-color 0.3s; /* Smooth transition for hover effects */
    padding: 5px; /* Add some padding */
    border-radius: 4px; /* Rounded corners */
}

.clickable-count:hover {
    color: #fff; /* Change text color on hover */
    background-color: #007bff; /* Change background color on hover */
}
h5{
    color: black;
}

.custom-btn {
    padding: 12px 24px; /* Adjust padding for button size */
    font-size: 16px; /* Adjust font size */
}


        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 150px;
            }
        }
    </style>
</head>

<body>

    <!-- Page Content  -->

    <div id="main-content" class="container mt-5">
    <h3>
    <a href="#" id="awaitingActivation" class="clickable-count">
        Awaiting Activation: <?php echo $userActivateCount; ?> (Disabled)
    </a>
</h3>
<h3>
    <a href="#" id="registeredUsers" class="clickable-count">
        Registered Users: <?php echo $userCount; ?> (Activate)
    </a>
</h3>

        <form method="POST" action="">
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="search" name="search" placeholder="Search users...">
              
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
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-striped">
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
                            <th>Action</th>
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
        </div>
        
        <!-- Action Modal -->
<div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex justify-content-center w-100" id="actionModalLabel">Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="accountNumberContainer" style="display: none;">
                    <label for="accountNumberInput">Enter Account Number:</label>
                    <input type="text" id="accountNumberInput" class="form-control" placeholder="Account Number">
                </div>
                <button id="activateBtn" class="btn btn-success custom-btn mx-2" onclick="confirmActivate()">Activate</button>
                <button id="disableBtn" class="btn btn-danger custom-btn mx-2" onclick="confirmDisable()">Disable</button>
                <button id="transferFundsBtn" class="btn btn-warning custom-btn mx-2" onclick="confirmTransferDisable()">Transfer Funds</button>
            </div>
        </div>
    </div>
</div>

                        </div>

        <!-- Confirmation Script -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
        <script>


            $(document).ready(function () {
    // Function to fetch users based on search and filter
    function fetchUsers(search = '', statusFilter = '') {
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
        fetchUsers($(this).val(), $('#statusFilter').val());
    });

    // Trigger fetch on dropdown change
    $('#statusFilter').on('change', function () {
        fetchUsers($('#search').val(), $(this).val());
    });

    // Click event for Awaiting Activation count
    $('#awaitingActivation').on('click', function (e) {
        e.preventDefault(); // Prevent default anchor behavior
        fetchUsers('', 0); // Fetch users with status 0 (not activated)
    });

    // Click event for Registered Users count
    $('#registeredUsers').on('click', function (e) {
        e.preventDefault(); // Prevent default anchor behavior
        fetchUsers('', 1); // Fetch users with status 1 (activated)
    });
});
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
        $('#accountNumberContainer').hide(); // Hide account number input
    } else {
        $('#activateBtn').prop('disabled', false); // Enable 'Activate' if the user is not activated
        $('#disableBtn').prop('disabled', true);
        $('#transferFundsBtn').prop('disabled', true); // Enable 'Disable' if the user is not activated

        // Check if the user has an account number
        var accountNumber = $('#user-row-' + userId + ' td:nth-child(8)').text().trim(); // Assuming account number is in the 8th column
        if (!accountNumber) {
            $('#accountNumberContainer').show(); // Show input for account number
        } else {
            $('#accountNumberContainer').hide(); // Hide input if account number exists
        }
    }
}

            function toggleSidebar(event) {
                event.preventDefault(); // Prevent default action when clicking toggle button
                $('#sidebar').toggleClass('active');
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

            function activateUser(userId, accountNumber) {
        // Create the data to send
        var formData = new FormData();
        formData.append('user_id', userId);
        formData.append('account_number', accountNumber);

        // Send the POST request to the PHP script
        fetch('path/to/your/script.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'activate.php'; // Redirect after alert
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'Try Again'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error!',
                text: 'There was a problem with the request.',
                icon: 'error',
                confirmButtonText: 'Try Again'
            });
        });
    }
    function confirmActivate() {
    var userId = $('#actionModal').data('userId'); // Get the stored user ID
    var accountNumber = $('#accountNumberInput').val(); // Get the account number from the input

    // Set the user_id in the hidden form input for submitting
    $('#actionForm').find('input[name="user_id"]').val(userId);

    // Check if the account number input is visible
    if ($('#accountNumberContainer').is(':visible')) {
        // If the input is visible, it means we need to use the provided account number
        if (!accountNumber) {
            Swal.fire('Error!', 'Please enter an account number.', 'error');
            return; // Exit if no account number is provided
        }
    } else {
        // If the input is not visible, we can proceed without it
        accountNumber = ''; // Set to empty or handle as needed
    }

    // Submit the form to activate the user
    $.ajax({
        url: '../../actions/activate_user.php', // Ensure this points to your activate.php or script
        method: 'POST',
        data: { user_id: userId, account_number: accountNumber }, // Send user ID and account number
        success: function (response) {
            const result = JSON.parse(response); // Parse the JSON response

            if (result.status === 'success') { // Check if the result status is success
                Swal.fire('Activated!', result.message, 'success').then(() => {
                    location.reload(); // Reload the page after successful activation
                });
            } else {
                Swal.fire('Error!', result.message, 'error'); // Show error if activation fails
            }
        },
        error: function () {
            Swal.fire('Error!', 'There was an error activating the user.', 'error');
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
                            url: '../../actions/transfer_and_disabled.php',
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
                            url: '../../actions/disable_user_only.php',
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
                        url: '../../actions/search_users.php',
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