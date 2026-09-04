<?php
require_once '../includes/security.php';
bfms_require_roles(['Admin', 'Superadmin']);
header('Content-Type: application/json');

include "../includes/connection.php";

// Fetch all routes
$stmt = $pdo->query("SELECT route_id, route_name, route_lat, route_long, radius, coordinates FROM routes");
$routes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the routes as JSON
echo json_encode($routes);
?>
