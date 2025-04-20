<?php
session_start();
date_default_timezone_set('Asia/Manila');
include "../includes/connection.php";

function formatLine($label, $value, $width = 32) {
    // Format line with left-aligned label and right-aligned value
    $line = str_pad($label, $width - strlen($value), ' ', STR_PAD_RIGHT) . $value;
    return $line . "\n";
}

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $rfid = $data['rfid'] ?? '';
    $busNo = $data['bus_no'] ?? '';
    $conductor = $data['conductor_name'] ?? '';
    $totalFare = $data['total_fare'] ?? '0';
    $totalCard = $data['total_card'] ?? '0';
    $totalLoad = $data['total_load'] ?? '0';
    $netAmount = $data['net_amount'] ?? '0';
    $deductions = $data['deductions'] ?? [];

    $driver_name = isset($_SESSION['driver_name']) ? $_SESSION['driver_name'] : null;
    $nameParts = explode(' ', $driver_name);
    $firstname = $nameParts[0];
    $middlename = isset($nameParts[1]) ? $nameParts[1] : '';
    $lastname = isset($nameParts[2]) ? $nameParts[2] : '';

    $date = date("Y-m-d");
    $time = date("H:i:s");

    // Insert into remit_logs
    $deduction_total = 0;
    foreach ($deductions as $deduction) {
        $parts = explode(':', $deduction);
        $raw_amount = trim($parts[1] ?? '0');
        $amount = floatval(str_replace(['₱', 'P', 'p'], '', $raw_amount));
        $deduction_total += $amount;
    }
    $stmt0 = $conn->prepare("INSERT INTO remittances (conductor_id, bus_no, remit_date, total_earning, total_deductions, net_amount) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt0->bind_param("sssddd", $rfid, $busNo, $date, $totalFare, $deduction_total, $netAmount);
    $stmt0->execute();
    $remit_id = $stmt0->insert_id;
    $stmt0->close();

    // Step 2: Insert into remit_logs, using the remit_id from above
    $stmt2 = $conn->prepare("INSERT INTO remit_logs (remit_id, conductor_id, bus_no, total_cash, total_card, total_load, net_amount, total_deductions, remit_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("issddddds", $remit_id, $rfid, $busNo, $totalFare, $totalCard, $totalLoad, $netAmount, $deduction_total, $date);
    $stmt2->execute();
    $stmt2->close();

    // Update passenger_logs to 'remitted'
    $stmt1 = $conn->prepare("UPDATE passenger_logs SET status = 'remitted' WHERE conductor_id = ? AND bus_number = ? AND status = 'notremitted'");
    $stmt1->bind_param("ss", $rfid, $busNo);
    $stmt1->execute();
    $stmt1->close();

    // Update transactions to 'remitted'
    $stmt2 = $conn->prepare("UPDATE transactions SET status = 'remitted' WHERE conductor_id = ? AND status != 'edited'");
    $stmt2->bind_param("s", $rfid);
    $stmt2->execute();
    $stmt2->close();

    // Unset conductor session and update businfo
    $updateBusStmt = $conn->prepare("UPDATE businfo SET driverName ='', conductorName ='', status = 'available', destination = '', driverID = '', conductorID = '', current_stop = '' WHERE bus_number = ?");
    $updateBusStmt->bind_param("s", $busNo);
    $updateBusStmt->execute();
    $updateBusStmt->close();

    // Respond with data for frontend
    echo json_encode([
        'success' => true,
        'rfid' => $rfid,
        'bus_no' => $busNo,
        'conductor_name' => $conductor,
        'total_fare' => $totalFare,
        'total_card' => $totalCard,
        'total_load' => $totalLoad,
        'net_amount' => $netAmount,
        'deductions' => $deductions,
        'remit_id' => substr(md5($remit_id), 0, 15) // Optional: Masked remit_id
    ]);
} else {
    echo json_encode(['error' => 'Invalid JSON input.']);
}
