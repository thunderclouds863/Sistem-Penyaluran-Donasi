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

    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $full_name = htmlspecialchars($_POST['full_name']);
        $email = htmlspecialchars($_POST['email']);
        $message = htmlspecialchars($_POST['message']);

        // Prepare the SQL statement to insert the message into the database
        $stmt = $conn->prepare("INSERT INTO contact_messages (full_name, email, message) VALUES (:full_name, :email, :message)");
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':message', $message);
        $stmt->execute();

        // Setelah berhasil menyimpan data ke database
        if ($stmt->execute()) {
            // Tampilkan pop-up terima kasih
            echo "<script>
        alert('Terima kasih atas partisipasi Anda. Data Anda telah kami terima.');
        window.location.href = 'index.php'; // Redirect setelah alert
    </script>";
            exit();
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>