<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions</title>
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
    <h1>Terms and Conditions</h1>

    <p>Welcome to<b>Ramstar Zaragoza</b>! These terms and conditions outline the rules and regulations for the use of Ramstar Zaragoza. By accessing or using the System, you accept and agree to be bound by these terms. If you do not agree, you may not use the System.</p>

    <h2>1. Definitions</h2>
    <p><strong>System:</strong> Refers to the web-based bus fare management platform provided by <b>Ramstar Zaragoza</b>.</p>
    <p><strong>User:</strong> Refers to anyone using the System, including conductors, passengers, and administrators.</p>

    <h2>2. Use of the System</h2>
    <ul>
        <li>Users must provide accurate and up-to-date information while registering or making transactions.</li>
        <li>The System is intended for lawful purposes only. Any misuse or unauthorized access will result in termination of access and may lead to legal action.</li>
        <li>Conductors and administrators are responsible for ensuring fare calculations and transactions are conducted accurately.</li>
    </ul>

    <h2>3. Payments and Refunds</h2>
    <ul>
        <li>All payments made via the System are non-refundable unless otherwise specified.</li>
        <li>Refunds for incorrect transactions are subject to verification and approval.</li>
    </ul>

    <h2>4. Liability</h2>
    <ul>
        <li><b>Ramstar Zaragoza</b> is not liable for any inaccuracies in fare calculations caused by user error or misuse of the System.</li>
        <li>We do not guarantee uninterrupted access to the System and will not be held responsible for any downtime or technical issues.</li>
    </ul>

    <h2>5. Data Security</h2>
    <p>The System uses reasonable measures to protect user data. However, <b>Ramstar Zaragoza</b> is not responsible for unauthorized access due to user negligence.</p>

    <h2>6. Amendments</h2>
    <p><b>Ramstar Zaragoza</b> reserves the right to modify these terms at any time. Continued use of the System after changes indicates acceptance of the revised terms.</p>

    <h2>7. Contact Us</h2>
    <p>If you have any questions about these terms, please contact us at <b><i>portfolio@example.invalid</b></i></p>
</div>

<button onclick="goBack()">Go Back</button>

<script>
function goBack() {
    if (document.referrer !== "") {
        window.history.back();
    } else {
        window.location.href = 'admindashboard.php';
    }
}
</script>

</body>
</html>
