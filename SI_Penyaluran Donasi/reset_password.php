<?php
require_once __DIR__ . '/includes/bootstrap.php';

$token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));
$tokenValid = false;
$errorMessage = null;
$successMessage = null;
$resetRecord = null;

try {
    $pdo = get_pdo();
    ensure_password_resets_table($pdo);

    if ($token !== '' && ctype_xdigit($token) && strlen($token) === 64) {
        $tokenHash = hash('sha256', $token);
        $stmt = $pdo->prepare("
            SELECT pr.id, pr.signup_id, pr.expires_at, pr.used_at, su.username
            FROM password_resets pr
            INNER JOIN signup su ON su.id = pr.signup_id
            WHERE pr.token_hash = :hash
            LIMIT 1
        ");
        $stmt->execute([':hash' => $tokenHash]);
        $resetRecord = $stmt->fetch();

        if ($resetRecord && $resetRecord['used_at'] === null && strtotime($resetRecord['expires_at']) > time()) {
            $tokenValid = true;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify()) {
            $errorMessage = 'Sesi formulir tidak valid. Silakan coba lagi.';
        } elseif (!$tokenValid || !$resetRecord) {
            $errorMessage = 'Tautan reset password tidak valid atau sudah kedaluwarsa.';
        } else {
            $newPass = $_POST['password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';

            if (strlen($newPass) < 6) {
                $errorMessage = 'Password baru minimal 6 karakter.';
            } elseif ($newPass !== $confirmPass) {
                $errorMessage = 'Konfirmasi password tidak cocok.';
            } else {
                $pdo->beginTransaction();
                $hashed = password_hash($newPass, PASSWORD_DEFAULT);
                $updUser = $pdo->prepare("UPDATE signup SET password = :pwd WHERE id = :uid");
                $updUser->execute([':pwd' => $hashed, ':uid' => $resetRecord['signup_id']]);

                $updToken = $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = :rid");
                $updToken->execute([':rid' => $resetRecord['id']]);
                $pdo->commit();

                $tokenValid = false;
                $successMessage = 'Password berhasil diperbarui! Silakan masuk menggunakan password baru Anda.';
            }
        }
    }
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $errorMessage = 'Terjadi kesalahan sistem saat memperbarui password.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Atur Ulang Password — Donasi Panti Asuhan</title>
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
        <img src="assets/img/auth-donor.jpg" alt="Ilustrasi atur ulang password akun donatur" class="auth-art-image">
        <div class="auth-art-caption">
          <h2>Buat Password Baru</h2>
          <p>Gunakan password yang kuat dan unik minimal 6 karakter untuk melindungi akun Anda.</p>
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
        <h1 class="auth-heading">Atur Ulang Password</h1>

        <?php if ($successMessage): ?>
          <div class="alert-banner is-success" role="status"><?= e($successMessage) ?></div>
          <a href="login.html" class="btn btn-primary btn-lg btn-block">Masuk Sekarang</a>
        <?php elseif (!$tokenValid): ?>
          <div class="alert-banner is-error" role="alert">
            Tautan reset password tidak valid, sudah digunakan, atau telah kedaluwarsa.
          </div>
          <a href="forgot_password.php" class="btn btn-navy btn-lg btn-block">Minta Tautan Reset Baru</a>
        <?php else: ?>
          <p class="auth-subheading">Masukkan password baru untuk akun <strong><?= e($resetRecord['username']) ?></strong>.</p>
          <?php if ($errorMessage): ?>
            <div class="alert-banner is-error" role="alert"><?= e($errorMessage) ?></div>
          <?php endif; ?>

          <form method="POST" action="reset_password.php">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">

            <div class="form-field">
              <label class="form-label" for="password">Password Baru</label>
              <div class="input-password-wrap">
                <input type="password" id="password" name="password" class="form-control-app" minlength="6" placeholder="Minimal 6 karakter" required>
                <button type="button" class="password-toggle-btn" data-toggle-password="password" aria-label="Tampilkan password">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
            </div>

            <div class="form-field">
              <label class="form-label" for="confirm_password">Konfirmasi Password Baru</label>
              <div class="input-password-wrap">
                <input type="password" id="confirm_password" name="confirm_password" class="form-control-app" minlength="6" placeholder="Ulangi password baru" required>
                <button type="button" class="password-toggle-btn" data-toggle-password="confirm_password" aria-label="Tampilkan konfirmasi password">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block">Simpan Password Baru</button>
          </form>
        <?php endif; ?>
      </div>
    </main>
  </div>
  <script src="assets/js/app.js"></script>
</body>
</html>
