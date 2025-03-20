<?php
session_start();
include "../includes/connection.php";

$limit = 10; // Users per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT id, firstname, middlename, lastname, birthday, age, gender, address, account_number, balance, province, municipality, barangay, is_activated FROM useracc WHERE is_activated = 1";

$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (firstname LIKE ? OR middlename LIKE ? OR lastname LIKE ?)";
    $searchTerm = "%$search%";
    array_push($params, $searchTerm, $searchTerm, $searchTerm);
    $types .= "sss";
}

$query .= " ORDER BY created_at DESC LIMIT ?, ?";
array_push($params, $offset, $limit);
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Get total count for pagination
$countQuery = "SELECT COUNT(id) AS total FROM useracc WHERE is_activated = 1";
if (!empty($search)) {
    $countQuery .= " AND (firstname LIKE ? OR middlename LIKE ? OR lastname LIKE ?)";
    $stmtCount = $conn->prepare($countQuery);
    $stmtCount->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmtCount = $conn->prepare($countQuery);
}

$stmtCount->execute();
$countResult = $stmtCount->get_result();
$totalUsers = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalUsers / $limit);

echo json_encode(['users' => $users, 'totalPages' => $totalPages, 'currentPage' => $page]);
?>
