<?php
session_start();
include "../includes/connection.php";

$limit = 15; // Transactions per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$query = "SELECT 
    t.id, 
    u.firstname, 
    u.lastname, 
    u.account_number, 
    t.amount, 
    t.transaction_type, 
    t.transaction_date, 
    t.conductor_id, 
    c.firstname AS conductor_firstname, 
    c.lastname AS conductor_lastname, 
    c.account_number AS conductor_account_number,
    c.role AS loaded_by_role,
    t.status
    FROM transactions t 
    JOIN useracc u ON t.account_number = u.account_number
    LEFT JOIN useracc c ON t.conductor_id = c.account_number
    WHERE c.role = 'Cashier'
    ORDER BY t.transaction_date DESC
    LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

// Get total transactions count for pagination
$countQuery = "SELECT COUNT(id) AS total FROM transactions";
$countResult = $conn->query($countQuery);
$totalTransactions = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalTransactions / $limit);

echo json_encode(['transactions' => $transactions, 'totalPages' => $totalPages, 'currentPage' => $page]);
?>
