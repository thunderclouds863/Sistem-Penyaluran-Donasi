<?php
require_once __DIR__ . '/includes/bootstrap.php';

$statusMessage = null;
$errorMessage = null;
$demoResetUrl = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errorMessage = 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        if ($identifier === '') {
            $errorMessage = 'Masukkan username atau alamat email terdaftar Anda.';
        } else {
            try {
                $pdo = get_pdo();
                ensure_password_resets_table($pdo);

                $stmt = $pdo->prepare("SELECT id, email, username FROM signup WHERE email = :id OR username = :id2 LIMIT 1");
                $stmt->execute([':id' => $identifier, ':id2' => $identifier]);
                $user = $stmt->fetch();

                // Always show non-enumerating success message
                $statusMessage = 'Jika akun dengan data tersebut terdaftar, tautan reset password telah disiapkan dan berlaku selama 30 menit.';

                if ($user) {
                    $rawToken = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $rawToken);
                    $expiresAt = date('Y-m-d H:i:s', time() + 1800);

                    // Invalidate previous unused tokens for this user
                    $inv = $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE signup_id = :uid AND used_at IS NULL");
                    $inv->execute([':uid' => $user['id']]);

                    $ins = $pdo->prepare("INSERT INTO password_resets (signup_id, token_hash, expires_at) VALUES (:uid, :hash, :exp)");
                    $ins->execute([
                        ':uid' => $user['id'],
                        ':hash' => $tokenHash,
                        ':exp' => $expiresAt,
                    ]);

                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $resetLink = $scheme . '://' . $host . '/reset_password.php?token=' . urlencode($rawToken);

                    $mailSent = @mail(
                        $user['email'],
                        'Reset Password - Donasi Panti Asuhan',
                        "Gunakan tautan berikut untuk mengatur ulang password Anda (berlaku 30 menit):\n" . $resetLink,
                        'From: no-reply@donasipantiasuhan.local'
                    );

                    if (!$mailSent) {
                        $demoResetUrl = 'reset_password.php?token=' . urlencode($rawToken);
                    }
                }
            } catch (PDOException $e) {
                $errorMessage = 'Terjadi kesalahan sistem. Silakan coba beberapa saat lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password — Donasi Panti Asuhan</title>
  <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="assets/css/design-system.css">
</head>
<body>
  <div class="auth-shell">
    <aside class="auth-visual-panel">
      <div class="auth-brand-top">
        <a href="index.php">
          <span class="brand-mark" aria-hidden="true" style="background:rgba(255,255,255,0.12);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M12 21C12 21 3 15.2 3 8.8C3 5.6 5.45 3 8.5 3C10.37 3 12 4.02 12 5.55C12 4.02 13.63 3 15.5 3C18.55 3 21 5.6 21 8.8C21 15.2 12 21 12 21Z" fill="#D95D39"/>
              <path d="M8.5 11.8L11 14.3L16 9.2" stroke="#FAF8F5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          Donasi Panti Asuhan
        </a>
        <a href="login.html" class="auth-back-link">&larr; Login</a>
      </div>

      <div class="auth-art-center">
        <img src="assets/img/auth-donor.jpg" alt="Ilustrasi pemulihan akun donatur" class="auth-art-image">
        <div class="auth-art-caption">
          <h2>Pemulihan Akses Akun</h2>
          <p>Kami menggunakan token sekali pakai berbatas waktu untuk menjaga keamanan akun donatur Anda.</p>
        </div>
      </div>

      <div class="auth-visual-foot">
        <span>Sistem Penyaluran Donasi</span>
        <span>&copy; 2024 Donasi Panti Asuhan</span>
      </div>
    </aside>

    <main class="auth-form-panel">
      <div class="auth-mobile-header">
        <a class="brand-link" href="index.php">
          <span class="brand-mark" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M12 21C12 21 3 15.2 3 8.8C3 5.6 5.45 3 8.5 3C10.37 3 12 4.02 12 5.55C12 4.02 13.63 3 15.5 3C18.55 3 21 5.6 21 8.8C21 15.2 12 21 12 21Z" fill="#D95D39"/>
            </svg>
          </span>
          <span>Donasi Panti Asuhan</span>
        </a>
        <a href="login.html" class="btn btn-ghost btn-sm">&larr; Login</a>
      </div>

      <div class="auth-form-box">
        <h1 class="auth-heading">Lupa Password?</h1>
        <p class="auth-subheading">Masukkan username atau alamat email Anda untuk membuat tautan pengaturan ulang password.</p>

        <?php if ($errorMessage): ?>
          <div class="alert-banner is-error" role="alert"><?= e($errorMessage) ?></div>
        <?php endif; ?>

        <?php if ($statusMessage): ?>
          <div class="alert-banner is-success" role="status">
            <div>
              <?= e($statusMessage) ?>
              <?php if ($demoResetUrl): ?>
                <div style="margin-top:10px;">
                  <a href="<?= e($demoResetUrl) ?>" class="btn btn-navy btn-sm" id="demoResetLink">Buka Tautan Reset Password (Mode Demo)</a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <form method="POST" action="forgot_password.php">
          <?= csrf_field() ?>
          <div class="form-field">
            <label class="form-label" for="identifier">Username atau Email</label>
            <input type="text" id="identifier" name="identifier" class="form-control-app" placeholder="Masukkan username atau email terdaftar" required>
          </div>
          <button type="submit" class="btn btn-primary btn-lg btn-block">Kirim Tautan Reset</button>
        </form>

        <p class="auth-footer-note">
          Ingat password Anda? <a href="login.html">Kembali ke Login</a>
        </p>
      </div>
    </main>
  </div>
  <script src="assets/js/app.js"></script>
</body>
</html>
