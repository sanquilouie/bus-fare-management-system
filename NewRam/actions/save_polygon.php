<?php
header('Content-Type: application/json');

// Get the POST data
$data = json_decode(file_get_contents("php://input"), true);

include "../includes/connection.php";

// Prepare your database connection (make sure to configure this part correctly)
$pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);

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
