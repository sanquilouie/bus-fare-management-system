<?php
include 'NewRam/includes/connection.php';
session_start();
// For Slide
$sliderFeatures = $conn->query("SELECT * FROM features WHERE is_active = 1 AND type = 'Slide'");

// For Card
$cardFeatures = $conn->query("SELECT * FROM features WHERE is_active = 1 AND type = 'Card'");


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Fare Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Arial', sans-serif;
    }

    a {
        text-decoration: none;
        color: inherit;
    }

    .container {
        width: 90%;
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
    }
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    /* Make body a flex container */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    header {
        width: 100%;
        background: linear-gradient(to right, rgb(243, 75, 83), rgb(131, 4, 4));
        color: white;
        padding: 20px 0;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    header .logo h1 {
        margin: 0;
        text-align: center;
        flex-grow: 1;
        font-size: 36px;
        font-weight: bold;
        letter-spacing: 1px;
    }

    header .login-button,
    #register-button {
        background: #f1c40f;
        color: white;
        font-size: 16px;
        font-weight: bold;
        padding: 12px 25px;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease-in-out;
        margin-left: 10px;
    }

    header .login-button:hover,
    #register-button:hover {
        background: #e67e22;
        transform: scale(1.1);
    }

    .slider {
        position: relative;
        height: 600px;
        margin: 20px auto;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    .slider img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .slider .slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 1s ease-in-out;
    }

    .slider .slide.active {
        opacity: 1;
    }

    .slider .navigation {
        position: absolute;
        width: 100%;
        display: flex;
        justify-content: space-between;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
    }

    .slider .navigation button {
        background: rgba(0, 0, 0, 0.6);
        color: white;
        border: none;
        font-size: 24px;
        padding: 10px 15px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s ease-in-out;
    }

    .slider .navigation button:hover {
        background: rgba(0, 0, 0, 0.8);
    }

    .scrollable {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
    padding: 1rem;
}

.scrollable-item {
    flex: 1 1 250px; /* grow/shrink with a base width */
    max-width: 300px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.2s ease-in-out;
}

.scrollable-item:hover {
    transform: translateY(-5px);
}

.scrollable-item img {
    width: 100%;
    height: auto;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 10px;
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
      padding: 10px 20px; 
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

    /* Register Link Styling */
    .register-link {
        background-color: #3498db;
        color: white;
        font-size: 16px;
        font-weight: bold;
        padding: 10px 20px;
        border: none;
        border-radius: 25px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s;
    }

    .register-link:hover {
        background-color: #2980b9;
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
            .slider {
                height: 300px;
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


.swal2-html-container .left-align {
  text-align: left;
  padding-left: 20px;  /* Optional: Adds a bit of padding to the left */
}
.swal2-html-container li {
  margin-bottom: 5px
}
    </style>
</head>

<body>
    <?php
        include 'NewRam/includes/loader.php';
    ?>
    <header>
        <div class="container d-flex justify-content-between align-items-center">
            <div class="logo">
                <h1>Zaragoza Ramstar Transport Cooperative</h1>
            </div>
            <div class="header-buttons d-flex">
                <a href="NewRam/auth/login.php" class="login-button">Log In</a>
                <button id="register-button" type="button" class="btn btn-primary">How to register?</button>
                <a href="NewRam/includes/bus_current_location.php" class="login-button">Bus Routes</a>
            </div>
        </div> 
    </header> 


    <!-- Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                    <div>
                        <h5 class="modal-title">How to Register</h5>
                        <p>Follow these steps to create your account:</p>
                    </div>
            </div>
            <div class="modal-body">
                <ul>
                        <li>Visit the registration page.</li>
                        <li>Fill in your personal details (Full name, email, contact number, etc.).</li>
                        <li>Click on the "Register" button to complete the process.</li>
                    </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="NewRam/auth/userregister.php" class="register-link">Go to Registration</a>
            </div>
            </div>
        </div>
    </div>


    <div class="main-content">
        <section class="hero">
            <div class="slider">
            <?php
                $isFirst = true;
                while ($row = $sliderFeatures->fetch_assoc()):
                ?>
                    <div class="slide <?= $isFirst ? 'active' : '' ?>">
                        <img src="NewRam/assets/images/<?= $row['image'] ?>" alt="<?= htmlspecialchars($row['title']) ?>" loading="lazy">
                    </div>
                <?php
                $isFirst = false;
                endwhile;
                ?>
                <div class="navigation">
                    <button id="prev">&#10094;</button>
                    <button id="next">&#10095;</button>
                </div>
            </div>
        </section>
        <section class="scrollable">
            <?php while ($row = $cardFeatures->fetch_assoc()): ?>
                <div class="scrollable-item">
                    <img src="NewRam/assets/images/<?= $row['image'] ?>" alt="<?= htmlspecialchars($row['title']) ?>" loading="lazy">
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p><?= htmlspecialchars($row['description']) ?></p>
                </div>
            <?php endwhile; ?>
        </section>
    </div>
    <footer class="footer-content">
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

    </footer>

    

</body>
<script>


        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const totalSlides = slides.length;
        const prevButton = document.getElementById('prev');
        const nextButton = document.getElementById('next');
        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (i === index) {
                    slide.classList.add('active');
                }
            });
        }
        prevButton.addEventListener('click', () => {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(currentSlide);
        });

        nextButton.addEventListener('click', () => {
            currentSlide = (currentSlide + 1) % totalSlides;
            showSlide(currentSlide);
        });

        setInterval(() => {
            currentSlide = (currentSlide + 1) % totalSlides;
            showSlide(currentSlide);
        }, 5000);

       
        document.getElementById('register-button').addEventListener('click', function() {
            Swal.fire({
                title: 'How to Register',
                html: `
                <p>Follow these steps to create your account:</p>
                <ul class="left-align">
                    <li>Visit the registration page.</li>
                    <li>Fill in your personal details (Full name, email, contact number, etc.).</li>
                    <li>Use a valid, active email address </li>
                    <li>Only register one account per person </li>
                    <li>Provide accurate personal information </li>
                    <li>Keep your email account secured</li>
                    <li>Check your spam/junk folder after registering</li>
                    <li>Click on the "Register" button to complete the process.</li>
                </ul>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Go to Registration',
                cancelButtonText: 'Close',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                window.location.href = 'NewRam/auth/userregister.php';  // Redirect to registration page
                }
            });
        });


    </script>
</html>