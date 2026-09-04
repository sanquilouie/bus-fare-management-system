<?php
require_once '../includes/mailer.php';
require_once '../includes/security.php';
bfms_require_roles(['Admin', 'Superadmin']);
bfms_require_same_origin();

include "../includes/connection.php";

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
            $userQuery = "SELECT id, account_number, balance, email, firstname, lastname
                          FROM useracc WHERE id = ? AND is_activated = 1 AND role = 'User'";
            $stmt = $conn->prepare($userQuery);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $userResult = $stmt->get_result();

            if ($userResult->num_rows === 1) {
                $userData = $userResult->fetch_assoc();

                // Check if the current account_number (RFID) exists in useracc
                $checkCurrentAccountQuery = "SELECT 1 FROM useracc WHERE account_number = ? AND id != ? LIMIT 1";
                $stmt = $conn->prepare($checkCurrentAccountQuery);
                $stmt->bind_param("si", $userData['account_number'], $user_id);
                $stmt->execute();
                $currentAccountResult = $stmt->get_result();

                if ($currentAccountResult->num_rows > 0) {
                    echo json_encode(['success' => false, 'message' => "The current RFID already exists in active user accounts."]);
                    exit;
                }

                // Check if the current RFID (or corresponding identifier) exists in deactivated accounts
                $checkDeactivatedQuery = "SELECT 1 FROM deactivated_accounts WHERE original_account_number = ? LIMIT 1";
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
                $checkNewAccountQuery = "SELECT 1 FROM useracc WHERE account_number = ? LIMIT 1";
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

                    try {
                        $mail = bfms_create_mailer();
                        $loginUrl = bfms_app_url('auth/login.php');
                        $mail->addAddress($userData['email'], $userData['firstname'] . ' ' . $userData['lastname']);
                        $mail->isHTML(true);
                        $mail->Subject = 'Registration Successful';
                        $mail->Body = "
                            <p>Dear {$userData['firstname']},</p>
                            <p>We’re pleased to inform you that your account has been successfully transferred.</p>
                            <p><strong>Your new account number is:</strong> $newAccountNumber</p>
                            <p>You can now log in at <a href='$loginUrl'>$loginUrl</a>.</p>
                            <p>Best regards,<br>
                            Ramstar Bus Transportation</p>
                        ";


                        $mail->send();
                    } catch (Exception $e) {
                        error_log('Account-transfer email failed: ' . $e->getMessage());
                    }

                // Log the activity of disabling the user
                logActivity($conn, $user_id, 'Transferred Funds And Disabled', $_SESSION['firstname'] . ' ' . $_SESSION['lastname']);

                // Commit the transaction
                $conn->commit();

                // Fetch updated list of users
                $userListQuery = "SELECT id, firstname, middlename, lastname, birthday, age, gender, address, province, municipality, barangay, account_number, balance
                                  FROM useracc WHERE is_activated = 1 AND role = 'User'";
                $userResult = mysqli_query($conn, $userListQuery);

                $updatedTableData = '';
                $safe = static function ($value) {
                    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
                };

                // Build the updated table rows
                while ($row = mysqli_fetch_assoc($userResult)) {
                    $updatedTableData .= '<tr>
                        <td>' . $safe($row['id']) . '</td>
                        <td>' . $safe($row['firstname']) . '</td>
                        <td>' . $safe($row['middlename']) . '</td>
                        <td>' . $safe($row['lastname']) . '</td>
                        <td>' . $safe(date('F j, Y', strtotime($row['birthday']))) . '</td>
                        <td>' . $safe($row['age']) . '</td>
                        <td>' . $safe($row['gender']) . '</td>
                        <td>' . $safe($row['address']) . '</td>
                        <td>' . $safe($row['province']) . '</td>
                        <td>' . $safe($row['municipality']) . '</td>
                        <td>' . $safe($row['barangay']) . '</td>
                        <td>' . $safe($row['account_number']) . '</td>
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
            error_log('Account transfer failed: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'The account transfer could not be completed.']);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => "User  ID or new account number is missing."]);
    }
    exit;
}
