<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!isset($_SESSION['username'])) {
  header("Location: login.html");
  exit();
}
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pengajuan Bantuan — Donasi Panti Asuhan</title>
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

      <ul class="nav-links">
        <li><a href="index.php">Beranda</a></li>
        <li><a href="index.php#about">Tentang</a></li>
        <li><a href="donation.php">Tempat Donasi</a></li>
        <li><a href="formpengajuan.php" class="active">Pengajuan Bantuan</a></li>
        <li><a href="index.php#contact">Kontak</a></li>
      </ul>

      <div class="nav-actions">
        <a href="logout.php" class="btn btn-secondary btn-sm">Keluar (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
      </div>

      <button type="button" class="nav-toggle" data-nav-toggle aria-label="Buka menu navigasi" aria-expanded="false">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
    </div>
  </header>

  <nav class="mobile-drawer" data-mobile-drawer aria-label="Navigasi Mobile">
    <ul class="mobile-nav-list">
      <li><a href="index.php">Beranda</a></li>
      <li><a href="donation.php">Tempat Donasi</a></li>
      <li><a href="formpengajuan.php">Pengajuan Bantuan</a></li>
      <li><a href="index.php#contact">Kontak</a></li>
    </ul>
    <div class="mobile-nav-divider"></div>
    <a href="logout.php" class="btn btn-danger-outline btn-block">Keluar</a>
  </nav>

  <section class="page-banner">
    <div class="container-app">
      <h1>Formulir Pengajuan Bantuan</h1>
      <p>Ajukan permohonan bantuan logistik, pangan, atau dana operasional secara resmi agar dapat ditinjau dan diprioritaskan oleh tim pengelola.</p>
    </div>
  </section>

  <main class="container-app">
    <div class="checkout-grid">
      <div class="surface-card">
        <h2 style="font-size:1.35rem; margin-bottom:6px;">Detail Permohonan Bantuan</h2>
        <p class="form-hint" style="margin-bottom:22px;">Lengkapi deskripsi kebutuhan secara jelas serta lampirkan dokumen pendukung (PDF/JPG/PNG, maks. 5 MB).</p>

        <?php if ($flash): ?>
          <div class="alert-banner is-<?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <form action="pengajuan.php" method="POST" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="form-field">
            <label class="form-label" for="assistanceType">Jenis Bantuan</label>
            <select id="assistanceType" name="assistanceType" class="form-control-app" required>
              <option value="">-- Pilih Jenis Bantuan --</option>
              <option value="Bantuan Barang dan Pangan">Bantuan Barang dan Pangan</option>
              <option value="Bantuan Dana">Bantuan Dana</option>
            </select>
          </div>

          <div class="form-field">
            <label class="form-label" for="description">Deskripsi Permohonan</label>
            <textarea id="description" name="description" class="form-control-app" placeholder="Jelaskan secara rinci bantuan yang dibutuhkan, jumlah penerima manfaat, dan tingkat urgensi..." required></textarea>
          </div>

          <div class="form-field">
            <label class="form-label" for="supportingDocuments">Unggah Dokumen Pendukung</label>
            <div class="file-dropzone">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--navy-700); margin:0 auto 6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <div style="font-weight:600; font-size:0.9rem; color:var(--navy-900);">Pilih berkas surat permohonan / dokumentasi</div>
              <div class="form-hint">Format PDF, JPG, atau PNG yang jelas terbaca.</div>
              <input type="file" id="supportingDocuments" name="supportingDocuments" required>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:8px;">
            Kirim Pengajuan Bantuan
          </button>
          <p class="form-hint" style="text-align:center; margin-top:12px;">
            Tim kami akan menghubungi Anda melalui email atau nomor telepon yang terdaftar pada akun Anda.
          </p>
        </form>
      </div>

      <aside class="contact-info-card">
        <span class="section-kicker" style="color:var(--coral-400);">Alur Verifikasi</span>
        <h3>Bagaimana Pengajuan Diproses?</h3>
        <p>Setiap permohonan bantuan diverifikasi secara objektif untuk memastikan penyaluran tepat sasaran.</p>

        <div class="contact-detail-list">
          <div class="contact-detail-item">
            <span class="contact-detail-icon">1</span>
            <div>
              <strong>Pemeriksaan Kelengkapan</strong>
              <span>Admin meninjau deskripsi kebutuhan dan berkas pendukung yang dilampirkan.</span>
            </div>
          </div>
          <div class="contact-detail-item">
            <span class="contact-detail-icon">2</span>
            <div>
              <strong>Konfirmasi &amp; Persetujuan</strong>
              <span>Status permohonan diperbarui pada menu Riwayat di halaman utama.</span>
            </div>
          </div>
          <div class="contact-detail-item">
            <span class="contact-detail-icon">3</span>
            <div>
              <strong>Koordinasi Penyaluran</strong>
              <span>Tim akan menghubungi kontak terdaftar untuk jadwal serah terima bantuan.</span>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </main>

  <footer class="site-footer">
    <div class="container-app">
      <div class="footer-bottom" style="padding-top:0; border:none;">
        <span>&copy; 2024 Donasi Panti Asuhan. Semua Hak Dilindungi.</span>
        <a href="index.php" style="color:rgba(255,255,255,0.75);">Kembali ke Beranda</a>
      </div>
    </div>
  </footer>

  <script src="assets/js/app.js"></script>
</body>
</html>
