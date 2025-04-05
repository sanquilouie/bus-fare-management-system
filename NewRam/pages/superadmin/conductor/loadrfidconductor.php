<?php
session_start();
include '../../../includes/connection.php';
include '../../../includes/functions.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Conductor' && $_SESSION['role'] != 'Superadmin')) {
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

// Fetch user count
$userCount = fetchUserCount($conn);

$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

if (!isset($_SESSION['bus_number']) || !isset($_SESSION['driver_account_number'])) {
    echo json_encode(['error' => 'Bus number or conductor not set in session.']);
    exit;
}

$bus_number = $_SESSION['bus_number']; // Get bus number from session
$conductor_id = $_SESSION['driver_account_number']; // Get conductor ID from session

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

    <div id="main-content" class="container-fluid mt-5">
        <h2>Load User</h2>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h3 class="text-center">Total Users: <span class="badge bg-secondary"><?php echo $userCount; ?></span></h3>
                <div class="mb-4">
                    <form id="searchForm" method="POST">
                        <input type="text" class="form-control" name="account_number" placeholder="Enter Account Number" required autofocus>
                        <input type="hidden" class="form-control" name="search_account" value="1">
                        <div class="text-center mt-2">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>
                </div>

                <div id="searchResult" class="text-center"></div>

                <div class="modal fade" id="loadBalanceModal" tabindex="-1" aria-labelledby="loadBalanceModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="loadBalanceModalLabel">Load Balance</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3 text-center">
                                    <!-- Predefined Load Buttons -->

                                </div>
                                <form id="loadBalanceForm" method="POST">
                                    <div class="mb-3">
                                        <label for="balance" class="form-label">Enter Custom Load Amount</label>
                                        <input type="number" id="balance" name="balance" class="form-control"
                                            placeholder="Enter Load" required>
                                    </div>
                                    <input type="hidden" id="user_account_number" name="user_account_number">
                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <?php
                                        $loadAmounts = [100, 150, 200, 250, 300, 350, 400, 450, 500, 550, 600, 650, 700, 750, 800, 850, 900, 1000];
                                        foreach ($loadAmounts as $amount) {
                                            echo '<button type="button" class="btn btn-outline-primary m-1 load-amount-btn" data-amount="' . $amount . '">₱' . $amount . '</button>';
                                        }
                                        ?>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Load</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="deductBalanceModal" tabindex="-1" aria-labelledby="deductBalanceModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deductBalanceModalLabel">Deduct Balance</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="deductBalanceForm" method="POST">
                                    <div class="mb-3">
                                        <label for="deduct_balance" class="form-label">Balance to Deduct</label>
                                        <input type="number" id="deduct_balance" name="deduct_balance" class="form-control"
                                            placeholder="Enter Amount to Deduct" required>
                                    </div>
                                    <input type="hidden" id="deduct_user_account_number" name="deduct_user_account_number">
                                    <div class="d-flex flex-wrap justify-content-center gap-2">

                                    </div>
                                    <button type="submit" class="btn btn-danger">Deduct</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>

            document.addEventListener('DOMContentLoaded', () => {
                const loadButtons = document.querySelectorAll('.load-amount-btn');
                const balanceInput = document.getElementById('balance');

                loadButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const amount = button.getAttribute('data-amount');
                        balanceInput.value = amount; // Set the value of the input field
                    });
                });
            });

            document.getElementById('searchForm').onsubmit = async (event) => {
                event.preventDefault();
                const formData = new FormData(event.target);

                try {
                    const response = await fetch('../../../actions/search_user.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }

                    const result = await response.json();
                    document.getElementById('searchResult').innerHTML = '';

                    if (result.error) {
                        document.getElementById('searchResult').innerHTML =
                            `<div class="alert alert-danger">${result.error}</div>`;
                    } else {
                        const userInfo = `<div class="card shadow-lg border-light rounded">
    <div class="card-header bg-success text-white text-start">
        <h5 class="mb-0"><strong class="fs-4">${result.success}</strong></h5>
    </div>
    <div class="card-body text-start">
        <ul class="list-unstyled">
            <li class="mb-2">
                <strong class="fs-5">Account Number:</strong> 
                <span class="fs-6 text-muted">${result.user.account_number}</span>
            </li>
            <li class="mb-2">
                <strong class="fs-5">Full Name:</strong> 
                <span class="fs-6 text-muted">${result.user.firstname} ${result.user.middlename} ${result.user.lastname}</span>
            </li>
            <li class="mb-2">
                <strong class="fs-5">Birthday:</strong> 
                <span class="fs-6 text-muted">${result.user.birthday}</span>
            </li>
            <li class="mb-2">
                <strong class="fs-5">Gender:</strong> 
                <span class="fs-6 text-muted">${result.user.gender}</span>
            </li>
            <li class="mb-2">
                <strong class="fs-5">Email:</strong> 
                <span class="fs-6 text-muted">${result.user.email}</span>
            </li>
            <li class="mb-2">
                <strong class="fs-5">Contact Number:</strong> 
                <span class="fs-6 text-muted">${result.user.contactnumber}</span>
            </li>
            <li class="mb-2">
                <strong class="fs-5">Balance:</strong> 
                <span class="fs-6 text-muted">₱${parseFloat(result.user.balance).toFixed(2)}</span>
            </li>
        </ul>
        <!-- Align buttons to the left -->
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#loadBalanceModal"
                    onclick="document.getElementById('user_account_number').value = '${result.user.account_number}';">Load</button>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deductBalanceModal"
                    onclick="document.getElementById('deduct_user_account_number').value = '${result.user.account_number}';">Deduct</button>
        </div>
    </div>
</div>
`;
                        document.getElementById('searchResult').innerHTML = userInfo;
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            };

            function openLoadBalanceModal(accountNumber) {
                document.getElementById('user_account_number').value = accountNumber;
                const modal = new bootstrap.Modal(document.getElementById('loadBalanceModal'));
                modal.show();

            }

            document.getElementById('loadBalanceForm').onsubmit = async (event) => {
                event.preventDefault();

                const balance = document.getElementById('balance').value;

                // Check if the load amount is less than 100 pesos
                if (parseFloat(balance) < 100) {
                    Swal.fire('Error', 'The minimum load amount is ₱100.', 'error');
                    return;
                }

                const formData = new FormData(event.target);

                try {
                    const response = await fetch('../../../actions/load_balance_superadmin.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();
                    if (result.success) {
                        Swal.fire('Success', result.success, 'success').then(() => {
                            // Refresh user info after successful load
                            refreshUserInfo(document.getElementById('user_account_number').value);

                            // Close the modal after successful form submission
                            $('#loadBalanceModal').modal('hide');
                            $('.modal-backdrop').remove(); // This removes the backdrop // Properly hide the modal
                        });
                    } else {
                        Swal.fire('Error', result.error, 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            };

            document.getElementById('deductBalanceForm').onsubmit = async (event) => {
                event.preventDefault();
                const formData = new FormData(event.target);

                try {
                    const response = await fetch('../../../actions/deduct_balance.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();
                    if (result.success) {
                        Swal.fire('Success', result.success, 'success').then(() => {
                            // Refresh user info after successful deduction
                            refreshUserInfo(document.getElementById('deduct_user_account_number').value);

                            $('#deductBalanceModal').modal('hide');
                            $('.modal-backdrop').remove(); // This will close the modal
                        });
                    } else {
                        Swal.fire('Error', result.error, 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            };


            // Function to refresh user information
            async function refreshUserInfo(accountNumber) {
                const formData = new FormData();
                formData.append('account_number', accountNumber);
                formData.append('search_account', '1'); // You might need to adjust this as necessary

                try {
                    const response = await fetch('../../../actions/search_user.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();
                    document.getElementById('searchResult').innerHTML = '';

                    if (result.error) {
                        document.getElementById('searchResult').innerHTML =
                            `<div class="alert alert-danger">${result.error}</div>`;
                    } else {
                        const userInfo = `<div class="card shadow-lg border-light rounded">
    <div class="card-header bg-success text-white text-start">
        <h5 class="mb-0"><strong class="fs-4">${result.success}</strong></h5>
    </div>
    <div class="card-body text-start">
        <ul class="list-unstyled">
            <li class="mb-2">
                <strong class="fs-5">Account Number:</strong> 
                <span class="fs-6 text-muted">${result.user.account_number}</span>
            </li>
            <li class="mb-2">
                <strong class="fs-5">Full Name:</strong> 
                <span class="fs-6 text-muted">${result.user.firstname} ${result.user.middlename} ${result.user.lastname}</span>
            </li>
            <li class="mb-2">
                <strong class="fs-5">Birthday:</strong> 
                <span class="fs-6 text-muted">${result.user.birthday}</span>
            </li>
            <li class="mb-2">
                <strong class="fs-5">Gender:</strong> 
                <span class="fs-6 text-muted">${result.user.gender}</span>
            </li>
            <li class="mb-2">
                <strong class="fs-5">Email:</strong> 
                <span class="fs-6 text-muted">${result.user.email}</span>
            </li>
            <li class="mb-2">
                <strong class="fs-5">Contact Number:</strong> 
                <span class="fs-6 text-muted">${result.user.contactnumber}</span>
            </li>
            <li class="mb-2">
                <strong class="fs-5">Balance:</strong> 
                <span class="fs-6 text-muted">₱${parseFloat(result.user.balance).toFixed(2)}</span>
            </li>
        </ul>
        <!-- Align buttons to the left -->
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#loadBalanceModal"
                    onclick="document.getElementById('user_account_number').value = '${result.user.account_number}';">Load</button>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deductBalanceModal"
                    onclick="document.getElementById('deduct_user_account_number').value = '${result.user.account_number}';">Deduct</button>
        </div>
    </div>
</div>`;
                        document.getElementById('searchResult').innerHTML = userInfo;
                    }
                } catch (error) {
                    console.error('Error refreshing user info:', error);
                }
            }

        </script>
    </div>
    </div>
</body>

</html>