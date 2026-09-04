<?php
require_once '../includes/mailer.php';
require_once '../includes/passwords.php';
require_once '../includes/security.php';
bfms_start_secure_session();

// Include your database connection
include '../includes/connection.php';

// Function to generate OTP
function generateOTP($length = 6) {
    $minimum = 10 ** ($length - 1);
    $maximum = (10 ** $length) - 1;
    return (string) random_int($minimum, $maximum);
}

// Initialize otp_sent and password_updated variables to avoid undefined warnings
$otp_sent = false;
$password_updated = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    bfms_require_csrf_token();
}

// Handle Forgot Password form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'forgot_password' && isset($_POST['email'])) {
    // Collect form data
    $email = $_POST['email'];
    $otp = generateOTP();  // Generate OTP

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT firstname, lastname, email FROM useracc WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Send OTP via email
        try {
            $mail = bfms_create_mailer();
            $mail->addAddress($email, $user['firstname'] . ' ' . $user['lastname']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body = "
                <p>Dear {$user['firstname']},</p>
                <p>Your OTP for password reset is <strong>$otp</strong></p>
                <p>This OTP will expire in 15 minutes. Please use it to reset your password.</p>
                <p>Best regards,<br>RAMSTAR</p>
            ";

            $mail->send();

            // Store only a hash of the OTP and its expiration time.
            $otpHash = password_hash($otp, PASSWORD_DEFAULT);
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes')); // OTP expires in 15 minutes
            $stmt = $conn->prepare("UPDATE useracc SET otp = ?, otp_expiry = ? WHERE email = ?");
            $stmt->bind_param("sss", $otpHash, $otp_expiry, $email);
            $stmt->execute();

            $otp_sent = true;  // Flag to indicate OTP was sent successfully
        } catch (Exception $e) {
            error_log('Password-reset email failed: ' . $e->getMessage());
            $error_message = 'Unable to send the password-reset email right now.';
        }
    } else {
        // Keep the public response generic so the endpoint does not enumerate accounts.
        $otp_sent = true;
    }

    $stmt->close();
}

// Handle OTP verification and password reset
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'verify_otp' && isset($_POST['email'])) {
    // Collect form data
    $email = $_POST['email'];
    $entered_otp = (string) ($_POST['otp'] ?? '');
    $new_password = (string) ($_POST['new_password'] ?? '');
    $confirm_password = (string) ($_POST['confirm_password'] ?? '');

    $strongPassword = strlen($new_password) >= 8
        && preg_match('/[A-Z]/', $new_password)
        && preg_match('/[a-z]/', $new_password)
        && preg_match('/[0-9]/', $new_password)
        && preg_match('/[^\w]/', $new_password);
    if (!$strongPassword) {
        $error_message = 'Choose a password with at least eight characters, including uppercase, lowercase, number, and symbol.';
    } elseif (!hash_equals($new_password, $confirm_password)) {
        $strongPassword = false;
        $error_message = 'The password confirmation does not match.';
    }

    $stmt = $conn->prepare("SELECT firstname, lastname, otp, otp_expiry FROM useracc WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

// Check if 'firstname' and 'lastname' exist and are not empty
if ($user) {
    $firstname = isset($user['firstname']) && !empty($user['firstname']) ? $user['firstname'] : 'User'; // Default to 'User' if not found
    $lastname = isset($user['lastname']) && !empty($user['lastname']) ? $user['lastname'] : '';  // Empty last name if not found
    $fullname = $firstname . ' ' . $lastname;
} else {
    $fullname = 'User'; // If no user found
}
    

    if ($user && $strongPassword) {
        // Check if OTP matches and is not expired
        $storedOtp = (string) ($user['otp'] ?? '');
        $isHashedOtp = !empty(password_get_info($storedOtp)['algo']);
        $otpMatches = $isHashedOtp
            ? password_verify($entered_otp, $storedOtp)
            : ($storedOtp !== '' && hash_equals($storedOtp, $entered_otp));

        if ($otpMatches && strtotime($user['otp_expiry']) > time()) {
            $hashed_password = bfms_hash_password_for_database($conn, $new_password);
            $stmt = $conn->prepare("UPDATE useracc SET password = ?, otp = NULL, otp_expiry = NULL WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);
            if ($stmt->execute()) {
                $password_updated = true;  // Password updated successfully
                
                // Send confirmation email about password change
               // Send confirmation email about password change
try {
    $mail = bfms_create_mailer();
    $mail->addAddress($email,$fullname);


    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Successful';

    // Check if firstname and lastname exist in the $user array
    $firstname = isset($user['firstname']) ? $user['firstname'] : 'User';
    $lastname = isset($user['lastname']) ? $user['lastname'] : '';

    $mail->Body = "
        <p>Dear {$firstname} {$lastname},</p>
        <p>Your password has been successfully updated.</p>
        <p>If you did not request this change, please contact support immediately.</p>
        <p>Best regards,<br>RAMSTAR</p>
    ";

    $mail->send();
} catch (Exception $e) {
    error_log('Password-change confirmation email failed: ' . $e->getMessage());
}

            } else {
                $error_message = "Error updating password.";
            }
        } else {
            $error_message = "Invalid or expired OTP.";
        }
    } elseif (!$user) {
        $error_message = "Invalid or expired reset request.";
    }

    $stmt->close();
}


$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Forgot Password</title>
   <link rel="stylesheet" href="../assets/css/login.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.3/dist/css/bootstrap.min.css">
   <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.6.5/dist/sweetalert2.min.css" rel="stylesheet">

   <style>
     body {
        background: white;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
     }

     header {
        background: linear-gradient(to right, rgb(243, 75, 83), rgb(131, 4, 4));
        color: white;
        padding: 15px 0;
        width: 100%;
        position: fixed;
        top: 0;
        z-index: 1000;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
     }

     header nav ul {
        list-style: none;
        margin: 0;
        padding: 0;
        text-align: center;
     }

     header nav ul li {
        display: inline-block;
        margin: 0 15px;
     }

     header nav ul li a {
        color: white;
        font-size: 16px;
        font-weight: bold;
        text-decoration: none;
        padding: 10px 20px;
        background: #f1c40f;
        border-radius: 30px;
        transition: 0.3s;
     }

     header nav ul li a:hover {
        background: #e67e22;
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
     }

     .page-content {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        padding: 20px;
        margin-top: 100px;
     }

     .form-container {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(5px);
        padding: 25px 20px;
        border-radius: 10px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        width: 100%;
        max-width: 400px;
        margin: 0 15px;
     }

     .form-container h2 {
        text-align: center;
        color: #333;
        margin-bottom: 15px;
        font-weight: bold;
     }

     label {
        font-size: 14px;
        color: #555;
        margin-bottom: 6px;
        display: block;
     }

     input[type="email"],
     input[type="text"],
     input[type="password"] {
        width: 100%;
        padding: 10px;
        margin: 6px 0 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
        background: #f9f9f9;
        transition: all 0.3s ease-in-out;
     }

     input[type="email"]:focus,
     input[type="text"]:focus,
     input[type="password"]:focus {
        border-color: #3498db;
        outline: none;
        background: #ffffff;
     }

     button {
        width: 100%;
        padding: 10px;
        background-color: #f1c40f;
        border: none;
        color: black;
        font-size: 14px;
        font-weight: bold;
        border-radius: 25px;
        margin-bottom: 10px;
        transition: all 0.3s ease-in-out;
        cursor: pointer;
     }

     button:hover {
        background-color: #e67e22;
        transform: scale(1.05);
     }

     .password-field {
        position: relative;
     }

     .password-field i {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
     }

     small#password-strength-text,
     small#match-text {
        min-height: 1em;
        display: block;
        transition: all 0.3s ease-in-out;
     }

     @media (max-width: 600px) {
        .form-container {
           padding: 20px 15px;
        }

        header nav ul li a {
           font-size: 14px;
           padding: 8px 15px;
        }
     }
   </style>
</head>

<body>
   <header>
      <nav>
         <ul>
            <li><a href="../../index.php">Home</a></li>
         </ul>
      </nav>
   </header>

   <?php if ($otp_sent && isset($email)) { ?>
   <div class="alert alert-success text-center mt-5" role="alert" 
        style="position: fixed; top: 5px; left: 50%; transform: translateX(-50%); z-index: 1050; max-width: 500px; width: 90%;">
      OTP has been sent to <strong><?php echo htmlspecialchars($email); ?></strong>. Please check your inbox.
   </div>
   <?php } ?>

   <div class="page-content">
      <?php if (!$otp_sent) { ?>
         <div class="form-container">
            <h2>Forgot Password</h2>
            <form method="POST">
               <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(bfms_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
               <input type="hidden" name="action" value="forgot_password">
               <label for="email">Email Address:</label>
               <input type="email" name="email" placeholder="Enter your email" required>
               <button type="submit">Send OTP</button>
               <button type="button" onclick="window.location.href='login.php'">Go Back</button>
            </form>
         </div>
      <?php } ?>

      <?php if ($otp_sent) { ?>
         <div class="form-container">
            <h2>Verify OTP</h2>
            <form id="reset-password-form" method="POST">
               <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(bfms_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
               <input type="hidden" name="action" value="verify_otp">
               <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

               <input type="text" name="otp" placeholder="Enter OTP" required>

               <div class="password-field">
                  <input type="password" name="new_password" id="new_password" placeholder="Enter new password" required>
                  <i id="toggleNew" class="fa-solid fa-eye"></i>
               </div>
               <small id="password-strength-text"></small>

               <div class="password-field">
                  <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required>
                  <i id="toggleConfirm" class="fa-solid fa-eye"></i>
               </div>
               <small id="match-text"></small>

               <button type="submit">Reset Password</button>
               <button type="button" onclick="window.location.href='login.php'">Go Back</button>
            </form>
         </div>
      <?php } ?>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

   <script>
      <?php if ($otp_sent) { ?>
         Swal.fire({
            icon: 'success',
            title: 'OTP Sent!',
            text: 'OTP has been sent to your email. Please check your inbox.',
            confirmButtonText: 'OK'
         });
      <?php } elseif ($password_updated) { ?>
         Swal.fire({
            icon: 'success',
            title: 'Password Reset Successful!',
            text: 'Your password has been updated successfully.',
            confirmButtonText: 'OK'
         }).then(() => {
            window.location.href = 'login.php';
         });
      <?php } elseif ($error_message) { ?>
         Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?php echo $error_message; ?>',
            confirmButtonText: 'OK'
         });
      <?php } ?>

      function toggleVisibility(inputId, toggleIconId) {
         const input = document.getElementById(inputId);
         const icon = document.getElementById(toggleIconId);
         icon.addEventListener('click', () => {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            icon.classList.toggle('fa-eye-slash');
            icon.classList.toggle('fa-eye');
         });
      }

      toggleVisibility('new_password', 'toggleNew');
      toggleVisibility('confirm_password', 'toggleConfirm');

      const newPassword = document.getElementById('new_password');
      const strengthText = document.getElementById('password-strength-text');

      newPassword?.addEventListener('input', () => {
         const pwd = newPassword.value;
         let score = 0;
         if (pwd.length >= 6) score++;
         if (/[A-Z]/.test(pwd)) score++;
         if (/[0-9]/.test(pwd)) score++;
         if (/[^A-Za-z0-9]/.test(pwd)) score++;

         let label = '', color = '';
         if (pwd.length === 0) {
            label = '';
         } else if (score <= 1) {
            label = 'Weak';
            color = 'text-danger';
         } else if (score === 2) {
            label = 'Medium';
            color = 'text-warning';
         } else {
            label = 'Strong';
            color = 'text-success';
         }

         strengthText.textContent = label;
         strengthText.className = `${color} mt-1 d-block`;
      });

      const confirmPassword = document.getElementById('confirm_password');
      const matchText = document.getElementById('match-text');

      confirmPassword?.addEventListener('input', () => {
         if (confirmPassword.value !== newPassword.value) {
            matchText.textContent = "Passwords do not match";
            matchText.className = "text-danger mt-1 d-block";
         } else {
            matchText.textContent = "Passwords match";
            matchText.className = "text-success mt-1 d-block";
         }
      });

      document.getElementById("reset-password-form")?.addEventListener('submit', function(event) {
         if (newPassword.value !== confirmPassword.value) {
            event.preventDefault();
            Swal.fire({
               icon: 'error',
               title: 'Password Mismatch',
               text: 'The passwords do not match. Please try again.',
               confirmButtonText: 'OK'
            });
         }
      });
   </script>
</body>
</html>

