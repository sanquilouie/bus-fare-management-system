<?php
session_start();
ob_start(); // Start output buffering
include '../../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../index.php");
    exit();
}

// Fetch all users for fund transfer
$allUsersQuery = "SELECT * FROM useracc WHERE is_activated = 1 ORDER BY created_at DESC";
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
    header("Location: transfer.php"); // Redirect back to the transfer page
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
</head>
<body>
    <?php
        include '../../../includes/topbar.php';
        include '../../../includes/sidebar2.php';
        include '../../../includes/footer.php';
    ?>
    <div class="container mt-5">
        <h3>Transfer Funds</h3>

        <!-- Feedback Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info">
                <?php
                echo $_SESSION['message'];
                unset($_SESSION['message']); // Clear message after displaying
                ?>
            </div>
        <?php endif; ?>

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
                <tbody>
    <?php while ($row = mysqli_fetch_assoc($allUsersResult)): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['firstname']); ?></td>
            <td><?php echo htmlspecialchars($row['lastname']); ?></td>
            <td><?php echo htmlspecialchars($row['account_number']); ?></td>
            <td>
                <button type="button" class="btn btn-warning" onclick="confirmTransferDisable(<?php echo $row['id']; ?>)">Transfer Funds</button>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>
            </table>
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
            $.ajax({
                url: '../../../actions/transfer_and_disabled.php',
                method: 'POST',
                data: { user_id: userId, new_account_number: newAccountNumber },
                success: function (response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        $('#userTableBody').html(result.tableData);
                        // Display the new account number in the success message
                        Swal.fire('Transfered!', `Funds have been transferred successfully to new RFID: ${newAccountNumber}.`, 'success').then(() => {
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
</script>
</html>

<?php
ob_end_flush(); // End output buffering
?>