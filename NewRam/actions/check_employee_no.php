<?php
// check_employee_no.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../includes/connection.php";

if (isset($_POST['employeeNumber'])) {
    $empNo = $_POST['employeeNumber'];

    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM useracc WHERE account_number = ?");
    mysqli_stmt_bind_param($stmt, 's', $empNo);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    header('Content-Type: application/json');
    echo json_encode(['exists' => $count > 0]);
} else {
    echo json_encode(['error' => 'No employee number provided']);
}
?>
