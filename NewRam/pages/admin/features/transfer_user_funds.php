<?php
session_start();
ob_start(); // Start output buffering
include '../../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../../index.php");
    exit();
}

// Fetch all users for fund transfer
$allUsersQuery = "SELECT * FROM useracc WHERE is_activated = 1 AND role = 'User' ORDER BY created_at DESC";
$allUsersResult = mysqli_query($conn, $allUsersQuery);

// Handle fund transfer
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    // Additional logic for fund transfer goes here...

    if ($user_id) {
        // Example: Update account number or transfer funds logic
        $_SESSION['message'] = "Funds transferred successfully.";
    } else {
        $_SESSION['message'] = "User  ID is missing.";
    }
    header("Location: transfer_user_funds.php"); // Redirect back to the transfer page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Funds</title>
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
    <script src="/NewRam/assets/js/NFCScanner.js"></script>
</head>
<body>
<?php
    include '../../../includes/topbar.php';
    include '../../../includes/sidebar2.php';
    include '../../../includes/footer.php';
    ?>
    <div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
        <h2>Transfer Funds</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">       
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Firstname</th>
                                <th>Lastname</th>
                                <th>Account Number</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody"></tbody>
                    </table>
                </div>
                <nav>
                    <ul class="pagination" id="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</body>
<script>
    function confirmTransferDisable(userId) {
    $('#actionModal').modal('hide');

    // Set the user_id in the hidden form input for submitting
    $('#actionForm').find('input[name="user_id"]').val(userId);

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

        // Ask for confirmation before submitting
        Swal.fire({
            title: 'Are you sure?',
            text: `Are you sure you want to transfer funds to account number ${newAccountNumber}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Transfer!',
            cancelButtonText: 'No, Cancel'
        }).then((confirmResult) => {
            if (confirmResult.isConfirmed) {
                $.ajax({
                    url: '../../../actions/transfer_and_disabled.php',
                    method: 'POST',
                    data: { user_id: userId, new_account_number: newAccountNumber },
                    success: function (response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Display the new account number in the success message
                            Swal.fire('Transferred!', `Funds have been transferred successfully to new RFID: ${newAccountNumber}.`, 'success').then(() => {
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
});
}
$(document).ready(function () {
    function loadUsers(page = 1) {
        $.ajax({
            url: '../../../actions/fetch_disable_users.php',
            type: 'GET',
            data: { page: page },
            dataType: 'json',
            success: function (response) {
                let users = response.users;
                let totalPages = response.totalPages;
                let currentPage = response.currentPage;
                let tableBody = $("#userTableBody");
                let pagination = $("#pagination");

                // Clear existing data
                tableBody.empty();
                pagination.empty();

                // Populate the user table
                users.forEach(user => {
                    tableBody.append(`
                        <tr>
                            <td>${user.id}</td>
                            <td>${user.firstname}</td>
                            <td>${user.lastname}</td>
                            <td>${user.account_number}</td>
                            <td>
                                <button type="button" class="btn btn-warning" onclick="confirmTransferDisable(${user.id})">
                                    Transfer Funds
                                </button>
                            </td>
                        </tr>
                    `);
                });



                // Previous button
                if (currentPage > 1) {
                    pagination.append(`
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                        </li>
                    `);
                }

                // Numbered page links
                for (let i = 1; i <= totalPages; i++) {
                    pagination.append(`
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `);
                }

                // Next button
                if (currentPage < totalPages) {
                    pagination.append(`
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                        </li>
                    `);
                }
            }
        });
    }

    // Handle pagination click
    $(document).on('click', '.page-link', function (e) {
        e.preventDefault();
        let page = $(this).data('page');
        loadUsers(page);
    });

    // Load the first page initially
    loadUsers();
});
</script>
</html>

<?php
ob_end_flush(); // End output buffering
?>