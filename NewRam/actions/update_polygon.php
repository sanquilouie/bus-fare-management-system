<?php
header('Content-Type: application/json');

// Get the POST data
$data = json_decode(file_get_contents("php://input"), true);

include "../includes/connection.php";

// Prepare your database connection (make sure to configure this part correctly)
$pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);

// Prepare the SQL statement to update the polygon
$stmt = $pdo->prepare("UPDATE routes SET route_name = ?, coordinates = ? WHERE route_id = ?");
$stmt->execute([
    $data['route_name'], 
    $data['coordinates'],
    $data['route_id'] // The route ID to update
]);

echo json_encode(['status' => 'success']);
?>
