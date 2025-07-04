<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_code = htmlspecialchars(trim($_POST['verification_code']));

    if (isset($_SESSION['verification_code'])) {
        $verification_code = $_SESSION['verification_code'];

        if ($entered_code == $verification_code) {
            header("Location: login.html");
            exit();
        } else {
            header("Location: verifyfailed.html");

        }
    } else {
        echo "<h1>Kesalahan!</h1>";
        echo "<p>Data verifikasi tidak ditemukan. Silakan registrasi ulang.</p>";
        echo '<p><a href="register.html">Kembali ke Form</a></p>';
    }
} else {
    header("Location: verify.php");
    exit();
}
