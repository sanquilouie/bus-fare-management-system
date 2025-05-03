<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../includes/connection.php";

if (isset($_POST['query'])) {
    $search = mysqli_real_escape_string($conn, $_POST['query']);

    $searchQuery = "SELECT id, firstname, middlename, lastname, birthday, age, gender, address,province,municipality,barangay, account_number, balance, status 
                    FROM useracc 
                    WHERE is_activated = 1 AND role = 'User'
                    AND (account_number LIKE '%$search%' OR email LIKE '%$search%')"; // Assuming you have an email column

    $result = mysqli_query($conn, $searchQuery);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $status = isset($row['is_activated']) ? ($row['is_activated'] == 1 ? 'Activated' : 'Disabled') : 'N/A';

            echo '<tr>
                    <td>' . $row['id'] . '</td>
                    <td>' . $row['firstname'] . '</td>
                    <td>' . $row['middlename'] . '</td>
                    <td>' . $row['lastname'] . '</td>
                    <td>' . date('F j, Y', strtotime($row['birthday'])) . '</td>
                    <td>' . $row['age'] . '</td>
                    <td>' . $row['gender'] . '</td>
                    <td>' . $row['account_number'] . '</td>  
                    <td>₱' . number_format($row['balance'], 2) . '</td>
                    <td>' . $status . '</td>   
                </tr>';
        }
    } else {
        echo '<tr><td colspan="11" class="text-center">No results found</td></tr>';
    }
} else {
    echo '<tr><td colspan="11" class="text-center">No query provided</td></tr>';
}
?>