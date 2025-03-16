<?php
// load_balance.php
include '../../config/connection.php';
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
