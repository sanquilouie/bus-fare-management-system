<?php
include "../includes/connection.php";
require('../libraries/fpdf/fpdf.php'); // Adjust the path if necessary
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the selected date values from the POST request
    $selectedDate = $_POST['date'] ?? date('Y-m-d'); // Default to current date if not provided

    // Extract year, month, and day from the selected date
    $year = date('Y', strtotime($selectedDate));
    $month = date('m', strtotime($selectedDate));
    $day = date('d', strtotime($selectedDate));

    // Base query with the selected date filter
    $query = "SELECT SUM(fare) AS total_revenue 
              FROM passenger_logs 
              WHERE DATE(timestamp) = '$selectedDate'"; // Filter by the exact selected date

    $result = mysqli_query($conn, $query);

    if (!$result) {
        die('Database query failed: ' . mysqli_error($conn));
    }

    // Initialize PDF
    $pdf = new FPDF();
    $pdf->AddPage('L'); // Landscape orientation for more space
    $pdf->SetFont('Arial', 'B', 16);

    // Add title
    $pdf->Cell(0, 10, "Revenue Report for $selectedDate", 0, 1, 'C');
    $pdf->Ln(10); // Add space below the title

    if (mysqli_num_rows($result) > 0) {
        // Table headers with wider columns
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(60, 12, 'Date', 1, 0, 'C'); // Wider column for date
        $pdf->Cell(120, 12, 'Total Revenue (PHP)', 1, 1, 'C'); // Wider column for revenue

        // Add the data rows
        $pdf->SetFont('Arial', '', 12);
        $row = mysqli_fetch_assoc($result);
        $pdf->Cell(60, 12, $selectedDate, 1, 0, 'C'); // Date value
        $pdf->Cell(120, 12, number_format($row['total_revenue'], 2), 1, 1, 'C'); // Revenue value
    } else {
        // Handle no data case
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell(0, 10, 'No revenue data available for the selected date.', 0, 1, 'C');
    }

    mysqli_close($conn);

    // Output the PDF
    $pdf->Output('Revenue_Report.pdf', 'D');
    exit;
}
?>