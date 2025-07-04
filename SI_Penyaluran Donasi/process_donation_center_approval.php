<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
  die("Unauthorized access. Please log in.");
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "donation_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Cek apakah pengguna sudah login
$loggedIn = isset($_SESSION['username']);
if ($loggedIn) {
  // Ambil ID user yang sedang login berdasarkan username
  $conn = mysqli_connect("localhost", "root", "", "donation_system");
  if ($conn) {
    $username = $_SESSION['username'];  // username from session
    $query = "SELECT id FROM signup WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $user = $result->fetch_assoc();
      $signup_id = $user['id'];  // Get the user's ID
    } else {
      // Handle error if no user found
      echo "User not found.";
      exit;
    }
  }
  // Process POST request
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize inputs
    $name = $_POST['donation_center_name'] ?? null;
    $address = $_POST['donation_center_address'] ?? null;
    $contact = $_POST['donation_center_contact'] ?? null;
    $status = "Default";

    if (!$name || !$address || !$contact) {
      die("Invalid input. Please provide all required details.");
    }

    // Insert the new donation center into the database
    $sql = "INSERT INTO donation_centers (signup_id, name, address, contact_info, status) VALUES (?, ?, ?, ?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $signup_id, $name, $address, $contact, $status);


    if ($stmt->execute()) {
      header("Location: jenis_donasi.html");
    } else {
      echo "Failed to update donation center status.";
    }

    $stmt->close();
  }
} else {
  echo "Anda harus login terlebih dahulu untuk memilih tempat donasi.";
}

$conn->close();
?>