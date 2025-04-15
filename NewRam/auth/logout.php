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
        $bus_number = $_SESSION['bus_number'];
        $conductor_id = $_SESSION['driver_account_number'];
        $email = $_SESSION['email'];
        $driver_name = $_SESSION['driver_name'];
   
        // Update the bus status to 'Available'
        $updateBusStmt = $conn->prepare("UPDATE businfo SET driverName ='', driverID='', conductorName ='', conductorID='', status = 'available', destination = '', current_stop='' WHERE bus_number = ?");
        if ($updateBusStmt) {
            $updateBusStmt->bind_param("s", $bus_number);
            if ($updateBusStmt->execute()) {
                // Split the full name into first, middle, and last name if necessary
               
                // Update the driver status in the useracc table to 'notdriving'
                $updateDriverStatusStmt = $conn->prepare("UPDATE useracc SET driverStatus = 'notdriving' WHERE  account_number = ?");
                if ($updateDriverStatusStmt) {
                    $updateDriverStatusStmt->bind_param("s",$lastname);
                    if ($updateDriverStatusStmt->execute()) {
                        session_destroy(); // End the session
                        $response = ['success' => true, 'message' => 'Conductor logged out successfully and driver status updated.'];
                    } else {
                        $response = ['error' => 'Error updating driver status: ' . $conn->error];
                    }
                    $updateDriverStatusStmt->close();
                } else {
                    $response = ['error' => 'Error preparing driver status update statement: ' . $conn->error];
                }
            } else {
                $response = ['error' => 'Error updating bus status: ' . $conn->error];
            }
            $updateBusStmt->close();
        } else {
            $response = ['error' => 'Error preparing bus update statement: ' . $conn->error];
        }
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