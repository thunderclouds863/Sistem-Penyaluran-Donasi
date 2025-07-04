<?php
// Start the session
session_start();

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
            header("Location: login.html");
            exit;
        }
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize user input
        $assistance_type = htmlspecialchars($_POST['assistanceType']);
        $description = htmlspecialchars($_POST['description']);

        // Validasi jenis bantuan yang diterima
        $valid_assistance_types = ['Bantuan Barang dan Pangan', 'Bantuan Dana']; // Jenis bantuan yang valid

        // Periksa apakah jenis bantuan yang dipilih valid
        if (!in_array($assistance_type, $valid_assistance_types)) {
            echo "Jenis bantuan tidak valid.";
            exit;
        }

        // Prepare SQL query to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO donation_requests (assistance_type, description, signup_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $assistance_type, $description, $signup_id); // Corrected bind_param types

        // Execute the query
        if ($stmt->execute()) {
            header("Location: pengajuan_berhasil.html");
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

    // Close the connection
    $conn->close();
}
?>
