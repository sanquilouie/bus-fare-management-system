<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['direction'])) {
    $_SESSION['direction'] = $_POST['direction'];
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
