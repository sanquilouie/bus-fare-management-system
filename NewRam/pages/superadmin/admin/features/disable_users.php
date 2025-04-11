<?php
session_start();
ob_start(); 
include '../../../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../../../index.php");
    exit();
}

// Count total records
$totalQuery = "SELECT COUNT(*) AS total FROM useracc WHERE is_activated = 1";
$totalResult = mysqli_query($conn, $totalQuery);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? null;

    if ($user_id) {
        $disableQuery = "UPDATE useracc SET is_activated = 0 WHERE id = ?";
        $stmt = $conn->prepare($disableQuery);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Error disabling user."]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "User ID is missing."]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disable Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../../../assets/css/sidebars.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Use full version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>
    <?php
        include '../../../../includes/topbar.php';
        include '../../../../includes/superadmin_sidebar.php';
        include '../../../../includes/footer.php';
    ?>
<div id="main-content" class="container-fluid mt-5">
    <h2>Disable Users</h2>
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-8 col-xl-8 col-xxl-8">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Firstname</th>
                            <th>Lastname</th>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
    function loadUsers(page = 1) {
        $.ajax({
            url: '../../../../actions/fetch_disable_users.php',
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
                                <button type="button" class="btn btn-danger disable-user" data-user-id="${user.id}">
                                    Disable
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
    
$(document).on("click", ".disable-user", function () {
    let userId = $(this).data("user-id");

    Swal.fire({
        title: "Are you sure?",
        text: "This will disable the user!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, disable!",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("disable_users.php", { user_id: userId }, function (response) {
                let result = JSON.parse(response);
                if (result.success) {
                    Swal.fire("Disabled!", "The user has been disabled.", "success").then(() => {
                        location.reload(); // Refresh to reflect changes
                    });
                } else {
                    Swal.fire("Error!", result.message, "error");
                }
            });
        }
    });
});

</script>
</html>
<?php
ob_end_flush();
?>
