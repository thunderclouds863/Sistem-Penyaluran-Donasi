<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $entered_code = trim($_POST['verification_code'] ?? '');

    if (isset($_SESSION['verification_code'])) {
        $verification_code = (string) $_SESSION['verification_code'];

        if (hash_equals($verification_code, $entered_code)) {
            unset($_SESSION['verification_code'], $_SESSION['demo_verification_code']);
            set_flash('success', 'Akun Anda berhasil diverifikasi! Silakan masuk menggunakan username dan password Anda.');
            header("Location: login.html");
            exit();
        } else {
            header("Location: verifyfailed.html");
            exit();
        }
    } else {
        header("Location: signup.html");
        exit();
    }
} else {
    header("Location: verify.html");
    exit();
}
