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

if ($_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("UPDATE features SET title = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $title, $description, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update']);
    }

    exit;
}
