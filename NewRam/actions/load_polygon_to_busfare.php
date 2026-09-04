<?php
require_once '../includes/security.php';
bfms_require_roles(['Conductor', 'Admin', 'Superadmin']);
header('Content-Type: application/json');

include "../includes/connection.php";

// Fetch all routes
$stmt = $pdo->query("SELECT route_id, route_name, route_lat, route_long, radius, coordinates FROM routes");
$routes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Decode the coordinates and prepare for use in JavaScript
foreach ($routes as &$route) {
    // Decode the coordinates stored in the database (assumed to be JSON)
    $route['polygon'] = json_decode($route['coordinates'], true); // Decoding the coordinates into an array
}

// Return the routes with polygons as JSON
echo json_encode($routes);
?>
