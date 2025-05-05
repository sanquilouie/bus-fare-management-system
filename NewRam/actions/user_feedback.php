<?php
// Start the session and include database connection
session_start();
include "../includes/connection.php";

// Check if the user is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['account_number'])) {
    header("Location: ../../index.php");
    exit();
}

// Get the POST data from the form
$trip_id = $_POST['trip_id'];
$rating = $_POST['rating'];
$feedback = $_POST['feedback'];

// Validate the input
if (empty($rating) || empty($feedback)) {
    echo "Rating and feedback are required!";
    exit();
}

// Validate rating (should be between 1 and 5)
if ($rating < 1 || $rating > 5) {
    echo "Invalid rating!";
    exit();
}

// Insert the rating and feedback into the database
$updateQuery = "
    UPDATE passenger_logs 
    SET rating = ?, feedback = ?
    WHERE id = ? AND rfid = ?
";

$stmt = $conn->prepare($updateQuery);
if ($stmt === false) {
    die("Error in preparing the query: " . $conn->error);
}

$account_number = $_SESSION['account_number']; // User's account number (RFID)
$stmt->bind_param('issi', $rating, $feedback, $trip_id, $account_number);

if ($stmt->execute()) {
    echo "Feedback submitted successfully!";
    header("Location: ../pages/user/recent_trips.php"); // Redirect back to the trips page
    exit();
} else {
    echo "Error submitting feedback: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
