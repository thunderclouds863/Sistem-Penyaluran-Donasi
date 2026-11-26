<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit();
}

try {
    $conn = get_pdo();
    $submitted_username = trim($_POST['name'] ?? '');
    $submitted_password = $_POST['ktm'] ?? '';

    if ($submitted_username === '' || $submitted_password === '') {
        header("Location: loginfailed.html");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, username, password FROM signup WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $submitted_username]);
    $user = $stmt->fetch();

    if ($user && password_verify($submitted_password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id'] = (int) $user['id'];
        header("Location: index.php");
        exit();
    }

    header("Location: loginfailed.html");
    exit();
} catch (PDOException $e) {
    http_response_code(500);
    echo "Terjadi kesalahan sistem saat memproses login.";
}
