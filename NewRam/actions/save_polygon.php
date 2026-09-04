<?php
require_once '../includes/security.php';
bfms_require_roles(['Admin', 'Superadmin']);
bfms_require_same_origin();
header('Content-Type: application/json');

// Get the POST data
$data = json_decode(file_get_contents("php://input"), true);

include "../includes/connection.php";

// Prepare the SQL statement to insert the polygon with name
$stmt = $pdo->prepare("INSERT INTO routes (route_name, route_lat, route_long, radius, coordinates) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([
    $data['route_name'], 
    $data['route_lat'], 
    $data['route_long'], 
    $data['radius'], 
    $data['coordinates']
]);

echo json_encode(['status' => 'success']);
?>
