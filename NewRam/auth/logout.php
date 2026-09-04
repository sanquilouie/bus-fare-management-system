<?php
require_once '../includes/security.php';
bfms_start_secure_session();

if (isset($_POST['confirm_logout']) && $_POST['confirm_logout'] === 'true') {
    bfms_require_csrf_token();

    $accountNumber = (string) ($_SESSION['account_number'] ?? '');
    $role = (string) ($_SESSION['role'] ?? '');
    if ($accountNumber !== '' && in_array($role, ['Conductor', 'Superadmin'], true)) {
        require_once '../includes/connection.php';
        $assignmentStatement = $conn->prepare(
            'SELECT 1 FROM businfo WHERE conductorID = ? LIMIT 1'
        );
        $assignmentStatement->bind_param('s', $accountNumber);
        $assignmentStatement->execute();
        $hasActiveAssignment = $assignmentStatement->get_result()->fetch_row() !== null;
        $assignmentStatement->close();
        $conn->close();

        if ($hasActiveAssignment) {
            http_response_code(409);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => 'Complete and remit the active trip before logging out.',
            ]);
            exit;
        }
    }

    session_unset();
    session_destroy();

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => true,
        'message' => 'You have been logged out successfully.',
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
Swal.fire({
    title: 'Are you sure?',
    text: 'You will be logged out of the system.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, log me out!'
}).then((result) => {
    if (!result.isConfirmed) {
        window.history.back();
        return;
    }

    fetch('logout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            confirm_logout: 'true',
            csrf_token: <?php echo json_encode(bfms_csrf_token()); ?>
        })
    })
        .then((response) => response.json())
        .then((data) => {
            if (!data.success) {
                throw new Error(data.message || 'Logout failed');
            }

            Swal.fire({
                title: 'Logged Out!',
                text: data.message,
                icon: 'success',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = 'login.php';
            });
        })
        .catch((error) => {
            Swal.fire('Unable to log out', error.message || 'Unable to log out right now.', 'error');
        });
});
</script>
</body>
</html>
