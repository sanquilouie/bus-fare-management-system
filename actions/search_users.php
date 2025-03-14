<?php
session_start();
include "../includes/connection.php";

if (isset($_POST['query'])) {
    $search = mysqli_real_escape_string($conn, $_POST['query']);

    $searchQuery = "SELECT id, firstname, middlename, lastname, birthday, age, gender, address,province,municipality,barangay, account_number, balance 
                    FROM useracc 
                    WHERE is_activated = 1 
                    AND (account_number LIKE '%$search%' OR email LIKE '%$search%')"; // Assuming you have an email column

    $result = mysqli_query($conn, $searchQuery);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>
                    <td>' . $row['id'] . '</td>
                    <td>' . $row['firstname'] . '</td>
                    <td>' . $row['middlename'] . '</td>
                    <td>' . $row['lastname'] . '</td>
                    <td>' . date('F j, Y', strtotime($row['birthday'])) . '</td>
                    <td>' . $row['age'] . '</td>
                    <td>' . $row['gender'] . '</td>
                    <td>' . $row['address'] . '</td>
                        <td>' . $row['province'] . '</td>
                        <td>' . $row['municipality'] . '</td>
                        <td>' . $row['barangay'] . '</td>
                    <td>' . $row['account_number'] . '</td>
                    <td>₱' . number_format($row['balance'], 2) . '</td>
                    <td>
                        <form method="POST" onsubmit="return confirmDisable();" action="">
                            <input type="hidden" name="user_id" value="' . $row['id'] . '">
                            <button type="submit" name="disable_user" class="btn btn-danger btn-sm">Disable</button>
                        </form>
                    </td>
                </tr>';
        }
    } else {
        echo '<tr><td colspan="11" class="text-center">No results found</td></tr>';
    }
} else {
    echo '<tr><td colspan="11" class="text-center">No query provided</td></tr>';
}
?>