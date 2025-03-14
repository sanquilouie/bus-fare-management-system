<?php
session_start();
include "../includes/connection.php";

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../index.php");
    exit();
}

function logActivity($conn, $user_id, $action, $performed_by)
{
    $logQuery = "INSERT INTO activity_logs (user_id, action, performed_by) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($logQuery);
    $stmt->bind_param("iss", $user_id, $action, $performed_by);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];

    if (empty($userId)) {
        echo json_encode(['success' => false, 'message' => 'User  ID is missing.']);
        exit;
    }

    try {
        // Disable the user account by setting is_activated to 0
        $stmt = $conn->prepare("UPDATE useracc SET is_activated = 0 WHERE id = ?");

        // Bind the parameter (s for string, i for integer, d for double, b for blob)
        $stmt->bind_param("i", $userId); // Assuming user_id is an integer

        if ($stmt->execute()) {
            logActivity($conn, $userId, 'Deactivated', $_SESSION['firstname'] . ' ' . $_SESSION['lastname']);

            // Fetch updated data to refresh the table
            $tableData = fetchUpdatedTableData($conn);

            echo json_encode(['success' => true, 'tableData' => $tableData]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to disable the user.']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
}

// Function to fetch updated table data
function fetchUpdatedTableData($conn)
{
    $stmt = $conn->query("SELECT * FROM useracc");
    $users = $stmt->fetch_all(MYSQLI_ASSOC);

    $tableData = '';
    foreach ($users as $user) {
        $tableData .= "<tr>
            <td>{$user['id']}</td>
            <td>{$user['account_number']}</td>
            <td>{$user['firstname']} {$user['lastname']}</td>
            <td>{$user['balance']}</td>
            <td>" . ($user['is_activated'] ? 'Active' : 'Disabled') . "</td>
        </tr>";
    }
    return $tableData;
}
?>