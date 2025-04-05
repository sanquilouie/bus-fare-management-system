<?php
// connection.php

// config.php

if ($_SERVER['HTTP_HOST'] === 'localhost') {
    // Local
    $dbhost = 'localhost';
    $dbuser = 'REDACTED_DB_USER';
    $dbpass = 'REDACTED_DB_PASSWORD';
    $dbname = 'REDACTED_DB_NAME';
} else {
    // Live
    $dbhost = 'localhost';
	$dbuser = 'REDACTED_DB_USER';
	$dbpass = 'REDACTED_DB_PASSWORD';
	$dbname = 'REDACTED_DB_NAME';
}

// Mysqli connection
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
	die("Failed to connect using MySQLi: " . mysqli_connect_error());
}

// PDO connection
try {
	$pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
	// Set the PDO error mode to exception
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	die("Failed to connect using PDO: " . $e->getMessage());
}
?>