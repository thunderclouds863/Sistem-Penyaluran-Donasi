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
        $submitted_username = $_POST['username'];  // Admin username from form
        $submitted_password = $_POST['password'];  // Admin password from form

        // Prepare and execute the query to fetch the admin based on the submitted username
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = :username");
        $stmt->bindParam(':username', $submitted_username);
        $stmt->execute();

        // Fetch the admin details
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify if the password matches the hashed password in the database
        if ($admin && hash('sha256', $submitted_password) === $admin['password']) {
            // Admin login successful
            $_SESSION['admin_username'] = $submitted_username;  // Store admin username in session

            // Update the last login timestamp
            $stmt = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE id = :id");
            $stmt->bindParam(':id', $admin['id']);
            $stmt->execute();

            header("Location: admin_dashboard.php");  // Redirect to admin dashboard
            exit();
        } else {
            // Admin login failed, redirect to login page with error
            header("Location: admin_login_failed.html");
            exit();
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>
