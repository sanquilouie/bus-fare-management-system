<?php
require_once "../includes/connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $action = $_POST['action'];

    if ($action === 'toggle') {
        $result = $conn->query("UPDATE features SET is_active = NOT is_active WHERE id = $id");
        echo json_encode(['success' => $result]);
    } elseif ($action === 'delete') {
        $result = $conn->query("DELETE FROM features WHERE id = $id");
        echo json_encode(['success' => $result]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}
