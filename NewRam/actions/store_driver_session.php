<?php
session_start();

// Get the raw POST data and decode JSON
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['account_number'])) {
    $_SESSION['driver_account_number'] = $data['account_number'];
}
