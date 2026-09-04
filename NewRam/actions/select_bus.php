<?php
require_once '../includes/security.php';
bfms_require_roles(['Conductor', 'Superadmin']);
bfms_require_csrf_token();

include "../includes/connection.php";

if (isset($_POST['bus_number'], $_SESSION['driver_account_number'])) {
    $conductor_account_number = $_SESSION['account_number'];
    $driver_account_number = (string) $_SESSION['driver_account_number'];
    $bus_number = (string) $_POST['bus_number'];
    $conductor_name = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];

    $driverStmt = $conn->prepare(
        "SELECT firstname, lastname FROM useracc
         WHERE account_number = ? AND role = 'Driver' AND is_activated = 1
           AND driverStatus = 'notdriving'
           AND NOT EXISTS (SELECT 1 FROM businfo WHERE driverID = ?)
         LIMIT 1"
    );
    $driverStmt->bind_param('ss', $driver_account_number, $driver_account_number);
    $driverStmt->execute();
    $driver = $driverStmt->get_result()->fetch_assoc();
    $driverStmt->close();

    if (!$driver) {
        http_response_code(409);
        exit('The selected driver is no longer available.');
    }

    $driver_name = $driver['firstname'] . ' ' . $driver['lastname'];

    $updateBus = $conn->prepare(
        "UPDATE businfo
         SET driverName = ?, conductorName = ?, driverID = ?, conductorID = ?, status = 'assigned'
         WHERE bus_number = ? AND status = 'available' AND statusofbus = 'active'"
    );
    $updateBus->bind_param(
        'sssss',
        $driver_name,
        $conductor_name,
        $driver_account_number,
        $conductor_account_number,
        $bus_number
    );
    $updateBus->execute();

    if ($updateBus->affected_rows === 1) {
        $updateBus->close();
        $_SESSION['bus_number'] = $bus_number;
        $_SESSION['conductor_account_number'] = $conductor_account_number;
        $_SESSION['driver_name'] = $driver_name;
        $_SESSION['conductor_name'] = $conductor_name;
        $_SESSION['conductor_number'] = $conductor_account_number;
        // Redirect to the conductor dashboard or another page after saving
        header("Location: /NewRam/pages/conductor/busfare_auto.php");
        exit();
    } else {
        $updateBus->close();
        http_response_code(409);
        exit('The selected bus is no longer available.');
    }
} else {
    // Handle invalid submission or redirection
    header("Location: ../auth/login.php");
    exit();
}
?>
