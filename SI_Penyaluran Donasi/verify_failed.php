<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (empty($_SESSION['user_email'])) {
    header("Location: signup.html");
    exit();
}

$verification_code = (string) random_int(100000, 999999);
$_SESSION['verification_code'] = $verification_code;

$subject = "Kode Verifikasi Baru - Donasi Panti Asuhan";
$message = "Kode verifikasi baru Anda adalah: " . $verification_code;
$headers = "From: no-reply@donasipantiasuhan.local";

$mailSent = @mail($_SESSION['user_email'], $subject, $message, $headers);
if (!$mailSent) {
    $_SESSION['demo_verification_code'] = $verification_code;
} else {
    unset($_SESSION['demo_verification_code']);
}

header("Location: verify.html");
exit();
