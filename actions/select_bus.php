<?php
session_start();
include "../includes/connection.php";

if (isset($_POST['bus_number']) && isset($_POST['driver_name'])) {
    // Get the selected bus number and driver name
    $driver_account_number = $_SESSION['account_number']; // Ensure this is defined
    $bus_number = mysqli_real_escape_string($conn, $_POST['bus_number']);
    $driver_name = mysqli_real_escape_string($conn, $_POST['driver_name']);
    $conductor_name = $_SESSION['firstname'] . ' ' . $_SESSION['lastname']; // Use session to get the conductor's name
    $email = $_SESSION['email']; // Ensure this is defined

    // Update session with the bus number, driver name, and conductor name
    $_SESSION['bus_number'] = $bus_number;
    $_SESSION['driver_account_number'] = $driver_account_number; // Set the driver account number
    $_SESSION['driver_name'] = $driver_name;
    $_SESSION['conductor_name'] = $conductor_name;
    $_SESSION['conductor_number'] = $conductor_account_number; // Ensure this is defined
    $_SESSION['email'] = $email; // Set the email variable

    // Update the database with temporary data
    $update_bus_data = "
    UPDATE businfo 
    SET driverName = '$driver_name', 
        conductorName = '$conductor_name', 
        status = ' In Transit' 
    WHERE bus_number = '$bus_number'
";
echo "<script>console.log(" . json_encode($_SESSION) . ");</script>";
    if (mysqli_query($conn, $update_bus_data)) {
        // Redirect to the conductor dashboard or another page after saving
        header("Location: /NewRam/pages/conductor/busfare.php");
        exit();
    } else {
        // Handle database error
        echo "Error updating record: " . mysqli_error($conn);
    }
} else {
    // Handle invalid submission or redirection
    header("Location: ../auth/login.php");
    exit();
}
?>