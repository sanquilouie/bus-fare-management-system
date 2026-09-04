<?php
require_once '../includes/security.php';
bfms_require_roles(['Admin', 'Superadmin']);
bfms_require_csrf_token();
require_once "../includes/connection.php";
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    bfms_json_error('Method not allowed.', 405);
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$action = (string) ($_POST['action'] ?? '');
if (!$id) {
    bfms_json_error('A valid feature ID is required.', 422);
}

if ($action === 'toggle') {
    $stmt = $conn->prepare('UPDATE features SET is_active = NOT is_active WHERE id = ?');
    $stmt->bind_param('i', $id);
} elseif ($action === 'delete') {
    $imageStmt = $conn->prepare('SELECT image FROM features WHERE id = ?');
    $imageStmt->bind_param('i', $id);
    $imageStmt->execute();
    $imageStmt->bind_result($imagePath);
    $imageFound = $imageStmt->fetch();
    $imageStmt->close();
    if (!$imageFound) {
        bfms_json_error('Feature not found.', 404);
    }

    $deleteStmt = $conn->prepare('DELETE FROM features WHERE id = ?');
    $deleteStmt->bind_param('i', $id);
    $deleted = $deleteStmt->execute() && $deleteStmt->affected_rows === 1;
    $deleteStmt->close();
    if (!$deleted) {
        bfms_json_error('Unable to delete the feature.', 500);
    }

    // Only delete files created by the hardened uploader, and only when no row still references them.
    if (preg_match('#^features/(feature_[a-f0-9]{32}\.(?:jpg|png|webp))$#', (string) $imagePath, $matches)) {
        $referenceStmt = $conn->prepare('SELECT COUNT(*) FROM features WHERE image = ?');
        $referenceStmt->bind_param('s', $imagePath);
        $referenceStmt->execute();
        $referenceStmt->bind_result($referenceCount);
        $referenceStmt->fetch();
        $referenceStmt->close();

        if ((int) $referenceCount === 0) {
            $featureDirectory = realpath(__DIR__ . '/../assets/images/features');
            $candidate = $featureDirectory ? realpath($featureDirectory . DIRECTORY_SEPARATOR . $matches[1]) : false;
            if ($candidate && dirname($candidate) === $featureDirectory && is_file($candidate) && !unlink($candidate)) {
                error_log('A deleted feature image could not be removed from storage.');
            }
        }
    }

    echo json_encode(['success' => true]);
    exit;
} elseif ($action === 'edit') {
    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    if ($title === '' || $description === '') {
        bfms_json_error('Title and description are required.', 422);
    }

    $stmt = $conn->prepare("UPDATE features SET title = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $title, $description, $id);
} else {
    bfms_json_error('Invalid action.', 422);
}

echo json_encode(['success' => $stmt->execute()]);
$stmt->close();
