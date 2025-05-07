<?php
session_start();
include '../../includes/connection.php';


if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Cashier' && $_SESSION['role'] != 'Superadmin' && $_SESSION['role'] != 'Admin')) {
    header("Location: ../../../index.php");
    exit();
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $type = $_POST['type'];
    $target_dir = "../../assets/images/";
    $image = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO features (image, title, description, type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $image, $title, $desc, $type);
        $stmt->execute();

        header("Location: settings.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Image upload failed.</div>";
    }
}

// Handle toggle
if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    $conn->query("UPDATE features SET is_active = NOT is_active WHERE id = $id");
    header("Location: settings.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    // Get image filename first
    $stmt = $conn->prepare("SELECT image FROM features WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($imageName);
    $stmt->fetch();
    $stmt->close();

    // Delete image file from the server
    $imagePath = "../../assets/images/" . $imageName;
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }

    // Delete record from database
    $stmt = $conn->prepare("DELETE FROM features WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: settings.php");
    exit();
}




$limit = 5; // number of features per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total count
$totalResult = $conn->query("SELECT COUNT(*) as total FROM features");
$totalRow = $totalResult->fetch_assoc();
$total = $totalRow['total'];
$totalPages = ceil($total / $limit);

// Get paginated results
$stmt = $conn->prepare("SELECT * FROM features LIMIT ?, ?");
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$features = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Features</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
<?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar2.php';
        include '../../includes/footer.php';
?>
<body>

  <div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
        <h2>Upload New Feature</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
                <div class="text-end mb-3">
                    <button class="btn btn-secondary" onclick="openFeatureModal()">
                        <i class="bi bi-gear-fill me-1"></i> Manage Features
                    </button>
                </div>

                <form action="settings.php" method="POST" enctype="multipart/form-data" class="mb-5">
                    <div class="mb-3">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-control" required>
                    </div>
                    <div class="mb-3">
                    <input type="text" name="title" class="form-control" placeholder="Feature Title" required>
                    </div>
                    <div class="mb-3">
                    <textarea name="description" class="form-control" placeholder="Feature Description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="Slide">Slide</option>
                            <option value="Card" selected>Card</option>
                        </select>
                    </div>

                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#uploadFeatureModal">
                        <i class="bi bi-upload me-1"></i> Upload Feature
                    </button>
                </form>

                <div class="modal fade" id="featuresModal" tabindex="-1" aria-labelledby="featuresModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="featuresModalLabel">Manage Features</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="featuresModalBody">
                            <!-- Content loaded via AJAX -->
                            <p>Loading...</p>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
function openFeatureModal(page = 1) {
    const modalBody = document.getElementById("featuresModalBody");
    modalBody.innerHTML = '<p>Loading...</p>';
    fetch(`../../actions/fetch_features_table.php?page=${page}`)
        .then(res => res.text())
        .then(html => {
            modalBody.innerHTML = html;

            // Bind pagination
            document.querySelectorAll('.feature-pagination').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    openFeatureModal(this.getAttribute('data-page'));
                });
            });

            // Bind delete buttons
            document.querySelectorAll('.delete-feature').forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'You will not be able to recover this feature!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('../../actions/admin_settings_feature_actions.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `action=delete&id=${id}`
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    openFeatureModal(page); // Refresh current page
                                }
                            });
                        }
                    });
                });
            });


            // Bind toggle buttons
            document.querySelectorAll('.toggle-feature').forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    fetch('../../actions/admin_settings_feature_actions.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=toggle&id=${id}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            openFeatureModal(page); // Refresh current page
                        }
                    });
                });
            });
        });

    const modal = new bootstrap.Modal(document.getElementById('featuresModal'));
    modal.show();
}

document.getElementById('featuresModal').addEventListener('hidden.bs.modal', function () {
    location.reload();
});
</script>


</body>
</html>
