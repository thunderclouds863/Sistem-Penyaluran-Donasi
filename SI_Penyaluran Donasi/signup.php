<?php
session_start(); // Start a session to store the verification code

// Database connection settings
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "donation_system";

// Check form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get input values from the form
    $user_input_username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $ktm_number = $_POST['ktm_number'];  // Add KTM number field
    $user_input_password = $_POST['password'];
    $user_input_email = $_POST['email'];

    try {
        // Database connection
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $db_username, $db_password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if username already exists
        $stmt = $conn->prepare("SELECT * FROM signup WHERE username = :username");
        $stmt->bindParam(':username', $user_input_username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // If username exists, redirect to error page
            header("Location: signupfailed.html");
            exit();
        }

        // Hash the password
        $hashed_password = password_hash($user_input_password, PASSWORD_DEFAULT);

        // Insert new user data into the database
        $stmt = $conn->prepare("INSERT INTO signup (full_name, phone, username, password, ktm_number, email)
                                VALUES (:full_name, :phone, :username, :password, :ktm_number, :email)");
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':username', $user_input_username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':ktm_number', $ktm_number);  // Bind KTM number
        $stmt->bindParam(':email', $user_input_email);
        $stmt->execute();

        // Generate a verification code
        $verification_code = rand(100000, 999999);
        $_SESSION['verification_code'] = $verification_code;
        $_SESSION['user_email'] = $user_input_email;

        // Send verification email
        $subject = "Verification Code for Your Registration";
        $message = "Your verification code is: " . $verification_code;
        $headers = "From: no-reply@yourdomain.com";

        if (mail($user_input_email, $subject, $message, $headers)) {
            // Redirect to verification page after email is sent
            header("Location: verify.html");
            exit();
        } else {
            echo "<h1>Error Sending Email!</h1>";
            echo '<p><a href="register.html">Go back to the form</a></p>';
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    // Close connection
    $conn = null;
}
?>
