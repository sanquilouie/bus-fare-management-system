<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['rfid'])) {
        require_once "../includes/connection.php";
        $rfid = $input['rfid'];

        $user = getUserInfo($rfid, $conn);
        if (!$user || empty($user['contactnumber'])) {
            echo json_encode(['status' => 'error', 'message' => 'User not found or missing contact.']);
            exit;
        }

        $dateTime = date('Y-m-d H:i:s');
        $message = "Your NFC card was used on {$dateTime}. New balance: ₱" . number_format($user['balance'], 2);
        $result = sendSMS($user['contactnumber'], $message);

        echo json_encode(['status' => 'success', 'message' => 'SMS sent.']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'RFID missing.']);
    exit;
}
