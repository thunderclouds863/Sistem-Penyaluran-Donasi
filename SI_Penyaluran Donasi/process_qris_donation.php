<?php
session_start();

// Cek apakah pengguna sudah login
$loggedIn = isset($_SESSION['username']);

if ($loggedIn) {
    // Ambil ID user yang sedang login berdasarkan username
    $conn = mysqli_connect("localhost", "root", "", "donation_system");
    if ($conn) {
        $username = $_SESSION['username']; // username from session
        $query = "SELECT id FROM signup WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $signup_id = $user['id']; // Get the user's ID
        } else {
            // Handle error if no user found
            header("Location: login.php");
            exit;
        }
    }

    // Ambil `donation_center_id` dari tabel `donation_centers`
    $donation_center_query = "SELECT id FROM donation_centers WHERE signup_id = ?";
    $center_stmt = $conn->prepare($donation_center_query);
    $center_stmt->bind_param("i", $signup_id);
    $center_stmt->execute();
    $center_result = $center_stmt->get_result();

    if ($center_result->num_rows > 0) {
        $center = $center_result->fetch_assoc();
        $donation_center_id = $center['id'];
    } else {
        echo "No donation center found for this user.";
        exit;
    }

    // Get the donation amount and contact info from the form
    $donation_amount = htmlspecialchars(trim($_POST['donation_amount']));
    $contact_info = htmlspecialchars(trim($_POST['contact_info']));

    // Prepare the SQL statement to insert the donation data
    $stmt = $conn->prepare(
        "INSERT INTO financial_donations (amount, contact_info, status, signup_id, donation_center_id)
         VALUES (?, ?, 'Pending', ?, ?)"
    );
    // "d" for float (donation_amount), "s" for string (contact_info), "i" for integer (signup_id, donation_center_id)
    $stmt->bind_param("dsii", $donation_amount, $contact_info, $signup_id, $donation_center_id);

    // Execute the query and check if successful
    if ($stmt->execute()) {
        // After inserting the data, redirect to payment success page
        header("Location: payment-success.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
