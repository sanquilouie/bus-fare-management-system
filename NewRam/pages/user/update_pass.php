<?php
session_start();
include '../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'User')) {
    header("Location: ../../index.php");
    exit();
} ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../assets/css/sidebars.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Use full version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            display: flex;
            background-color: rgba(133, 187, 101);
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            transition: 0.2s;
            z-index: 100000;
        }

        header.sticky {
            padding: 10px 10px;
            background: rgba(133, 187, 101, 0.8);
        }

        .logo {
            margin-left: 10px;
            width: 50px;
            height: 45px;
        }

        .indicator {
            display: none;
            margin-top: 5px;
        }

        .indicator span {
            display: inline-block;
            width: 33%;
            height: 5px;
            margin-right: 2px;
            background: lightgray;
            border-radius: 5px;
        }

        .indicator .weak.active {
            background: red;
        }

        .indicator .medium.active {
            background: orange;
        }

        .indicator .strong.active {
            background: green;
        }

        h4 {
            color: black;
        }

        .btn-success {
            background-color: #007BFF;
            /* New background color */
            color: white;
            /* Text color */
            border: none;
            /* Optional: Remove border */
        }

        .btn-success:hover {
            background-color: #0056b3;
            /* Darker shade for hover effect */
        }
    </style>
</head>

<script type="text/javascript">
    window.addEventListener("scroll", function () {
        var header = document.querySelector("header");
        header.classList.toggle("sticky", window.scrollY > 0);
    });

    function myFunction1() {
        var x = document.getElementById("pass1");
        var y = document.getElementById("pass2");
        var z = document.getElementById("pass3");
        if (x.type === "password" || y.type === "password") {
            x.type = "text";
            y.type = "text";
            z.type = "text";
        } else {
            x.type = "password";
            y.type = "password";
            z.type = "password";
        }
    }

    function updatePassword(event) {
        event.preventDefault(); // Prevent the default form submission

        const old_pass = document.getElementById("pass2").value;
        const Password = document.getElementById("pass1").value;
        const PasswordConf = document.getElementById("pass3").value;

        // Confirm action with SweetAlert before submitting
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to update your password?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Make AJAX request to update password
                fetch('../../actions/update_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        old_pass: old_pass,
                        Password: Password,
                        PasswordConf: PasswordConf
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                title: 'Updated!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location = '/NewRam/pages/user/update_pass.php'; // Redirect to profile page
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while updating the password. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
            }
        });
    }
</script>

<body>
<?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar2.php';
        include '../../includes/footer.php';
    ?>
    <div id="main-content" class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2>Update Password</h2>
                    <form id="updatePasswordForm" onsubmit="updatePassword(event)">
                        <div class="form-group">
                            <label for="pass2" class="form-label">Old Password</label>
                            <input type="password" class="form-control" name="old_pass" id="pass2"
                                placeholder="Old Password" required>
                        </div>
                        <div class="form-group">
                            <label for="pass1" class="form-label">New Password</label>
                            <input onkeyup="trigger()" type="password" class="form-control" name="Password"
                                id="pass1" placeholder="New Password" required>
                            <div class="indicator">
                                <span class="weak"></span>
                                <span class="medium"></span>
                                <span class="strong"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="pass3" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="PasswordConf" id="pass3"
                                placeholder="Confirm Password" required>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" onclick="myFunction1()">
                            <label class="form-check-label">Show Password</label>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-block">Update Password</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        const indicator = document.querySelector(".indicator");
        const input = document.querySelector("#pass1");
        const weak = document.querySelector(".weak");
        const medium = document.querySelector(".medium");
        const strong = document.querySelector(".strong");

        function trigger() {
            const password = input.value;
            indicator.style.display = "block";

            let no = 0;

            if (password.length < 8) {
                no = 1;
            }
            if (password.match(/[A-z]/)) {
                no++;
            }
            if (password.match(/[0-9]/)) {
                no++;
            }
            if (password.match(/[\W]/)) {
                no++;
            }

            weak.classList.remove("active");
            medium.classList.remove("active");
            strong.classList.remove("active");

            if (no == 1) {
                weak.classList.add("active");
            } else if (no == 2) {
                medium.classList.add("active");
            } else if (no == 3) {
                strong.classList.add("active");
            }
        }
    </script>
</body>

</html>