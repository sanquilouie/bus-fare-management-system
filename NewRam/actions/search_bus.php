<?php
include '../includes/connection.php';

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$sql = "
SELECT 
    b.status,
    b.bus_number,
    p.driver_name AS driver_name,
    p.conductor_name AS conductor_name,
    IFNULL(SUM(p.fare), 0) AS total_fare,
    IFNULL(SUM(p.passenger_count), 0) AS total_passengers,
    CASE 
        WHEN b.status = 'assigned' 
             AND il.inspection_id IS NOT NULL 
             AND (il.driver_violation != 'None' OR il.conductor_violation != 'None') 
            THEN 'Inspected with Violation'
        WHEN b.status = 'assigned' 
             AND il.inspection_id IS NOT NULL 
            THEN 'Inspected'
        WHEN b.status = 'assigned' 
            THEN 'Pending'
        ELSE 'N/A'
    END AS inspection_status
FROM 
    businfo b
LEFT JOIN 
    passenger_logs p 
    ON b.bus_number = p.bus_number 
    AND DATE(p.timestamp) = CURDATE()
LEFT JOIN 
    inspection_logs il 
    ON b.bus_number = il.bus_no 
    AND DATE(il.inspection_date) = CURDATE()
WHERE
    b.statusofbus != 'inactive'
";

if (!empty($search)) {
    $sql .= " AND b.bus_number LIKE '%$search%'";
}

$sql .= "
GROUP BY 
    b.bus_number, b.status, p.driver_name, p.conductor_name, il.inspection_id, il.driver_violation, il.conductor_violation
";

$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$output = '<table class="table table-bordered">
<thead>
    <tr>
        <th>Status</th>
        <th>Bus Number</th>
        <th># of Passengers</th>
        <th>Driver</th>
        <th>Conductor</th>
        <th>Inspection Status</th>
    </tr>
</thead>
<tbody>';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $output .= "<tr>
            <td>" . htmlspecialchars($row['status']) . "</td>
            <td>" . htmlspecialchars($row['bus_number']) . "</td>
            <td>" . $row['total_passengers'] . "</td>
            <td>" . htmlspecialchars($row['driver_name']) . "</td>
            <td>" . htmlspecialchars($row['conductor_name']) . "</td>
            <td>";

        if ($row['status'] === 'assigned') {
            if ($row['inspection_status'] === 'Pending') {
                $output .= "<button 
                class='btn btn-warning btn-sm inspect-btn' 
                data-bus='" . htmlspecialchars($row['bus_number']) . "'
                data-driver='" . htmlspecialchars($row['driver_name']) . "'
                data-conductor='" . htmlspecialchars($row['conductor_name']) . "'
                data-passengers='" . $row['total_passengers'] . "'
                >Pending</button>";
            } elseif ($row['inspection_status'] === 'Inspected') {
                $output .= "<span class='badge bg-success'>Inspected</span>";
            }elseif ($row['inspection_status'] === 'Inspected with Violation') {
                $output .= "<span class='badge bg-danger'>Inspected with Violation</span>";
            } else {
                $output .= "N/A";
            }
        } else {
            $output .= "N/A";
        }

        $output .= "</td></tr>";
    }
} else {
    $output .= "<tr><td colspan='6'>No data found.</td></tr>";
}

$output .= '</tbody></table>';

echo $output;

$conn->close();

