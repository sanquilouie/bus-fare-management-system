<?php
include '../../../includes/connection.php';

$id = $_GET['id'] ?? null;
$updated = $_GET['updated'] ?? null;

if (!$id) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE features SET title = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $title, $description, $id);
    $stmt->execute();

    header("Location: edit_feature.php?id=$id&updated=1");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM features WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$feature = $result->fetch_assoc();

if (!$feature) {
    echo "Feature not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Feature</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Bootstrap Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Feature</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input class="form-control" type="text" name="title" value="<?= htmlspecialchars($feature['title']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" required><?= htmlspecialchars($feature['description']) ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="../settings.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Auto-trigger modal on page load -->
    <script>
        const modal = new bootstrap.Modal(document.getElementById('editModal'), {
            backdrop: 'static',
            keyboard: false
        });
        modal.show();
    </script>

    <!-- SweetAlert on successful update -->
    <?php if ($updated): ?>
    <script>
        Swal.fire({
            title: 'Updated!',
            text: 'Feature was successfully updated.',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then(() => {
            window.location.href = '../settings.php';
        });


    </script>
    <?php endif; ?>
</body>
</html>
