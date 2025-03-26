<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Fare Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Global Styles */
      /* Modal styles */
/* Modal styles */
.modal {
    display: flex; /* Use flexbox for centering */
    position: fixed; /* Fix the modal in place */
    top: 0; /* Align to the top */
    left: 0; /* Align to the left */
    width: 100%; /* Full width of the screen */
    height: 100%; /* Full height of the screen */
    background-color: rgba(0, 0, 0, 0.5); /* Darkened background */
    z-index: 1000; /* Ensure it's on top */
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
}

/* Modal content box */
.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    max-width: 600px; /* Limit the width */
    width: 100%; /* Allow it to fill up to max-width */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    overflow-y: auto;
}


        .modal h2 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        .modal ul {
            list-style-type: disc;
            margin-left: 20px;
            margin-bottom: 20px;
        }

        .modal ul li {
            margin-bottom: 10px;
        }

        .close-modal {
            background-color: #f1c40f;
            color: white;
            font-size: 16px;
            font-weight: bold;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: block;
            margin-top: 20px;
            margin-left: auto;
            margin-right: auto;
        }

        .close-modal:hover {
            background-color: #e67e22;
        }

        @media (max-width: 768px) {
            .modal-content {
                padding: 15px;
            }

            .modal h2 {
                font-size: 20px;
            }
        }

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

        .modal {
            display: flex;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            /* Centers horizontally */
            align-items: center;
            /* Centers vertically */
            padding: 20px;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            overflow-y: auto;
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

        /* Modal Buttons */
        .modal-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        /* Close Button Styling */
        .close-modal {
            background-color: #f1c40f;
            color: white;
            font-size: 16px;
            font-weight: bold;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s;
        }


        .close-modal:hover {
            background-color: #e67e22;
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
            <a href="auth/login.php" class="login-button">Log In</a>
            <button id="register-button">How to register?</button>
        </div>
    </header>

    <!-- Modal -->
    <div class="modal" id="register-modal">
        <div class="modal-content">
            <h2>How to Register</h2>
            <p>Follow these steps to create your account:</p>
            <ul>
                <li>Visit the registration page.</li>
                <li>Fill in your personal details (Full name, email, contact number, etc.).</li>
                <li>Click on the "Register" button to complete the process.</li>
            </ul>

            <div class="modal-buttons">
                <button class="close-modal" id="close-modal">Close</button>
                <a href="auth/userregister.php" class="register-link">Go to Registration</a>
            </div>
        </div>
    </div>



    <section class="hero">
        <div class="slider">
            <div class="slide active">
                <img src="assets/images/slider4.png" alt="Slide 1">
            </div>
            <div class="slide">
                <img src="assets/images/slider2.jpg" alt="Slide 2">
            </div>
            <div class="slide">
                <img src="assets/images/slider3.jpg" alt="Slide 3">
            </div>

            <div class="navigation">
                <button id="prev">&#10094;</button>
                <button id="next">&#10095;</button>
            </div>
        </div>
    </section>

    <section class="scrollable">
        <div class="scrollable-item">
            <img src="assets/images/discounts.jpg" alt="Feature 1">
            <h3>Affordable Fares</h3>
            <p>Enjoy discounts on your trips.</p>
        </div>
        <div class="scrollable-item">
            <img src="assets/images/bus1.jpg" alt="Feature 2">
            <h3>Comfortable Buses</h3>
            <p>Ride in style and comfort.</p>
        </div>
        <div class="scrollable-item">
            <img src="assets/images/route.jpg" alt="Feature 3">
            <h3>Convenient Routes</h3>
            <p>Track easily.</p>
        </div>
        <div class="scrollable-item">
            <img src="assets/images/cashback.jpg" alt="Feature 1">
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

document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("register-modal");
    const registerButton = document.getElementById("register-button");
    const closeModal = document.getElementById("close-modal");

    // Ensure modal is hidden on page load
    modal.style.display = "none";

    // Show the modal when the "Register" button is clicked
    registerButton.addEventListener("click", () => {
        modal.style.display = "block";
    });

    // Close the modal when the "Close" button inside the modal is clicked
    closeModal.addEventListener("click", () => {
        modal.style.display = "none";
    });
});

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