<?php
include "../includes/connection.php";

if (isset($_POST['busNumber']) || isset($_POST['plateNumber'])) {
    $busNumber = isset($_POST['busNumber']) ? $_POST['busNumber'] : '';
    $plateNumber = isset($_POST['plateNumber']) ? $_POST['plateNumber'] : '';

    // Query to check if bus number or plate number exists
    $queryCheck = "SELECT * FROM businfo WHERE bus_number = ? OR plate_number = ?";
    $stmtCheck = $conn->prepare($queryCheck);
    $stmtCheck->bind_param("ss", $busNumber, $plateNumber);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        echo "exists";  // Respond with "exists" if duplicate is found
    } else {
        echo "not_exists";  // Respond with "not_exists" if no duplicates are found
    }

    $stmtCheck->close();
    $conn->close();
}
?>
