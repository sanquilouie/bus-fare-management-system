<?php
include '../includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bus_no = $conn->real_escape_string($_POST['bus_no'] ?? '');
    $driver_name = $conn->real_escape_string($_POST['driver'] ?? '');
    $conductor_name = $conn->real_escape_string($_POST['conductor'] ?? '');
    $passenger_count = intval($_POST['passengers'] ?? 0);
    $issue = $conn->real_escape_string($_POST['issue'] ?? '');
    $remarks = $conn->real_escape_string($_POST['remarks'] ?? '');

    // Basic validation
    if (empty($bus_no) || empty($issue)) {
        echo 'Invalid input';
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO inspection_logs 
        (bus_no, driver, conductor, pass_count, violation, remarks, inspection_date)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");

    if ($stmt) {
        $stmt->bind_param("sssiss", $bus_no, $driver_name, $conductor_name, $passenger_count, $issue, $remarks);
        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'Database error: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        echo 'Prepare failed: ' . $conn->error;
    }

    $conn->close();
} else {
    echo 'Invalid request';
}
