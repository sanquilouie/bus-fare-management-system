<?php
require_once '../includes/security.php';
require_once '../includes/passwords.php';
bfms_start_secure_session();
ob_start();
include '../includes/connection.php';

$errors = [];
$msg = "";
// Helper function to get redirect URL based on role
function getRedirectURL($role)
{
   switch ($role) {
      case 'Admin':
         return '../pages/admin/dashboard.php';
      case 'Cashier':
         return '../pages/cashier/dashboard.php';
      case 'Superadmin':
         return '../pages/superadmin/dashboard.php';
      case 'User':
         return '../pages/user/dashboard.php';
      case 'Conductor':
         return '../pages/conductor/dashboard.php';
      case 'Inspector':
         return '../pages/inspector/dashboard.php';
      default:
         return '../index.php';
   }
}
if (isset($_POST['Login'])) {
   bfms_require_csrf_token();
   $username = trim($_POST['username'] ?? '');
   $password = $_POST['password'] ?? '';

   // Normalize contact number: if it starts with +63, replace with 0
   if (strpos($username, '+63') === 0) {
      $username = '0' . substr($username, 3);
   }

   $errors = [];
   if (empty($username)) {
      $errors[] = "Username is required!";
   }
   if (empty($password)) {
      $errors[] = "Password is required!";
   }

   if (empty($errors)) {
      $checkUser = $conn->prepare(
         'SELECT * FROM useracc WHERE email = ? OR account_number = ? LIMIT 1'
      );
      $checkUser->bind_param('ss', $username, $username);
      $checkUser->execute();
      $row = $checkUser->get_result()->fetch_assoc();
      $checkUser->close();

      $upgradedHash = null;
      if ($row && bfms_verify_password($password, (string) $row['password'], $upgradedHash)) {
         if ($upgradedHash !== null && bfms_password_column_supports_modern_hash($conn)) {
            $upgrade = $conn->prepare('UPDATE useracc SET password = ? WHERE id = ?');
            $upgrade->bind_param('si', $upgradedHash, $row['id']);
            $upgrade->execute();
            $upgrade->close();
         } elseif ($upgradedHash !== null) {
            error_log('Legacy password was verified but could not be upgraded because the password column is too narrow.');
         }

         if ($row['is_activated'] == 0) {
            $msg = "<div class='alert alert-warning' style='background-color:#FFA500; text-align:center; color:#FFFFFF;'>Your account is not activated! Please contact support.</div>";
         } else {
            $user_account_number = $row['account_number'];
            $is_email_login = filter_var($username, FILTER_VALIDATE_EMAIL);

            // If logging in using email, check if that account_number is in use in businfo
            if ($is_email_login) {
               $businfoCheck = $conn->prepare('SELECT 1 FROM businfo WHERE conductorID = ? LIMIT 1');
               $businfoCheck->bind_param('s', $user_account_number);
               $businfoCheck->execute();
               $businfoCheck->store_result();
               $accountIsInUse = $businfoCheck->num_rows > 0;
               $businfoCheck->close();

               if ($accountIsInUse) {
                  if (!isset($_SESSION['account_number']) || $_SESSION['account_number'] !== $user_account_number) {
                     $msg = "<div class='alert alert-danger' style='background-color:#BF0210; text-align:center; color:#FFFFFF;'>Login restricted: this account is currently in use.</div>";
                  } else {
                     // Session account matches, allow login
                     bfms_establish_user_session($row);

                     echo "
                     <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                     <script>
                     document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                              title: 'Login Successfully',
                              text: 'Welcome! Your role is: " . htmlspecialchars($row['role']) . "',
                              icon: 'success',
                              showConfirmButton: false,
                              timer: 1000
                        }).then((result) => {
                              window.location.href = '" . getRedirectURL($row['role']) . "';
                        });
                     });
                     </script>";
                     exit;
                  }
               } else {
                  // Not in businfo, proceed with login
                  bfms_establish_user_session($row);

                  echo "
                  <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                  <script>
                  document.addEventListener('DOMContentLoaded', function () {
                     Swal.fire({
                           title: 'Login Successfully',
                           text: 'Welcome! Your role is: " . htmlspecialchars($row['role']) . "',
                           icon: 'success',
                           showConfirmButton: false,
                           timer: 1000
                     }).then((result) => {
                           window.location.href = '" . getRedirectURL($row['role']) . "';
                     });
                  });
                  </script>";
                  exit;
               }
            } else {
               // Username is not email (likely account_number)
               if (!isset($_SESSION['account_number'])) {
                  $businfoCheck = $conn->prepare('SELECT 1 FROM businfo WHERE conductorID = ? LIMIT 1');
                  $businfoCheck->bind_param('s', $username);
                  $businfoCheck->execute();
                  $businfoCheck->store_result();
                  $accountIsInUse = $businfoCheck->num_rows > 0;
                  $businfoCheck->close();

                  if ($accountIsInUse) {
                     $msg = "<div class='alert alert-danger' style='background-color:#BF0210; text-align:center; color:#FFFFFF;'>Login restricted: this account is currently in use.</div>";
                  } else {
                     bfms_establish_user_session($row);

                     echo "
                     <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                     <script>
                     document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                              title: 'Login Successfully',
                              text: 'Welcome! Your role is: " . htmlspecialchars($row['role']) . "',
                              icon: 'success',
                              showConfirmButton: false,
                              timer: 1000
                        }).then((result) => {
                              window.location.href = '" . getRedirectURL($row['role']) . "';
                        });
                     });
                     </script>";
                     exit;
                  }
               } else {
                  // Session is set, compare account_number to username
                  if ($_SESSION['account_number'] === $username) {
                     bfms_establish_user_session($row);

                     echo "
                     <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                     <script>
                     document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                              title: 'Login Successfully',
                              text: 'Welcome! Your role is: " . htmlspecialchars($row['role']) . "',
                              icon: 'success',
                              showConfirmButton: false,
                              timer: 1000
                        }).then((result) => {
                              window.location.href = '" . getRedirectURL($row['role']) . "';
                        });
                     });
                     </script>";
                     exit;
                  } else {
                     $msg = "<div class='alert alert-danger' style='background-color:#BF0210; text-align:center; color:#FFFFFF;'>Login restricted: you are not allowed to use another account while logged in.</div>";
                  }
               }
            }
         }
      } else {
         $msg = "<div class='alert alert-danger' style='background-color:#BF0210; text-align:center; color:#FFFFFF;'>Invalid Credentials!</div>";
      }
   } else {
      foreach ($errors as $error) {
         $msg .= "<div class='alert alert-danger' style='background-color:#BF0210; text-align:center; color:#FFFFFF;'>{$error}</div>";
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.3/dist/css/bootstrap.min.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <script src="/NewRam/assets/js/NFCScanner_Login.js"></script>
   <style>
      html, body {
         height: 100%;
         margin: 0;
         padding: 0;
      }
      body {
         background: url('../assets/images/newbus2.jpg') no-repeat center center fixed;
         background-size: cover;
         font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
         margin: 0;
         padding: 0;
         display: flex;
         flex-direction: column;
         min-height: 100vh;
      }

      .main-content {
         flex: 1;
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 20px; /* optional */
      }

      .field {
         margin-bottom: 20px;
         position: relative;
      }

      .field input {
         width: 100%;
         padding: 12px 15px;
         font-size: 16px;
         border: 1px solid #ccc;
         border-radius: 4px;
         background-color: #f9f9f9;
         box-sizing: border-box;
         transition: border-color 0.3s ease;
      }

      .field input:hover {
         transform: scale(1.05);
         transition: transform 0.3s ease;
      }

      .field input:focus {
         outline: none;
         border-color: #3498ab;
      }

      .field i {
         position: absolute;
         right: 15px;
         top: 50%;
         transform: translateY(-50%);
         cursor: pointer;
         color: #aaa;
         transition: color 0.3s ease;
      }

      .field i:hover {
         color: #3498db;
      }

      .btn {
         background: #f1c40f;
         color: black;
         font-size: 16px;
         font-weight: 500;
         padding: 12px 25px;
         border: none;
         border-radius: 25px;
         cursor: pointer;
         transition: all 0.3s ease-in-out;
         margin-bottom: 10px;
      }

      .btn:hover {
         background-color: #e67e22;
         transform: scale(1.05);
         transition: transform 0.3s ease;
      }

      .alert {
         text-align: center;
         padding: 12px;
         border-radius: 5px;
         margin-bottom: 20px;
         font-size: 14px;
      }

      .alert-warning {
         background-color: #FFA500;
         color: white;
      }

      .alert-danger {
         background-color: #BF0210;
         color: white;
      }

      header {
         background: linear-gradient(to right, rgb(243, 75, 83), rgb(131, 4, 4));
         color: white;
         padding: 20px 0;
         box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      }

      header nav ul {
         list-style: none;
         margin: 0;
         padding: 0;
         margin-left: 5px;
      }

      header nav ul li {
         display: inline;
      }

      header nav ul li a {
         color: white;
         font-size: 16px;
         font-weight: bold;
         text-decoration: none;
         padding: 10px 20px;
         border-radius: 30px;
         background: #f1c40f;
         cursor: pointer;
         transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
      }

      header nav ul li a:hover {
         background: #e67e22;
         transform: scale(1.1);
         box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      }

      header nav ul li a:active {
         background: #f1c40f;
         transform: scale(1);
      }

      .social-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all 0.3s ease-in-out;
    }

    .social-icon:hover {
        transform: scale(1.1);
        opacity: 0.9;
    }

    /* Social Media Icons Styling */
    .social-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all 0.3s ease-in-out;
    }

    .social-icon:hover {
        transform: scale(1.1);
        opacity: 0.9;
    }

    /* Facebook Icon */
    .social-icon.facebook {
        background-color: #1877F2;
        /* Facebook Blue */
        color: white;
    }

    /* Twitter Icon */
    .social-icon.twitter {
        background-color: #1DA1F2;
        /* Twitter Blue */
        color: white;
    }

    /* Instagram Icon */
    .social-icon.instagram {
        background-color: #E1306C;
        /* Instagram Gradient Pink */
        color: white;
    }

    /* Gmail Icon */
    .social-icon.gmail {
        background-color: #DB4437;
        /* Gmail Red */
        color: white;
    }
      @media (max-width: 768px) {
         header nav ul li a {
            font-size: 18px;
            width: 100%;
         }
      }

      @media (max-width: 480px) {
         header nav ul li a {
            font-size: 16px;
            padding: 10px 15px;
         }
      }

      .forgot-link p {
  font-size: 20px;          /* Set the font size */
  color: #333;              /* Text color */
  font-family: Arial, sans-serif;  /* Font style */
  line-height: 1.5;         /* Line height for better readability */
  margin: 10px 0;           /* Spacing around the paragraph */
}

.forgot-link a {
  color: #007bff;           /* Link color */
  text-decoration: none;    /* Remove underline */
}

.forgot-link a:hover {
  text-decoration: underline; /* Underline on hover */
}

   footer {
        background: linear-gradient(to right, rgb(243, 75, 83), rgb(131, 4, 4));
        color: white;
        padding: 30px 20px;
    }

    .footer-content {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      flex-wrap: wrap;
      gap: 20px;
      width: 100%;
      margin: 0;
      padding: 0 20px; /* optional: add side padding if needed */
   }


    .footer-left,
    .footer-center,
    .footer-right {
        flex: 1;
        min-width: 250px;
    }

    footer h3 {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .social-icons {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 10px;
    }

    .footer-center {
        text-align: center;
    }

    .footer-left {
        text-align: left;
    }

    .footer-right {
        text-align: right;
    }
@media (max-width: 1024px) {
        .slider {
            height: 350px;
        }
        .footer-content {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .footer-left,
    .footer-center,
    .footer-right {
        text-align: center;
    }

    .social-icons {
        justify-content: center;
    }
    }

    @media (max-width: 768px) {
    .scrollable-item {
        flex: 1 1 100%;
    }
    .footer-content {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .footer-left,
    .footer-center,
    .footer-right {
        text-align: center;
    }

    .social-icons {
        justify-content: center;
    }
}

    @media (max-width: 480px) {
      .footer-content {
        flex-direction: column;
        align-items: center;
        text-align: center;
       }

    .footer-left,
    .footer-center,
    .footer-right {
        text-align: center;
    }

    .social-icons {
        justify-content: center;
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
   <div class="main-content">
      <div class="container">
         <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
            <div class="card border border-light-subtle rounded-3 shadow-sm" style="background-color: rgba(255, 255, 255, 0.7);">
               <div class="card-body p-3 p-md-4 p-xl-5">
                  <h2 class="fw-bold text-center mb-4" style="color: rgb(215, 185, 75);">Login Form</h2>
                  <form method="POST" action="#" class="login">
                     <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(bfms_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                     <?php echo $msg; ?>
                  <div class="row gy-2 overflow-hidden">
                     <div class="col-12">
                        <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="username" id="username" placeholder="Account Number/Email" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        <label for="username" class="form-label">Account Number/Email</label>
                        </div>
                     </div>
                     <div class="col-12">
                        <div class="form-floating mb-3 position-relative">
                           <input type="password" class="form-control" name="password" id="pass2" placeholder="Password" required>
                           <label for="password" class="form-label">Password</label>
                           <i class="fa fa-eye-slash position-absolute" id="togglePassword" style="right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                        </div>
                     </div>
                     <div class="col-12">
                        <div class="d-grid">
                        <button class="btn btn-primary btn-lg" type="submit" name="Login">Log in</button>
                        </div>
                     </div>
                     <div class="col-12">
                        <div class="d-grid">
                        <button class="btn btn-primary btn-lg" type="submit" onclick="window.location.href='userregister.php'">Sign Up</button>
                        </div>
                     </div>
                     <div class="col-12">
                        <p class="m-0 text-secondary text-center">Forgot Password? <a href="forgotpassword.php" class="link-primary text-decoration-none">Click Here</a></p>
                     </div>
                  </div>
                  </form>
               </div>
            </div>
            </div>
         </div>
      </div>
   </div>
  <!-- <footer class="footer-content">
      <div class="footer-left">
            <p>&copy; 2024 Ramstar Bus Transportation Cooperative | All rights reserved</p>
      </div>
      <div class="footer-center">
            <h3>Contact Us:</h3>
            <p>Phone No.: <i>(0967) 235 2590</i></p>
            <p>Email: <i>portfolio@example.invalid</i></p>
            <p>Address: <i>Purok 5, #235, San Rafael, Zaragoza, Nueva Ecija 3110</i></p>
      </div>
      <div class="footer-right">
         <div class="social-icons">
            <a href="https://www.facebook.com/people/Zaragoza-Ramstar-Transport-Cooperative/61550838758867/" target="_blank" class="social-icon facebook">
               <i class="fab fa-facebook-f"></i>
            </a>
            <a target="_blank" class="social-icon twitter">
               <i class="fab fa-twitter"></i>
            </a>
            <a class="social-icon instagram">
               <i class="fab fa-instagram"></i>
            </a>
            <a class="social-icon gmail">
               <i class="fab fa-google"></i>
            </a>
         </div>
      </div>
    </footer> -->
</body>
<script>
  // Toggle the password visibility
  document.getElementById('togglePassword').addEventListener('click', function() {
    var passwordField = document.getElementById('pass2');
    var icon = document.getElementById('togglePassword');

    if (passwordField.type === "password") {
      passwordField.type = "text"; // Show password
      icon.classList.remove("fa-eye-slash");
      icon.classList.add("fa-eye");
    } else {
      passwordField.type = "password"; // Hide password
      icon.classList.remove("fa-eye");
      icon.classList.add("fa-eye-slash");
    }
  });
</script>
</html>
