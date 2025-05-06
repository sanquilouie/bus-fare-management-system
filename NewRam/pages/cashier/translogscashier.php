<?php
session_start();
include '../../includes/connection.php';
include '../../includes/functions.php'; // Include your functions file

// Assuming you have the user id in session
$account_number = $_SESSION['account_number']; // Fetch account number from session
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <title>Transaction Logs</title>
</head>

<body>
    <?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar2.php';
        include '../../includes/footer.php';
    ?>
    <!-- Page Content  -->
    <div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
        <h2>Transaction Logs</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-8 col-xl-8 col-xxl-8">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Account Number</th>
                                <th>User Name</th>
                                <th>Amount</th>
                                <th>Transaction Type</th>
                                <th>Transaction Time</th>
                                <th>Loaded By</th>
                                <th>Role of Loader</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="transactionTableBody"></tbody>
                        
                    </table>
                    
                </div>
                <nav>
                    <ul class="pagination" id="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>
    <script>
$(document).ready(function () {
    function loadTransactions(page = 1) {
        $.ajax({
            url: '../../actions/fetch_translogscashier.php',
            type: 'GET',
            data: { page: page },
            dataType: 'json',
            success: function (response) {
                let transactions = response.transactions;
                let totalPages = response.totalPages;
                let currentPage = response.currentPage;
                let tableBody = $("#transactionTableBody");
                let pagination = $("#pagination");

                tableBody.empty();
                pagination.empty();

                // Populate the transactions table
                transactions.forEach(transaction => {
                    let statusBadge = '';
                    if (transaction.status === 'edited') {
                        statusBadge = `<span class="badge bg-warning text-dark" title="This transaction was edited after submission.">Edited</span>`;
                    } else if (transaction.status === 'notremitted') {
                        statusBadge = `<span class="badge bg-success">Remitted</span>`;
                    } else if (transaction.status === 'remitted') {
                        statusBadge = `<span class="badge bg-success">Remitted</span>`;
                    } else {
                        statusBadge = `<span class="badge bg-light text-dark">Unknown</span>`;
                    }

                    tableBody.append(`
                        <tr>
                            <td>${transaction.account_number}</td>
                            <td>${transaction.firstname} ${transaction.lastname}</td>
                            <td>${transaction.amount}</td>
                            <td>${transaction.transaction_type}</td>
                            <td>${transaction.transaction_date}</td>
                            <td>${transaction.conductor_firstname} ${transaction.conductor_lastname}</td>
                            <td>${transaction.loaded_by_role}</td>
                            <td>${statusBadge}</td>
                        </tr>
                    `);
                });

                // Responsive pagination logic
                function addPageButton(pageNumber, isActive = false) {
                    pagination.append(`
                        <li class="page-item ${isActive ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${pageNumber}">${pageNumber}</a>
                        </li>
                    `);
                }

                function addEllipsis() {
                    pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                }

                // Previous button
                if (currentPage > 1) {
                    pagination.append(`
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                        </li>
                    `);
                }

                let screenWidth = $(window).width();
                let showAll = screenWidth > 768; // Show all pages on larger screens

                if (showAll) {
                    // Full pagination
                    for (let i = 1; i <= totalPages; i++) {
                        addPageButton(i, currentPage === i);
                    }
                } else {
                    // Compact pagination
                    if (currentPage > 2) addPageButton(1); // First page
                    if (currentPage > 3) addEllipsis();

                    let start = Math.max(1, currentPage - 1);
                    let end = Math.min(totalPages, currentPage + 1);

                    for (let i = start; i <= end; i++) {
                        addPageButton(i, currentPage === i);
                    }

                    if (currentPage < totalPages - 2) addEllipsis();
                    if (currentPage < totalPages - 1) addPageButton(totalPages); // Last page
                }

                // Next button
                if (currentPage < totalPages) {
                    pagination.append(`
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                        </li>
                    `);
                }

                // Dropdown for mobile users
                if (screenWidth < 576) {
                    let selectDropdown = `<select id="pageSelect" class="form-select form-select-sm">`;
                    for (let i = 1; i <= totalPages; i++) {
                        selectDropdown += `<option value="${i}" ${i === currentPage ? "selected" : ""}>Page ${i}</option>`;
                    }
                    selectDropdown += `</select>`;
                    pagination.append(`<li class="page-item">${selectDropdown}</li>`);
                }
            }
        });
    }

    // Initial load
    loadTransactions();

    // Handle pagination click
    $(document).on("click", ".page-link", function (e) {
        e.preventDefault();
        let page = $(this).data("page");
        loadTransactions(page);
    });

    // Handle dropdown change (for mobile)
    $(document).on("change", "#pageSelect", function () {
        let page = $(this).val();
        loadTransactions(page);
    });

    // Re-render pagination on window resize
    $(window).resize(function () {
        loadTransactions($(".page-item.active .page-link").data("page") || 1);
    });
});


        </script>
</body>

</html>
