<?php
session_start();
include "../includes/connection.php";

$limit = 15; // Records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Paginated query for remit_logs
// Updated query with JOIN and full name
$query = "SELECT 
        r.*, 
        CONCAT(u.firstname, ' ', u.lastname) AS full_name
    FROM remit_logs r
    JOIN useracc u ON r.conductor_id = u.account_number
    ORDER BY r.remit_date DESC 
    LIMIT ? OFFSET ?
";


$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$remit_logs = [];
while ($row = $result->fetch_assoc()) {
    $remit_logs[] = $row;
}

// Get total records count for pagination
$countQuery = "SELECT COUNT(id) AS total FROM remit_logs";
$countResult = $conn->query($countQuery);
$totalRemits = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRemits / $limit);

// Return JSON response
echo json_encode([
    'remit_logs' => $remit_logs, 
    'totalPages' => $totalPages, 
    'currentPage' => $page
]);
?>
