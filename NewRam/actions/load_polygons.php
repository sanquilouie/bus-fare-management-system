<?php
header('Content-Type: application/json');

include "../includes/connection.php";

$pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);

// Fetch all routes
$stmt = $pdo->query("SELECT * FROM routes");
$routes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the routes as JSON
echo json_encode($routes);
?>
