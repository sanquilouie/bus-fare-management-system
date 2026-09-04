<?php
require_once '../includes/security.php';
bfms_require_roles(['Admin', 'Superadmin']);
bfms_require_same_origin();
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
