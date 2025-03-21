<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

include "../includes/connection.php";
include 'functions.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_account_number']) && isset($_POST['balance'])) {
    $userAccountNumber = $_POST['user_account_number'];
    $balanceToLoad = $_POST['balance'];

    if (loadUserBalance($conn, $userAccountNumber, $balanceToLoad)) {
        echo json_encode(['success' => 'Loaded successfully!']);
    } else {
        echo json_encode(['error' => 'Error loading balance.']);
    }
}
?>