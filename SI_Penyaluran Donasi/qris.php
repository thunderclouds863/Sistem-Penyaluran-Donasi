<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QRIS Donasi</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2, h3 {
            color: #28a745; /* Primary green color */
        }
        .form-group label {
            font-weight: bold;
        }
        .qr-code-container {
            text-align: center;
            margin-top: 30px;
        }
        .qr-code-container img {
            max-width: 300px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Form Donasi QRIS</h2>
        <form id="donationForm" action="process_qris_donation.php" method="POST">
            <div class="form-group">
                <label for="donation_amount">Nominal Donasi:</label>
                <input type="number" class="form-control" id="donation_amount" name="donation_amount" required>
            </div>
            <div class="form-group">
                <label for="contact_info">Kontak Info:</label>
                <input type="text" class="form-control" id="contact_info" name="contact_info" required>
            </div>
            <!-- Hidden input for signup_id -->
            <input type="hidden" name="signup_id" value="<?php echo $_SESSION['username']; ?>"> <!-- Assuming user_id is stored in session -->
        </form>
    </div>

    <div class="container qr-code-container">
        <h3>Scan QR Code untuk Pembayaran</h3>
        <img src="frame.png" alt="QRIS QR Code" class="img-fluid" id="qrCode"> <!-- Replace with your QR code image -->
        <p>Setelah Anda melakukan scan, Anda akan diarahkan ke halaman konfirmasi pembayaran.</p>
    </div>

    <script>
        // Simulate the QR code click event and submit the form
        const qrCodeImg = document.querySelector('#qrCode');
        qrCodeImg.addEventListener('click', function() {
            document.getElementById('donationForm').submit(); // Submit the form when QR is clicked
        });
    </script>
</body>
</html>
