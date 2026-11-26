<?php
session_start();

// Redirect if the user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Donasi Barang — Donasi Panti Asuhan</title>
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
      <h1>Formulir Donasi Barang</h1>
      <p>Daftarkan bantuan barang yang akan Anda serahkan melalui titik pengumpulan resmi agar penyalurannya tercatat rapi.</p>
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
        <h2 style="font-size:1.35rem; margin-bottom:6px;">Detail Barang Donasi</h2>
        <p class="form-hint" style="margin-bottom:22px;">Pastikan barang dalam kondisi bersih dan layak pakai sebelum diserahkan ke posko.</p>

        <?php if (isset($_GET['success']) && $_GET['success'] == 'true'): ?>
          <div class="alert-banner is-success" role="status">
            Terima kasih atas donasi Anda! Data donasi barang telah berhasil dicatat.
          </div>
        <?php endif; ?>

        <form action="process_item_donation.php" method="POST">
          <div class="form-field">
            <label class="form-label" for="location">Lokasi Pengumpulan (Drop-Off)</label>
            <select id="location" name="location" class="form-control-app" required>
              <option value="">-- Pilih Lokasi Pengumpulan --</option>
              <option value="Jakarta">Dekanat Undip</option>
            </select>
          </div>

          <div class="form-field">
            <label class="form-label" for="item_type">Jenis &amp; Jumlah Barang</label>
            <input type="text" id="item_type" name="item_type" class="form-control-app" placeholder="Contoh: Beras 10 kg, Pakaian layak pakai 2 dus" required>
          </div>

          <div class="form-field">
            <label class="form-label" for="contact">Kontak Info (No. HP / WhatsApp)</label>
            <input type="text" id="contact" name="contact_info" class="form-control-app" placeholder="Masukkan nomor yang dapat dihubungi" required>
          </div>

          <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:8px;">
            Kirim Data Donasi Barang
          </button>
        </form>
      </div>

      <aside class="contact-info-card">
        <span class="section-kicker" style="color:var(--coral-400);">Titik Pengumpulan</span>
        <h3>Dekanat Undip, Tembalang</h3>
        <p>Serahkan paket donasi Anda ke meja penerimaan relawan setelah mengisi formulir di samping.</p>

        <div class="contact-detail-list">
          <div class="contact-detail-item">
            <span class="contact-detail-icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </span>
            <div>
              <strong>Alamat Lengkap</strong>
              <span>Jl. Prof. Soedarto, SH, Tembalang, Semarang, Jawa Tengah 1269</span>
            </div>
          </div>
          <div class="contact-detail-item">
            <span class="contact-detail-icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            </span>
            <div>
              <strong>Barang yang Diterima</strong>
              <span>Sembako/bahan pangan, pakaian layak pakai, buku tulis &amp; bacaan, perlengkapan sekolah, serta mainan edukatif.</span>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </main>

  <script src="assets/js/app.js"></script>
</body>
</html>
