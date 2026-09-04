<?php

require_once __DIR__ . '/config.php';

$dbhost = bfms_required_env('DB_HOST');
$dbport = (int) bfms_env('DB_PORT', '3306');
$dbuser = bfms_required_env('DB_USER');
$dbpass = bfms_required_env('DB_PASSWORD');
$dbname = bfms_required_env('DB_NAME');
$timezone = bfms_env('APP_TIMEZONE', 'Asia/Manila');
$dbTimezone = bfms_env('DB_TIME_ZONE', '+08:00');

date_default_timezone_set($timezone);

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname, $dbport);
if (!$conn) {
    error_log('MySQLi connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    exit('Database connection unavailable.');
}

mysqli_set_charset($conn, 'utf8mb4');

$timezoneStatement = mysqli_prepare($conn, 'SET time_zone = ?');
if (!$timezoneStatement) {
    error_log('Unable to prepare the MySQLi session timezone setting.');
    http_response_code(500);
    exit('Database connection unavailable.');
}
$timezoneStatement->bind_param('s', $dbTimezone);
if (!$timezoneStatement->execute()) {
    error_log('Unable to set the MySQLi session timezone: ' . $timezoneStatement->error);
    $timezoneStatement->close();
    http_response_code(500);
    exit('Database connection unavailable.');
}
$timezoneStatement->close();

try {
    $dsn = "mysql:host={$dbhost};port={$dbport};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbuser, $dbpass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $timezoneStatement = $pdo->prepare('SET time_zone = ?');
    $timezoneStatement->execute([$dbTimezone]);
} catch (PDOException $exception) {
    error_log('PDO connection failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('Database connection unavailable.');
}
