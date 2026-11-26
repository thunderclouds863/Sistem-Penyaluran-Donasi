<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: signup.html");
    exit();
}

$user_input_username = trim($_POST['username'] ?? '');
$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$ktm_number = trim($_POST['ktm_number'] ?? '');
$user_input_password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? $user_input_password;
$user_input_email = trim($_POST['email'] ?? '');

if (
    $user_input_username === '' || $full_name === '' || $phone === '' ||
    $ktm_number === '' || strlen($user_input_password) < 6 ||
    $user_input_password !== $confirm_password || !filter_var($user_input_email, FILTER_VALIDATE_EMAIL)
) {
    header("Location: signupfailed.html");
    exit();
}

try {
    $conn = get_pdo();

    $stmt = $conn->prepare("SELECT id FROM signup WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $user_input_username]);
    if ($stmt->fetch()) {
        header("Location: signupfailed.html");
        exit();
    }

    $hashed_password = password_hash($user_input_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO signup (full_name, phone, username, password, ktm_number, email)
                            VALUES (:full_name, :phone, :username, :password, :ktm_number, :email)");
    $stmt->execute([
        ':full_name' => $full_name,
        ':phone' => $phone,
        ':username' => $user_input_username,
        ':password' => $hashed_password,
        ':ktm_number' => $ktm_number,
        ':email' => $user_input_email,
    ]);

    $verification_code = (string) random_int(100000, 999999);
    $_SESSION['verification_code'] = $verification_code;
    $_SESSION['user_email'] = $user_input_email;

    $subject = "Kode Verifikasi Pendaftaran - Donasi Panti Asuhan";
    $message = "Kode verifikasi Anda adalah: " . $verification_code;
    $headers = "From: no-reply@donasipantiasuhan.local";

    $mailSent = @mail($user_input_email, $subject, $message, $headers);
    if (!$mailSent) {
        // Fallback for local/demo environments without SMTP so verification flow remains completable
        $_SESSION['demo_verification_code'] = $verification_code;
    } else {
        unset($_SESSION['demo_verification_code']);
    }

    header("Location: verify.html");
    exit();
} catch (PDOException $e) {
    header("Location: signupfailed.html");
    exit();
}
