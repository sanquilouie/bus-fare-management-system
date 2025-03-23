<?php
session_start();
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
      default:
         return 'index.php';
   }
}

if (isset($_POST['Login'])) {
   $username = mysqli_real_escape_string($conn, $_POST['username']);
   $password = mysqli_real_escape_string($conn, md5($_POST['password']));

   // Normalize contact number: if it starts with +63, replace with 0
   if (strpos($username, '+63') === 0) {
      $username = '0' . substr($username, 3);
   }

   // Validate inputs
   if (empty($username)) {
      $errors[] = "Username is required!";
   }
   if (empty($password)) {
      $errors[] = "Password is required!";
   }

   if (empty($errors)) {
      // Query to check user credentials
      $check_user_query = "
            SELECT * 
            FROM useracc 
            WHERE (email = '{$username}' OR account_number = '{$username}') 
            AND password = '{$password}'";

      $check_user = mysqli_query($conn, $check_user_query);

      if (!$check_user) {
         die("Database query failed: " . mysqli_error($conn));
      }

      if (mysqli_num_rows($check_user) > 0) {
         $row = mysqli_fetch_assoc($check_user);

         if ($row['is_activated'] == 0) {
            $msg = "<div class='alert alert-warning' style='background-color:#FFA500; text-align:center; color:#FFFFFF;'>Your account is not activated! Please contact support.</div>";
         } else {
            // Set session variables
            foreach ($row as $key => $value) {
               $_SESSION[$key] = $value;
            }

            // Trigger SweetAlert2 for successful login using JavaScript
            echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            title: 'Login Successfully',
            text: 'Welcome, " . htmlspecialchars($row['fullname']) . "! Your role is: " . htmlspecialchars($row['role']) . "',
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
   <link rel="stylesheet" href="../assets/css/login.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <style>
      body {
         background: url('../assets/images/bus1.jpg') no-repeat center center fixed;
         /* Add your image path here */
         background-size: cover;
         /* Ensures the image covers the full page */
         font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
         margin: 0;
         padding: 0;
      }

      .wrapper {
         max-width: 400px;
         margin: 100px auto;
         padding: 40px;
         background-color: rgba(255, 255, 255, 0.8);
         /* Transparent background */
         border-radius: 8px;
         box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      }

      .title-text {
         text-align: center;
         margin-bottom: 30px;
      }

      .title-text p {
         font-size: 28px;
         font-weight: 600;
         color: #333;
      }

      .form-container {
         margin-top: 10px;
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
         border-color: #3498db;
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
         text-align: center;
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

   </style>
   <script>
      function togglePassword(inputId, iconId) {
         var input = document.getElementById(inputId);
         var icon = document.getElementById(iconId);
         if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
         } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
         }
      }
    

     
   </script>
</head>

<body>
   <header>
      <nav>
         <ul>
            <li><a href="index.php">Home</a></li>
         </ul>
      </nav>
   </header>

   <div class="wrapper">
   <p>Login Form</p>
   <div class="form-container">
      <form method="POST" action="#" class="login">
         <?php echo $msg; ?>
         <div class="field">
            <input type="text" name="username" placeholder="Account Number/Email" required>
         </div>
         <div class="field">
            <input type="password" name="password" placeholder="Password" id="pass2" required>
            <i class="fas fa-eye" id="togglePassword2" onclick="togglePassword('pass2', 'togglePassword2')"></i>
         </div>
         <input type="submit" name="Login" value="Log in" class="btn">
         <button onclick="window.location.href='userregister.php'" class="btn">Sign up</button>
       
      </form>
     
      <div class="forgot-link">
      <p>Forgot Password? <a href="forgotpassword.php">Click here</a></p>
      </div>
   </div>
   </div>
</body>

</html>