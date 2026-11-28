<?php
/**
 * Shared application bootstrap: secure session, DB connections, CSRF, and helpers.
 */

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function app_db_config(): array {
    return [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME') ?: 'donation_system',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
    ];
}

function get_pdo(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $cfg = app_db_config();
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $cfg['host'], $cfg['name']);
    $pdo = demo_pdo($dsn, $cfg['user'], $cfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function get_mysqli(): mysqli {
    $cfg = app_db_config();
    $conn = demo_mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['name']);
    if ($conn->connect_error) {
        http_response_code(500);
        die('Database connection failed.');
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

function e(?string $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(?string $token = null): bool {
    $submitted = $token ?? ($_POST['csrf_token'] ?? '');
    if (empty($_SESSION['csrf_token']) || !is_string($submitted) || $submitted === '') {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $submitted);
}

function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function get_current_user_id(PDO $pdo): ?int {
    if (empty($_SESSION['username'])) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT id FROM signup WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $_SESSION['username']]);
    $row = $stmt->fetch();
    return $row ? (int) $row['id'] : null;
}

function ensure_password_resets_table(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            signup_id INT NOT NULL,
            token_hash CHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token_hash (token_hash),
            FOREIGN KEY (signup_id) REFERENCES signup(id) ON DELETE CASCADE
        )
    ");
}
