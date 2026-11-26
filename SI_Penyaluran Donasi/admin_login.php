<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login_admin.html");
    exit();
}

try {
    $conn = get_pdo();
    $submitted_username = trim($_POST['username'] ?? '');
    $submitted_password = $_POST['password'] ?? '';

    if ($submitted_username === '' || $submitted_password === '') {
        header("Location: admin_login_failed.html");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $submitted_username]);
    $admin = $stmt->fetch();

    $valid = false;
    if ($admin) {
        if (hash_equals($admin['password'], hash('sha256', $submitted_password))) {
            $valid = true;
        } elseif (password_verify($submitted_password, $admin['password'])) {
            $valid = true;
        }
    }

    if ($valid) {
        session_regenerate_id(true);
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_id'] = (int) $admin['id'];

        $update = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE id = :id");
        $update->execute([':id' => $admin['id']]);

        header("Location: admin_dashboard.php");
        exit();
    }

    header("Location: admin_login_failed.html");
    exit();
} catch (PDOException $e) {
    http_response_code(500);
    echo "Terjadi kesalahan sistem saat memproses login admin.";
}
