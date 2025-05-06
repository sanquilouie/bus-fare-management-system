<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy</title>
    <style>
        .content {
            padding: 20px;
        
            margin: auto;
            font-family: Arial, sans-serif;
        }
        button {
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
        button:hover {
            background-color: #e67e22;
         transform: scale(1.05);
         transition: transform 0.3s ease;
        }
    </style>
</head>
<body>

<div class="content">
    <h1>Privacy Policy</h1>

    <p><b>Ramstar Zaragoza</b> ("we," "us," or "our") respects your privacy and is committed to protecting your personal information. This Privacy Policy explains how we collect, use, and safeguard your data.</p>

    <h2>1. Information We Collect</h2>
    <ul>
        <li><strong>Personal Information:</strong> Name, email, phone number, and account details during registration.</li>
        <li><strong>Transaction Data:</strong> Fare routes, payment methods, and transaction history.</li>
        <li><strong>Technical Data:</strong> IP address, device information, and browsing activity.</li>
    </ul>

    <h2>2. How We Use Your Information</h2>
    <ul>
        <li>To process transactions and manage accounts.</li>
        <li>To improve the functionality and performance of the System.</li>
        <li>To communicate updates, promotions, or system notifications.</li>
    </ul>

    <h2>3. Sharing Your Information</h2>
    <p>We do not sell or rent your data to third parties. Data may be shared with authorized partners or service providers to facilitate operations (e.g., payment processors).</p>

    <h2>4. Data Security</h2>
    <p>We implement encryption and secure servers to protect your information. Users are responsible for safeguarding their login credentials.</p>

    <h2>5. Your Rights</h2>
    <ul>
        <li>You have the right to access, update, or delete your personal data by contacting us at <b><i>portfolio@example.invalid</b></i></li>
        <li>You can opt-out of promotional communications at any time.</li>
    </ul>

    <h2>6. Cookies</h2>
    <p>The System uses cookies to enhance your user experience. You can disable cookies in your browser settings, but this may affect functionality.</p>

    <h2>7. Updates to Privacy Policy</h2>
    <p>We reserve the right to update this policy at any time. Changes will be posted on this page with an updated effective date.</p>

    <h2>8. Contact Us</h2>
    <p>If you have questions about this Privacy Policy, please email us at <b><i>portfolio@example.invalid</b></i></p>
</div>
<button onclick="goBack()">Go Back</button>

<script>
function goBack() {
    if (document.referrer !== "") {
        window.history.back();
    } else {
        window.location.href = typeof pageFallbackUrl !== 'undefined' ? pageFallbackUrl : 'admindashboard.php';
    }
}
</script>
</body>
</html>
