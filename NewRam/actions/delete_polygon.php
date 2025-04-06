<?php
include '../includes/connection.php';

$data = json_decode(file_get_contents("php://input"));

if (isset($data->route_id)) {
    $route_id = $data->route_id;

    $stmt = $conn->prepare("DELETE FROM routes WHERE route_id = ?");
    $stmt->bind_param("i", $route_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid route ID']);
}
?>
