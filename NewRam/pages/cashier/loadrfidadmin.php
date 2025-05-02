<?php
session_start();
include '../../includes/connection.php';
include '../../includes/functions.php';

// Assuming you have the user id in session
if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Cashier' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../index.php");
    exit();
}

$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

$account_number = $_SESSION['account_number'];
// Pagination setup
$perPage = 5; // Records per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Count total records
$totalRecordsQuery = "SELECT COUNT(*) AS total FROM transactions t JOIN useracc u ON t.account_number = u.account_number WHERE t.status = 'notremitted' 
      AND t.conductor_id = '$account_number'";
$totalRecordsResult = $conn->query($totalRecordsQuery);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $perPage);

// Fetch paginated data


$sql = "SELECT t.id, 
               t.account_number, 
               CONCAT(u.firstname, ' ', u.lastname) AS name, 
               t.amount,
               t.status
        FROM transactions t
        JOIN useracc u ON t.account_number = u.account_number
        WHERE t.status = 'notremitted' 
          AND t.conductor_id = '$account_number'
        ORDER BY t.transaction_date DESC
        LIMIT $offset, $perPage";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Load User</title>
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
    <script src="/NewRam/assets/js/NFCScanner.js"></script>
    
    <style>
        .btn-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>

<body>

<?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar2.php';
        include '../../includes/footer.php';
    ?>

    <div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
        <h2>Load User</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
                <form id="loadForm" method="POST" onsubmit="return false;">
                    <div class="mb-3">
                        <label for="loadAmount" class="form-label">Enter Load Amount</label>
                        <input type="number" id="loadAmount" name="loadAmount" class="form-control"
                            placeholder="Enter Amount" required>
                    </div>
                    <div class="btn-group mb-3" role="group">
                        <button type="button" class="btn btn-secondary load-button" data-amount="20">₱20</button>
                        <button type="button" class="btn btn-secondary load-button" data-amount="50">₱50</button>
                        <button type="button" class="btn btn-secondary load-button" data-amount="100">₱100</button>
                        <button type="button" class="btn btn-secondary load-button" data-amount="200">₱200</button>
                        <button type="button" class="btn btn-secondary load-button" data-amount="500">₱500</button>
                        <button type="button" class="btn btn-secondary load-button" data-amount="1000">₱1000</button>
                    </div>

                    <button type="button" id="scanRFIDBtn" class="btn btn-primary w-100">Scan Card</button>
                    <input type="hidden" id="user_account_number" name="user_account_number">
                </form>
        <!-- Transactions Table -->
         <div class="table-responsive">
            <table id="transactionTable" class="table table-bordered mt-4">
                <thead class="thead-light">
                    <tr>
                        <th>Transaction #</th>
                        <th>Account #</th>
                        <th>Passenger Name</th>
                        <th>Load Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['account_number']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo number_format($row['amount'], 2); ?></td>
                                <td>
                                    <!-- Disable the Edit button if the status is 'edited' -->
                                    <button class="btn btn-warning btn-sm edit-btn" 
                                        data-account="<?php echo $row['id']; ?>" 
                                        data-amount="<?php echo $row['amount']; ?>"
                                        <?php echo ($row['status'] === 'edited') ? 'disabled' : ''; ?>>
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No transaction records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

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

    <script>
        // Handle pre-defined load amount buttons
        $(document).on('click', '.load-button', function() {
            const amount = $(this).data('amount');
            const currentAmount = parseFloat($('#loadAmount').val()) || 0;
            $('#loadAmount').val(currentAmount + amount);
        });

        //Table Edit button
        $(document).ready(function () {
        $(".edit-btn").click(function () {
            let accountNumber = $(this).data("account");
            let currentAmount = $(this).data("amount");

            Swal.fire({
                title: "Edit Load Amount",
                input: "number",
                inputLabel: "Enter new amount",
                inputValue: currentAmount,
                showCancelButton: true,
                confirmButtonText: "Save",
                showLoaderOnConfirm: true,
                preConfirm: (newAmount) => {
                    if (!newAmount || newAmount <= 0) {
                        Swal.showValidationMessage("Please enter a valid amount.");
                        return false;
                    }
                    return $.ajax({
                        url: "../../actions/update_transactions.php",
                        type: "POST",
                        data: { id: accountNumber, amount: newAmount },
                        dataType: "json"
                    }).then(response => {
                        if (response.success) {
                            return response;
                        } else {
                            Swal.showValidationMessage(response.message);
                        }
                    }).catch(() => {
                        Swal.showValidationMessage("Request failed. Try again.");
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Success!",
                        text: "Amount updated successfully.",
                        icon: "success",
                        timer: 1000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload(); // Reload the page after the update
                    });
                }
            });
        });
    });
    const busNumber = <?= isset($_SESSION['bus_number']) ? json_encode($_SESSION['bus_number']) : 'null' ?>;
        document.getElementById('scanRFIDBtn').addEventListener('click', async () => { 
            try {
                // First, prompt for the user account number
                const { value: userAccountNumber } = await Swal.fire({
                    title: 'Enter Account Number',
                    input: 'text',
                    inputPlaceholder: 'Enter the account number',
                    showCancelButton: true
                });

                // If account number is provided, check if RFID scan is needed
                if (userAccountNumber) {
                    let rfid = userAccountNumber;

                    // If RFID is required, prompt for it
                    if (!userAccountNumber) {
                        const { value: rfidInput } = await Swal.fire({
                            title: 'Scan RFID',
                            input: 'text',
                            inputPlaceholder: 'Enter RFID code',
                            showCancelButton: true,
                            didOpen: () => {
                                const inputField = Swal.getInput();
                                if (inputField) {
                                    activeInput = inputField;  // Track the Swal input
                                    inputField.focus();  // Ensure it has focus
                                }
                            }
                        });

                        rfid = rfidInput;
                    }


                    const loadAmount = document.getElementById('loadAmount').value;

                    // Confirm the load transaction
                    const { isConfirmed } = await Swal.fire({
                        title: 'Confirm Load',
                        text: `You are about to load ₱${loadAmount} to account number ${userAccountNumber}. Do you want to proceed?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, proceed',
                        cancelButtonText: 'Cancel'
                    });

                    // If confirmed, send the request to load balance
                    if (isConfirmed) {
                        const formData = new FormData();
                        formData.append('loadAmount', loadAmount);
                        formData.append('user_account_number', userAccountNumber);
                        if (rfid) formData.append('rfid', rfid);  // Append RFID if it's available

                        const response = await fetch('../../actions/load_balance.php', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            Swal.fire('Success', `Load successful: ${result.success}`, 'success').then(() => {
                                setTimeout(() => {
                                    location.reload();
                                }, 800); // 2 seconds delay before reload
                            });
                        } else {
                            Swal.fire('Error', result.error, 'error');
                        }
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'There was an error processing your request.', 'error');
            }
        });
    </script>

</body>

</html>