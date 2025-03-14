<?php
session_start();
ob_start(); 
include '../../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../index.php");
    exit();
}

$activeUsersQuery = "SELECT * FROM useracc WHERE is_activated = 1 ORDER BY created_at DESC";
$activeUsersResult = mysqli_query($conn, $activeUsersQuery);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];

    if ($user_id) {
        $disableQuery = "UPDATE useracc SET is_activated = 0 WHERE id = ?";
        $stmt = $conn->prepare($disableQuery);
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'User disabled successfully.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error disabling user.'];
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'User ID is missing.'];
    }
    header("Location: disable_users.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disable Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Use full version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/notyf@3.9.0/notyf.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/notyf@3.9.0/notyf.min.js"></script>

<link rel="stylesheet" href="../../../assets/css/sidebars.css">
</head>
<body>
    <?php
        include '../../../includes/topbar.php';
        include '../../../includes/sidebar.php';
        include '../../../includes/footer.php';
    ?>
<div class="container mt-5">
    <h3>Disable Users</h3>
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
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($activeUsersResult)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                        <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                        <td>
                            <form method="POST" action="disable_users.php">
                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-danger">Disable</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        <?php if (isset($_SESSION['message'])): ?>
            const message = <?php echo json_encode($_SESSION['message']); ?>;
            Swal.fire({
                icon: message.type === 'success' ? 'success' : 'error',
                title: message.type === 'success' ? 'Success' : 'Error',
                text: message.text,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
    });
</script>
</body>
</html>
<?php
ob_end_flush();
?>
