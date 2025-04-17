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
    $target_dir = "../../assets/images/";
    $image = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO features (image, title, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $image, $title, $desc);
        $stmt->execute();
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

// Fetch all features
$features = $conn->query("SELECT * FROM features");
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

  <h2>Upload New Feature</h2>
  <div id="main-content" class="container-fluid mt-5">
        <h2>Upload New Feature</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
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
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>

                <h2>Manage Features</h2>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Preview</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Toggle</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $features->fetch_assoc()): ?>
                            <tr>
                            <td><img src="../../assets/images/<?= $row['image'] ?>" width="100"></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= $row['is_active'] ? "✅ Active" : "❌ Inactive" ?></td>
                            <td>
                                <a href="settings.php?toggle=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Toggle</a>
                            </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
