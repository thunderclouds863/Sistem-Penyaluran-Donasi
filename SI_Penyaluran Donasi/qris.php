<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Donasi Finansial (QRIS) — Donasi Panti Asuhan</title>
  <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="assets/css/design-system.css">
</head>
<body>
  <header class="site-header">
    <div class="container-app nav-inner">
      <a class="brand-link" href="index.php">
        <span class="brand-mark" aria-hidden="true">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <path d="M12 21C12 21 3 15.2 3 8.8C3 5.6 5.45 3 8.5 3C10.37 3 12 4.02 12 5.55C12 4.02 13.63 3 15.5 3C18.55 3 21 5.6 21 8.8C21 15.2 12 21 12 21Z" fill="#D95D39"/>
            <path d="M8.5 11.8L11 14.3L16 9.2" stroke="#FAF8F5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span>Donasi Panti Asuhan</span>
      </a>
      <div class="nav-actions">
        <a href="jenis_donasi.html" class="btn btn-secondary btn-sm">&larr; Kembali</a>
      </div>
    </div>
  </header>

  <section class="page-banner">
    <div class="container-app">
      <h1>Donasi Finansial via QRIS</h1>
      <p>Isi nominal donasi dan informasi kontak Anda, lalu pindai kode QRIS untuk menyelesaikan pembayaran.</p>
      <div class="stepper" aria-label="Langkah donasi">
        <span class="step-pill is-done">&#10003; 1. Pilih Tempat Donasi</span>
        <span class="step-pill is-done">&#10003; 2. Pilih Jenis Donasi</span>
        <span class="step-pill is-active">3. Selesaikan Donasi</span>
      </div>
    </div>
  </section>

  <main class="container-app">
    <div class="checkout-grid">
      <div class="surface-card">
        <h2 style="font-size:1.35rem; margin-bottom:6px;">Rincian Donasi Dana</h2>
        <p class="form-hint" style="margin-bottom:22px;">Pilih nominal cepat atau masukkan jumlah sesuai keinginan Anda.</p>

        <form id="donationForm" action="process_qris_donation.php" method="POST">
          <div class="form-field">
            <label class="form-label">Pilih Nominal Cepat</label>
            <div class="amount-chips">
              <button type="button" class="amount-chip tabular-nums" data-amount-chip="25000">Rp 25.000</button>
              <button type="button" class="amount-chip tabular-nums" data-amount-chip="50000">Rp 50.000</button>
              <button type="button" class="amount-chip tabular-nums" data-amount-chip="100000">Rp 100.000</button>
              <button type="button" class="amount-chip tabular-nums" data-amount-chip="250000">Rp 250.000</button>
            </div>
          </div>

          <div class="form-field">
            <label class="form-label" for="donation_amount">Nominal Donasi (Rp)</label>
            <input type="number" class="form-control-app tabular-nums" id="donation_amount" name="donation_amount" min="1000" placeholder="Contoh: 100000" required>
          </div>

          <div class="form-field">
            <label class="form-label" for="contact_info">Kontak Info (Email / WhatsApp)</label>
            <input type="text" class="form-control-app" id="contact_info" name="contact_info" placeholder="Untuk notifikasi konfirmasi donasi" required>
          </div>

          <input type="hidden" name="signup_id" value="<?= htmlspecialchars($_SESSION['username']) ?>">

          <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:8px;">
            Konfirmasi Pembayaran QRIS
          </button>
        </form>
      </div>

      <aside class="qris-panel">
        <span class="pathway-tag" style="background:rgba(255,255,255,0.14); color:#fff; margin-bottom:12px; display:inline-block;">QRIS Nasional</span>
        <h3>Scan QR Code Pembayaran</h3>
        <p>Pindai menggunakan aplikasi e-wallet atau mobile banking Anda, lalu klik kode QR atau tombol konfirmasi.</p>

        <div class="qris-frame-box" id="qrCode" role="button" tabindex="0" title="Klik setelah melakukan scan QRIS untuk konfirmasi">
          <img src="frame.png" alt="QRIS QR Code Pembayaran Donasi">
        </div>

        <p style="margin:0; font-size:0.82rem; color:rgba(255,255,255,0.68);">
          Setelah pembayaran dikonfirmasi, Anda akan diarahkan ke halaman bukti penerimaan donasi.
        </p>
      </aside>
    </div>
  </main>

  <script src="assets/js/app.js"></script>
  <script>
    const qrCodeBox = document.getElementById('qrCode');
    const donationForm = document.getElementById('donationForm');
    qrCodeBox.addEventListener('click', function () {
      if (donationForm.reportValidity()) {
        donationForm.submit();
      }
    });
  </script>
</body>
</html>
