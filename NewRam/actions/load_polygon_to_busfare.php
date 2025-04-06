<?php
header('Content-Type: application/json');

include "../includes/connection.php";

$pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);

// Fetch all routes
$stmt = $pdo->query("SELECT * FROM routes");
$routes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Decode the coordinates and prepare for use in JavaScript
foreach ($routes as &$route) {
    // Decode the coordinates stored in the database (assumed to be JSON)
    $route['polygon'] = json_decode($route['coordinates'], true); // Decoding the coordinates into an array
}

// Return the routes with polygons as JSON
echo json_encode($routes);
?>
