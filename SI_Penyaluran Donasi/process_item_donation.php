<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "donation_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve the logged-in user's ID based on the username
$username = $_SESSION['username']; // Username from session
$query = "SELECT id FROM signup WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_id = $user['id']; // Get the user's ID
} else {
    // Handle error if no user found
    header("Location: login.php");
    exit;
}

// Retrieve the form data
$location = htmlspecialchars(trim($_POST['location']));
$item_type = htmlspecialchars(trim($_POST['item_type']));
$contact_info = htmlspecialchars(trim($_POST['contact_info']));

$donation_center_id = 1; // Replace 1 with the actual ID of the donation center

// Prepare and execute the donation insertion query
$stmt = $conn->prepare("INSERT INTO item_donations (location, item_type, contact_info, signup_id, donation_center_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssii", $location, $item_type, $contact_info, $user_id, $donation_center_id);
$stmt->execute();


// Close the statement and connection
$stmt->close();
$conn->close();

// Redirect to the confirmation page
header("Location: barang_confirmation.html");
exit();
?>
