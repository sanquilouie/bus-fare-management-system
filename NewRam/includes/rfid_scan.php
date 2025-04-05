<?php
// rfid_scan.php
include '../../config/connection.php';
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rfid_code'])) {
    $rfidCode = $_POST['rfid_code'];
    $userResult = fetchUserByRFID($conn, $rfidCode);

    if ($userResult && mysqli_num_rows($userResult) > 0) {
        $userRow = mysqli_fetch_assoc($userResult);
        echo json_encode(['success' => true, 'user' => $userRow]);
    } else {
        echo json_encode(['error' => "No user found with RFID Code: " . htmlspecialchars($rfidCode)]);
    }
}
?>
