<?php
session_start();

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "donation_system";

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $submitted_username = $_POST['name'];
        $submitted_password = $_POST['ktm'];

        // Fetch the user details based on the username
        $stmt = $conn->prepare("SELECT * FROM signup WHERE username = :username");
        $stmt->bindParam(':username', $submitted_username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if ($user && password_verify($submitted_password, $user['password'])) {
            // Login successful
            $_SESSION['username'] = $submitted_username; // Store username in session
            header("Location: index.php");  // Redirect to index.html
            exit();
        } else {
            // Login failed, redirect with error parameter
            header("Location: loginfailed.html");
            exit();
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>
