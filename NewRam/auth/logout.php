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
    // Check if it's a conductor's session
    if (isset($_SESSION['bus_number'], $_SESSION['driver_account_number'], $_SESSION['email'], $_SESSION['driver_name'])) {
        echo json_encode(['success' => 'You have been logged out successfully!']);
        exit();
    }
    // Check if it's a regular user session
    elseif (isset($_POST['confirm_logout'])) {
        // Unset all session variables
        session_unset();

        // Destroy the session
        session_destroy();

        // Return a success message as JSON
        echo json_encode(['success' => 'You have been logged out successfully!']);
        exit();
    }

    echo json_encode($response);
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