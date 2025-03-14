<?php
session_start();
include '../config/connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log activities
function logActivity($conn, $user_id, $action, $performed_by)
{
    $logQuery = "INSERT INTO activity_logs (user_id, action, performed_by) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($logQuery);
    $stmt->bind_param("iss", $user_id, $action, $performed_by);
    $stmt->execute();
    $stmt->close();
}

// Disable user action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id']) && isset($_POST['new_account_number'])) {
    $user_id = $_POST['user_id'];
    $newAccountNumber = $_POST['new_account_number'];

    if ($user_id && $newAccountNumber) {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Fetch current user details
            $userQuery = "SELECT * FROM useracc WHERE id = ? AND is_activated = 1";
            $stmt = $conn->prepare($userQuery);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $userResult = $stmt->get_result();

            if ($userResult->num_rows === 1) {
                $userData = $userResult->fetch_assoc();

                // Check if the current account_number (RFID) exists in useracc
                $checkCurrentAccountQuery = "SELECT * FROM useracc WHERE account_number = ? AND id != ?";
                $stmt = $conn->prepare($checkCurrentAccountQuery);
                $stmt->bind_param("si", $userData['account_number'], $user_id);
                $stmt->execute();
                $currentAccountResult = $stmt->get_result();

                if ($currentAccountResult->num_rows > 0) {
                    echo json_encode(['success' => false, 'message' => "The current RFID already exists in active user accounts."]);
                    exit;
                }

                // Check if the current RFID (or corresponding identifier) exists in deactivated accounts
                $checkDeactivatedQuery = "SELECT * FROM deactivated_accounts WHERE original_account_number = ?";
                $stmt = $conn->prepare($checkDeactivatedQuery);
                $stmt->bind_param("s", $userData['account_number']);
                $stmt->execute();
                $deactivatedResult = $stmt->get_result();

                // Check if the current RFID exists in deactivated accounts
                if ($deactivatedResult->num_rows > 0) {
                    echo json_encode(['success' => false, 'message' => "The current RFID is in the deactivated accounts."]);
                    exit; // Stop further processing
                }

                // Check if the new account number already exists
                $checkNewAccountQuery = "SELECT * FROM useracc WHERE account_number = ?";
                $stmt = $conn->prepare($checkNewAccountQuery);
                $stmt->bind_param("s", $newAccountNumber);
                $stmt->execute();
                $newAccountResult = $stmt->get_result();

                // Check if the new account number already exists
                if ($newAccountResult->num_rows > 0) {
                    echo json_encode(['success' => false, 'message' => "The new account number already exists."]);
                    exit; // Stop further processing
                }

                // Update the current user with the new account number and set the balance
                $updateAccountQuery = "UPDATE useracc SET account_number = ?, balance = ? WHERE id = ?";
                $stmt = $conn->prepare($updateAccountQuery);
                $stmt->bind_param("ssi", $newAccountNumber, $userData['balance'], $user_id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update account number and balance: " . $stmt->error);
                }

                // Insert user details into the deactivated accounts table
                $historyQuery = "INSERT INTO deactivated_accounts (original_account_number, firstname, middlename, lastname, birthday, age, gender, address, balance, deactivated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($historyQuery);
                $stmt->bind_param(
                    "ssssssssd",
                    $userData['account_number'],
                    $userData['firstname'],
                    $userData['middlename'],
                    $userData['lastname'],
                    $userData['birthday'],
                    $userData['age'],
                    $userData['gender'],
                    $userData['address'],
                    $userData['balance']
                );
                $stmt->execute();

                // Disable the original account
                $disableAccountQuery = "UPDATE useracc SET is_activated = 0 WHERE id = ?";
                $stmt = $conn->prepare($disableAccountQuery);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();

                // Log the activity of disabling the user
                logActivity($conn, $user_id, 'Transferred Funds And Disabled', $_SESSION['firstname'] . ' ' . $_SESSION['lastname']);

                // Commit the transaction
                $conn->commit();

                // Fetch updated list of users
                $userListQuery = "SELECT id, firstname, middlename, lastname, birthday, age, gender, address, province, municipality, barangay, account_number, balance 
                                  FROM useracc WHERE is_activated = 1";
                $userResult = mysqli_query($conn, $userListQuery);

                $updatedTableData = '';

                // Build the updated table rows
                while ($row = mysqli_fetch_assoc($userResult)) {
                    $updatedTableData .= '<tr>
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
                            <form id="disableForm' . $row['id'] . '" method="POST">
                                <input type="hidden" name="user_id" value="' . $row['id'] . '">
                                <button type="button" onclick="confirmDisable(' . $row['id'] . ')" class="btn btn-danger btn-sm">Disable</button>
                            </form>
                        </td>
                    </tr>';
                }

                // Return the updated table rows as a JSON response
                echo json_encode(['success' => true, 'tableData' => $updatedTableData]);

            } else {
                throw new Exception("User  not found or already disabled.");
            }

        } catch (Exception $e) {
            // Rollback the transaction in case of any failure
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => "Transaction failed: " . $e->getMessage()]);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => "User  ID or new account number is missing."]);
    }
    exit;
}