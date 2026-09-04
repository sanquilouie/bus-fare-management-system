<?php
require_once '../includes/security.php';
bfms_require_roles(['Admin', 'Superadmin']);
bfms_require_same_origin();
include "../includes/connection.php";

if (isset($_POST['query'])) {
    $search = mysqli_real_escape_string($conn, $_POST['query']);

    $searchQuery = "SELECT id, firstname, middlename, lastname, birthday, age, gender, address,province,municipality,barangay, account_number, balance, is_activated 
                    FROM useracc 
                    WHERE is_activated = 1 AND role = 'User'
                    AND (account_number LIKE '%$search%' OR email LIKE '%$search%')";

    $result = mysqli_query($conn, $searchQuery);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $status = isset($row['is_activated']) ? ($row['is_activated'] == 1 ? 'Activated' : 'Disabled') : 'N/A';
            $safe = static function ($value) {
                return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            };

            echo '<tr>
                    <td>' . $safe($row['id']) . '</td>
                    <td>' . $safe($row['firstname']) . '</td>
                    <td>' . $safe($row['middlename']) . '</td>
                    <td>' . $safe($row['lastname']) . '</td>
                    <td>' . $safe(date('F j, Y', strtotime($row['birthday']))) . '</td>
                    <td>' . $safe($row['age']) . '</td>
                    <td>' . $safe($row['gender']) . '</td>
                    <td>' . $safe($row['account_number']) . '</td>
                    <td>₱' . number_format($row['balance'], 2) . '</td>
                    <td>' . $safe($status) . '</td>
                </tr>';
        }
    } else {
        echo '<tr><td colspan="11" class="text-center">No results found</td></tr>';
    }
} else {
    echo '<tr><td colspan="11" class="text-center">No query provided</td></tr>';
}
?>
