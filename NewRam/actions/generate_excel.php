<?php
require '../libraries/vendor/autoload.php'; // Include PhpSpreadsheet library

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Set headers for Excel download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="passenger_logs.xlsx"');

// Database connection
include "../includes/connection.php";

// Get bus number from request (you might get it via GET or POST)
$busNumber = $_GET['bus_number']; // Assume it's passed via GET
$today = $_GET['remit_date'];

// Query passenger logs for the selected bus number and today's date
$sql = "SELECT rfid, from_route, to_route, fare, conductor_id, conductor_name, driver_id, driver_name, timestamp, transaction_number, rating, feedback
        FROM passenger_logs
        WHERE bus_number = ? AND DATE(timestamp) = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $busNumber, $today); // Bind parameters to prevent SQL injection
$stmt->execute();
$result = $stmt->get_result();

// Create a new spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set column headers
$sheet->setCellValue('A1', 'RFID');
$sheet->setCellValue('B1', 'From Route');
$sheet->setCellValue('C1', 'To Route');
$sheet->setCellValue('D1', 'Fare');
$sheet->setCellValue('E1', 'Conductor ID');
$sheet->setCellValue('F1', 'Conductor Name');
$sheet->setCellValue('G1', 'Driver ID');
$sheet->setCellValue('H1', 'Driver Name');
$sheet->setCellValue('I1', 'Timestamp');
$sheet->setCellValue('J1', 'Transaction Number');
$sheet->setCellValue('K1', 'Rating');
$sheet->setCellValue('L1', 'Feedback');

// Style header row
$headerStyle = [
    'font' => [
        'bold' => true,
        'size' => 12,
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'argb' => 'FFCCCCCC', // Light grey background
        ],
    ],
];

// Apply the header style to the first row
$sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

// Set column widths
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(10);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(20);
$sheet->getColumnDimension('G')->setWidth(15);
$sheet->getColumnDimension('H')->setWidth(20);
$sheet->getColumnDimension('I')->setWidth(20);
$sheet->getColumnDimension('J')->setWidth(15);
$sheet->getColumnDimension('K')->setWidth(10);
$sheet->getColumnDimension('L')->setWidth(30);

// Populate rows with data from the query
$row = 2; // Start from the second row
while ($data = $result->fetch_assoc()) {
    $sheet->setCellValue("A$row", $data['rfid']);
    $sheet->setCellValue("B$row", $data['from_route']);
    $sheet->setCellValue("C$row", $data['to_route']);
    $sheet->setCellValue("D$row", $data['fare']);
    $sheet->setCellValue("E$row", $data['conductor_id']);
    $sheet->setCellValue("F$row", $data['conductor_name']);
    $sheet->setCellValue("G$row", $data['driver_id']);
    $sheet->setCellValue("H$row", $data['driver_name']);
    $sheet->setCellValue("I$row", $data['timestamp']);
    $sheet->setCellValueExplicit("J$row", (string)$data['transaction_number'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue("K$row", $data['rating']);
    $sheet->setCellValue("L$row", $data['feedback']);
    
    // Style each data row
    $sheet->getStyle("A$row:L$row")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ]);
    $row++;
}

// Write the spreadsheet to the browser
$writer = new Xlsx($spreadsheet);
$writer->save('php://output'); // Output to browser as Excel file
?>
