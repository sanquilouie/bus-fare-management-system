<?php 
session_start();
include '../includes/connection.php';
$response = ['success' => false, 'message' => ''];

$driver_name = isset($_SESSION['driver_name']) ? $_SESSION['driver_name'] : null;

$nameParts = explode(' ', $driver_name);
$firstname = $nameParts[0]; // First name
$middlename = isset($nameParts[1]) ? $nameParts[1] : ''; // Middle name (if present)
$lastname = isset($nameParts[2]) ? $nameParts[2] : ''; // Last name (if present)

if (isset($_POST['confirm_logout']) && $_POST['confirm_logout'] === 'true') {
    if (isset($_SESSION['bus_number'], $_SESSION['driver_account_number'])) {
        // Conductor session logic only
        $driverID = $_SESSION['driver_account_number'];

        $stmt = $conn->prepare("SELECT 1 FROM businfo WHERE driverID = ? LIMIT 1");
        $stmt->bind_param("s", $driverID);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            unset($_SESSION['bus_number']);
            unset($_SESSION['driver_account_number']);
			unset($_SESSION['direction']);
			unset($_SESSION['account_number']);
			$_SESSION['passengers'] = [];
        }

        $stmt->close();
        $conn->close();

        // Send response for conductor logout
        echo json_encode([
            'success' => true,
            'message' => 'You have been logged out successfully (conductor).'
        ]);
        exit();
    }

    // If not a conductor, do regular logout (full session clear)
    session_unset();
    session_destroy();

    echo json_encode([
        'success' => true,
        'message' => 'You have been logged out successfully.'
    ]);
    exit();
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
			text: "You will be logged out of the system.",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, log me out!'
		}).then((result) => {
			if (result.isConfirmed) {
				// If confirmed, log the user out
				fetch('logout.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: new URLSearchParams({ confirm_logout: 'true' })
				})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							Swal.fire({
					title: 'Logged Out!',
					text: data.message,
					icon: 'success',
					showConfirmButton: false,  // Disable the "OK" button
					timer: 1500  // Optionally, you can add a timer to auto-close after 1.5 seconds
				}).then(() => {
					window.location.href = 'login.php';  // Redirect to the login page after the alert
				});

						} else {
							Swal.fire('Error', data.error || 'An error occurred during logout.', 'error');
							window.history.back(); // Go back to the previous page
						}
					})
					.catch(error => {
						Swal.fire('Error', 'An error occurred while logging out.', 'error');
					});
			} else {
				// If cancel, navigate back
				window.history.back();
			}
		});
	</script>
</body>

</html>