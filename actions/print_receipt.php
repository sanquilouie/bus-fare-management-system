<?php
// Include the print function
require_once 'print_function.php';

// Initialize variables with POST data or default values
$busNumber = isset($_POST['busNumber']) ? $_POST['busNumber'] : 'Unknown Bus Number';
$transactionNumber = isset($_POST['transactionNumber']) ? $_POST['transactionNumber'] : 'Unknown Transaction Number';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from POST request
    $fromRoute = isset($_POST['fromRoute']) ? $_POST['fromRoute'] : 'Unknown From Route';
    $toRoute = isset($_POST['toRoute']) ? $_POST['toRoute'] : 'Unknown To Route';
    $fareType = isset($_POST['fareType']) ? $_POST['fareType'] : 'Unknown Fare Type';
    $totalFare = isset($_POST['totalFare']) ? $_POST['totalFare'] : '0.00';
    $conductorName = isset($_POST['conductorName']) ? $_POST['conductorName'] : 'Unknown Conductor';
    $distance = isset($_POST['distance']) ? $_POST['distance'] : 'Unknown Distance';
    $driverName = isset($_POST['driverName']) ? $_POST['driverName'] : 'unknown driver';
    $paymentMethod = isset($_POST['paymentMethod']) ? $_POST['paymentMethod'] : 'unknown paymet method';
    $passengerQuantity = isset($_POST['passengerQuantity']) ? $_POST['passengerQuantity'] : 'unknown Passenger Quantity';
    // Call the printReceipt function with the gathered data
    printReceipt($fromRoute, $toRoute, $fareType, $totalFare, $conductorName, $busNumber, $transactionNumber, $distance, $driverName, $paymentMethod, $passengerQuantity);
}

// Debugging line to check the transaction number (this can be removed in production)

?>