<?php
require_once '../includes/security.php';
bfms_require_roles(['Conductor', 'Superadmin']);
bfms_require_same_origin();
require_once '../includes/connection.php';
header('Content-Type: application/json; charset=UTF-8');

// Get the raw POST data and decode JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['account_number'])) {
    bfms_json_error('A driver account number is required.', 422);
}

$accountNumber = (string) $data['account_number'];
$stmt = $conn->prepare(
    "SELECT 1 FROM useracc u
     WHERE u.account_number = ? AND u.role = 'Driver' AND u.is_activated = 1
       AND u.driverStatus = 'notdriving'
       AND NOT EXISTS (SELECT 1 FROM businfo b WHERE b.driverID = u.account_number)
     LIMIT 1"
);
$stmt->bind_param('s', $accountNumber);
$stmt->execute();
$isEligible = $stmt->get_result()->fetch_row() !== null;
$stmt->close();

if (!$isEligible) {
    bfms_json_error('The selected driver is not available.', 409);
}

$_SESSION['driver_account_number'] = $accountNumber;
echo json_encode(['success' => true]);
