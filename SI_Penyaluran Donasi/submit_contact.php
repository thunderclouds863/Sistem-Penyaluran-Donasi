<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php#contact");
    exit();
}

if (!csrf_verify()) {
    set_flash('error', 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.');
    header("Location: index.php#contact");
    exit();
}

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($full_name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
    set_flash('error', 'Mohon lengkapi nama, email yang valid, dan pesan Anda.');
    header("Location: index.php#contact");
    exit();
}

try {
    $conn = get_pdo();
    $stmt = $conn->prepare("INSERT INTO contact_messages (full_name, email, message) VALUES (:full_name, :email, :message)");
    $stmt->execute([
        ':full_name' => $full_name,
        ':email' => $email,
        ':message' => $message,
    ]);

    set_flash('success', 'Terima kasih! Pesan Anda telah kami terima dan diteruskan ke tim pengelola.');
    header("Location: index.php#contact");
    exit();
} catch (PDOException $e) {
    set_flash('error', 'Gagal mengirim pesan. Silakan coba beberapa saat lagi.');
    header("Location: index.php#contact");
    exit();
}
