<?php
require '../libraries/fpdf/fpdf.php';
include "../includes/connection.php";

// Get parameters
$busNumber = $_GET['bus_number'];
$today = $_GET['remit_date'];

// Fetch data
$sql = "SELECT rfid, from_route, to_route, fare, conductor_id, conductor_name, driver_id, driver_name, timestamp, transaction_number, rating, feedback
        FROM passenger_logs
        WHERE bus_number = ? AND DATE(timestamp) = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $busNumber, $today);
$stmt->execute();
$result = $stmt->get_result();

// Create PDF
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 8);

// Table Header
// Table Header
$headers = ['RFID', 'From', 'To', 'Fare', 'Conductor ID', 'Conductor Name', 'Driver ID', 'Driver Name', 'Timestamp', 'Txn No.', 'Rating', 'Feedback'];
$widths = [20, 20, 20, 15, 22, 30, 22, 30, 30, 25, 15, 35];

foreach ($headers as $index => $col) {
    $pdf->Cell($widths[$index], 10, $col, 1, 0, 'C');
}
$pdf->Ln();

// Table Rows
$pdf->SetFont('Arial', '', 7); // Slightly smaller font
while ($row = $result->fetch_assoc()) {
    $pdf->Cell($widths[0], 10, $row['rfid'], 1);
    $pdf->Cell($widths[1], 10, $row['from_route'], 1);
    $pdf->Cell($widths[2], 10, $row['to_route'], 1);
    $pdf->Cell($widths[3], 10, $row['fare'], 1);
    $pdf->Cell($widths[4], 10, $row['conductor_id'], 1);
    $pdf->Cell($widths[5], 10, $row['conductor_name'], 1);
    $pdf->Cell($widths[6], 10, $row['driver_id'], 1);
    $pdf->Cell($widths[7], 10, $row['driver_name'], 1);
    $pdf->Cell($widths[8], 10, $row['timestamp'], 1);
    $pdf->Cell($widths[9], 10, $row['transaction_number'], 1);
    $pdf->Cell($widths[10], 10, $row['rating'], 1);
    $pdf->Cell($widths[11], 10, $row['feedback'], 1);
    $pdf->Ln();
}


// Output
$pdf->Output('D', 'passenger_logs.pdf');
?>
