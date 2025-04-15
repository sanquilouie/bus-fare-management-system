<?php
date_default_timezone_set('Asia/Manila');
require_once '../libraries/vendor/autoload.php';
include "../includes/connection.php";

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

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
    $totalLoad = $data['total_load'] ?? '0';
    $netAmount = $data['net_amount'] ?? '0';
    $deductions = $data['deductions'] ?? [];

    

    $date = date("Y-m-d");
    $time = date("H:i:s");

    // 1. INSERT into remit_logs
    $deduction_total = 0;
    foreach ($deductions as $deduction) {
        $parts = explode(':', $deduction);
        $raw_amount = trim($parts[1] ?? '0');
        $amount = floatval(str_replace(['₱', 'P', 'p'], '', $raw_amount));
        $deduction_total += $amount;
    }
    $stmt = $conn->prepare("INSERT INTO remit_logs (conductor_id, bus_no, total_cash, total_load, net_amount, total_deductions, remit_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdddds", $rfid, $busNo, $totalFare, $totalLoad, $netAmount, $deduction_total, $date);
    $stmt->execute();
    $remit_id = $stmt->insert_id; // Get auto-incremented remit_id
    $stmt->close();

    // 2. UPDATE passenger_logs to remitted
    $stmt1 = $conn->prepare("UPDATE passenger_logs SET status = 'remitted' WHERE conductor_id = ? AND bus_number = ? AND status = 'notremitted'");
    $stmt1->bind_param("ss", $rfid, $busNo);
    $stmt1->execute();
    $stmt1->close();

    // 3. UPDATE transactions to remitted
    $stmt2 = $conn->prepare("UPDATE transactions SET status = 'remitted' WHERE conductor_id = ? AND status != 'edited'");
    $stmt2->bind_param("s", $rfid);
    $stmt2->execute();
    $stmt2->close();

    // 4. Continue with receipt printing
    $connector = new WindowsPrintConnector("POS58");
    $printer = new Printer($connector);

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("ZARAGOZA RAMSTAR\n\n");
    $printer->text("=== REMITTANCE SLIP ===\n\n");

    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text(formatLine("RFID: $rfid", $date));
    $printer->text(formatLine("Bus No: $busNo", date("h:i A")));
    $printer->text("Conductor: $conductor\n\n");

    $printer->text(formatLine("Total Fare", "PHP$totalFare"));
    $printer->text(formatLine("Total Load", "PHP$totalLoad"));

    if (!empty($deductions)) {
        $printer->text("\nDeductions:\n");
        foreach ($deductions as $deduction) {
            $deduction = str_replace('₱', 'PHP', $deduction);
            $parts = explode(':', $deduction);
            $desc = $parts[0] ?? 'No Desc';
            $amount = trim($parts[1] ?? '0.00');
            $printer->text(formatLine(" - $desc", "$amount"));
        }
    }

    $printer->text("\n");
    $printer->text(formatLine("NET AMOUNT", "PHP$netAmount"));
    $printer->text("\n-------------------------------\n");
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $masked = substr(md5($remit_id), 0, 15);
    $printer->text(strtoupper($masked));
    $printer->text("\n");
    $printer->text("THANK YOU!\n\n");

    $printer->cut();
    $printer->close();

    echo "Printed and logged successfully.";
} else {
    echo "Invalid JSON input.";
}

