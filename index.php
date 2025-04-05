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

    header {
        background: linear-gradient(to right, rgb(243, 75, 83), rgb(131, 4, 4));
        color: white;
        padding: 20px 0;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    header .logo h1 {
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
        overflow-x: auto;
        padding: 20px 10px;
        background-color: #f5f7fa;
        gap: 20px;
        margin-top: 30px;
        flex-wrap: nowrap;
        justify-content: center;
    }

    .scrollable-item {
        min-width: 280px;
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        flex-shrink: 0;
        text-align: center;
        
        transition: transform 0.3s ease-in-out;
    }

    .scrollable-item:hover {
        transform: translateY(-10px);
    }

    .scrollable-item img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    footer {
        background: linear-gradient(to right, rgb(243, 75, 83), rgb(131, 4, 4));
        color: white;
        padding: 30px 0;
        text-align: center;
    }

    footer .footer-content {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 30px;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    footer h3 {
        margin-top: 15px;
        font-size: 18px;
        font-weight: bold;
    }

    footer .social-icons {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
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
    }

    @media (max-width: 768px) {
        .scrollable {
            flex-direction: column;
            align-items: center;
        }
    }

    @media (max-width: 480px) {
        .slider {
            height: 300px;
        }
    }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1>Ramstar Bus Transportation Cooperative</h1>
            </div>
            <a href="NewRam/auth/login.php" class="login-button">Log In</a>
            <button id="register-button" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal">How to register?</button>
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


    <section class="hero">
        <div class="slider">
            <div class="slide active">
                <img src="NewRam/assets/images/slider4.png" alt="Slide 1" loading="lazy">
            </div>
            <div class="slide">
                <img src="NewRam/assets/images/slider2.jpg" alt="Slide 2" loading="lazy">
            </div>
            <div class="slide">
                <img src="NewRam/assets/images/slider3.jpg" alt="Slide 3" loading="lazy">
            </div>

            <div class="navigation">
                <button id="prev">&#10094;</button>
                <button id="next">&#10095;</button>
            </div>
        </div>
    </section>

    <section class="scrollable">
        <div class="scrollable-item">
            <img src="NewRam/assets/images/discounts.jpg" alt="Feature 1" loading="lazy">
            <h3>Affordable Fares</h3>
            <p>Enjoy discounts on your trips.</p>
        </div>
        <div class="scrollable-item">
            <img src="NewRam/assets/images/bus1.jpg" alt="Feature 2" loading="lazy">
            <h3>Comfortable Buses</h3>
            <p>Ride in style and comfort.</p>
        </div>
        <div class="scrollable-item">
            <img src="NewRam/assets/images/route.jpg" alt="Feature 3" loading="lazy">
            <h3>Convenient Routes</h3>
            <p>Track easily.</p>
        </div>
        <div class="scrollable-item">
            <img src="NewRam/assets/images/cashback.jpg" alt="Feature 1" loading="lazy">
            <h3>Exclusive Cashback</h3>
            <p>Enjoy Cashback every load.</p>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <div>
                <p>&copy; 2024 Ramstar Bus Transportation Cooperative | All rights reserved</p>
            </div>
            <div>
                <h3>Contact Us:</h3>
                <p>Phone No.: <i>(0967) 235 2590</i></p>
                <p>Email: <i>portfolio@example.invalid</i></p>
                <p>Address: <i>Purok 5, #235, San Rafael, Zaragoza, Nueva Ecija 3110</i></p>
            </div>
            <div>
                <h3>Follow Us:</h3>
                <div class="social-icons">
                    <a href="https://www.facebook.com/people/Zaragoza-Ramstar-Transport-Cooperative/61550838758867/"
                        target="_blank" class="social-icon facebook">
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
        </div>
    </footer>
    <script>
    </script>
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

       

    </script>

</body>

</html>