<?php
session_start();
include "../includes/connection.php";

$limit = 10; // Users per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM useracc WHERE is_activated = 1 ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Get total users count for pagination
$countQuery = "SELECT COUNT(id) AS total FROM useracc WHERE is_activated = 1";
$countResult = $conn->query($countQuery);
$totalUsers = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalUsers / $limit);

echo json_encode(['users' => $users, 'totalPages' => $totalPages, 'currentPage' => $page]);
?>
