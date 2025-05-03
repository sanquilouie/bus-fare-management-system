<?php
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

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
	die("Failed to connect using MySQLi: " . mysqli_connect_error());
}

mysqli_query($conn, "SET time_zone = 'Asia/Manila'");

try {
    // Connect using PDO
    $pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set the time zone for the PDO connection
    $pdo->exec("SET time_zone = '+08:00'");
} catch (PDOException $e) {
    die("Failed to connect using PDO: " . $e->getMessage());
}
?>