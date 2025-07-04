<?php
session_start();

// Redirect if the user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Change to your login page
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi Barang</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #0072ff, #00c6ff);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 480px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
            padding: 40px;
            transition: transform 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
        }

        h2 {
            text-align: center;
            color: #333;
            font-size: 28px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: 500;
            color: #555;
        }

        input[type="text"] {
            width: 93%; /* Ensures all form elements are the same width */
            padding: 12px 15px;
            margin-bottom: 18px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background-color: #fafafa;
            transition: all 0.3s ease;
        }

        select {
            width: 100%; /* Ensures all form elements are the same width */
            padding: 12px 15px;
            margin-bottom: 18px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background-color: #fafafa;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus, select:focus {
            border-color: #0072ff;
            background-color: #e7f0fe;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 114, 255, 0.3);
        }

        button {
            background-color: #0072ff;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            width: 100%;
            font-weight: 500;
            transition: background-color 0.3s, transform 0.2s ease;
        }

        button:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .thank-you-message {
            display: none;
            margin-top: 20px;
            text-align: center;
            color: #28a745;
            font-size: 18px;
            font-weight: 500;
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

    </style>
</head>
<body>

    <div class="container">
        <h2>Donasi Barang</h2>
        <!-- Display the success message only after form submission -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 'true'): ?>
            <p class="thank-you-message">Thank you for your donation! Your item has been submitted.</p>
        <?php endif; ?>
        <form action="process_item_donation.php" method="POST">
            <label for="location">Pilih Lokasi Pengumpulan:</label>
            <select id="location" name="location" required>
                <option value="">--Pilih Lokasi--</option>
                <option value="Jakarta">Dekanat Undip</option>
            </select>

            <label for="item_type">Jenis Barang:</label>
            <input type="text" id="item_type" name="item_type" required placeholder="Masukkan jenis barang">

            <label for="contact">Kontak Info:</label>
            <input type="text" id="contact" name="contact_info" required placeholder="Masukkan info kontak">

            <button type="submit">Kirim Donasi</button>
        </form>
    </div>

</body>
</html>
