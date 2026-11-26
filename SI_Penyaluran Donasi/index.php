<?php
require_once __DIR__ . '/includes/bootstrap.php';
$isLoggedIn = isset($_SESSION['username']);
$pdo = get_pdo();
$flash = get_flash();

// Fetching user-scoped donation & request history only when logged in
$donationStatus = [];
if ($isLoggedIn) {
  $currentUserId = get_current_user_id($pdo);
  if ($currentUserId) {
    $finStmt = $pdo->prepare("SELECT amount, status, created_at FROM financial_donations WHERE signup_id = :uid");
    $finStmt->execute([':uid' => $currentUserId]);
    foreach ($finStmt->fetchAll() as $row) {
      $donationStatus[] = [
        'type' => 'Donasi Finansial',
        'description' => 'Rp ' . number_format((float) $row['amount'], 0, ',', '.'),
        'status' => $row['status'],
        'created_at' => $row['created_at'],
      ];
    }

    $itemStmt = $pdo->prepare("SELECT item_type, status, created_at FROM item_donations WHERE signup_id = :uid");
    $itemStmt->execute([':uid' => $currentUserId]);
    foreach ($itemStmt->fetchAll() as $row) {
      $donationStatus[] = [
        'type' => 'Donasi Barang',
        'description' => 'Barang: ' . $row['item_type'],
        'status' => $row['status'],
        'created_at' => $row['created_at'],
      ];
    }

    $reqStmt = $pdo->prepare("SELECT assistance_type, status, created_at FROM donation_requests WHERE signup_id = :uid");
    $reqStmt->execute([':uid' => $currentUserId]);
    foreach ($reqStmt->fetchAll() as $row) {
      $donationStatus[] = [
        'type' => 'Pengajuan Bantuan',
        'description' => 'Bantuan: ' . $row['assistance_type'],
        'status' => $row['status'],
        'created_at' => $row['created_at'],
      ];
    }

    usort($donationStatus, function ($a, $b) {
      return strcmp((string) $b['created_at'], (string) $a['created_at']);
    });
  }
}

// Live impact metrics from database
$totalApprovedCenters = (int) $pdo->query("SELECT COUNT(*) FROM donation_centers WHERE status = 'Approved'")->fetchColumn() + 5;
$totalConfirmedFunds = (float) $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM financial_donations WHERE status = 'Confirmed'")->fetchColumn();
$totalDonationsCount = (int) $pdo->query("SELECT COUNT(*) FROM financial_donations")->fetchColumn()
                     + (int) $pdo->query("SELECT COUNT(*) FROM item_donations")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Donasi Panti Asuhan — Sistem Penyaluran Donasi Transparan</title>
  <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="assets/css/design-system.css">
</head>
<body>
  <!-- Header -->
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
        <li><a href="#home" class="active">Beranda</a></li>
        <li><a href="#about">Tentang</a></li>
        <li><a href="#donation">Cara Donasi</a></li>
        <li><a href="formpengajuan.php">Pengajuan Bantuan</a></li>
        <li><a href="#contact">Kontak</a></li>
        <?php if ($isLoggedIn): ?>
          <li><a href="#" data-modal-open="riwayatModal">Riwayat</a></li>
        <?php endif; ?>
      </ul>

      <div class="nav-actions">
        <?php if ($isLoggedIn): ?>
          <a href="donation.php" class="btn btn-primary btn-sm">Mulai Donasi</a>
          <div class="dropdown-wrap">
            <button type="button" class="dropdown-trigger is-user" data-dropdown-toggle aria-expanded="false">
              <span class="user-avatar-dot"><?= htmlspecialchars(substr($_SESSION['username'], 0, 1)) ?></span>
              <span><?= htmlspecialchars($_SESSION['username']) ?></span>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="dropdown-panel">
              <div class="dropdown-panel-label">Akun Donatur</div>
              <a href="#" data-modal-open="riwayatModal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Riwayat Donasi
              </a>
              <a href="donation.php">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                Pilih Tempat Donasi
              </a>
              <a href="logout.php" class="is-danger">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Keluar
              </a>
            </div>
          </div>
        <?php else: ?>
          <div class="dropdown-wrap">
            <button type="button" class="dropdown-trigger" data-dropdown-toggle aria-expanded="false">
              <span>Masuk</span>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="dropdown-panel">
              <div class="dropdown-panel-label">Pilih Akses Masuk</div>
              <a href="login.html">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                Mahasiswa / Donatur
              </a>
              <a href="login_admin.html">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Administrator
              </a>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <button type="button" class="nav-toggle" data-nav-toggle aria-label="Buka menu navigasi" aria-expanded="false">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
    </div>
  </header>

  <!-- Mobile Navigation Drawer -->
  <nav class="mobile-drawer" data-mobile-drawer aria-label="Navigasi Mobile">
    <ul class="mobile-nav-list">
      <li><a href="#home">Beranda</a></li>
      <li><a href="#about">Tentang Kami</a></li>
      <li><a href="#donation">Cara Donasi</a></li>
      <li><a href="donation.php">Pilih Tempat Donasi</a></li>
      <li><a href="formpengajuan.php">Pengajuan Bantuan</a></li>
      <li><a href="#contact">Kontak</a></li>
      <?php if ($isLoggedIn): ?>
        <li><a href="#" data-modal-open="riwayatModal">Riwayat Donasi</a></li>
      <?php endif; ?>
    </ul>
    <div class="mobile-nav-divider"></div>
    <?php if ($isLoggedIn): ?>
      <p class="form-hint" style="margin-bottom:12px;">Masuk sebagai <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
      <a href="logout.php" class="btn btn-danger-outline btn-block">Keluar</a>
    <?php else: ?>
      <div style="display:flex; flex-direction:column; gap:10px;">
        <a href="login.html" class="btn btn-primary btn-block">Masuk sebagai Mahasiswa</a>
        <a href="login_admin.html" class="btn btn-secondary btn-block">Masuk sebagai Admin</a>
      </div>
    <?php endif; ?>
  </nav>

  <!-- Riwayat Modal -->
  <div class="app-modal-backdrop" id="riwayatModal" aria-hidden="true" role="dialog" aria-labelledby="riwayatModalLabel">
    <div class="app-modal">
      <div class="app-modal-header">
        <h3 id="riwayatModalLabel">Riwayat Donasi &amp; Pengajuan</h3>
        <button type="button" class="app-modal-close" data-modal-close aria-label="Tutup">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div class="app-modal-body">
        <?php if (count($donationStatus) > 0): ?>
          <div class="history-list">
            <?php foreach ($donationStatus as $status):
              $statusClass = 'status-' . strtolower($status['status']);
            ?>
              <div class="history-row">
                <div class="history-row-info">
                  <strong><?= htmlspecialchars($status['type']) ?></strong>
                  <span><?= htmlspecialchars($status['description']) ?></span>
                </div>
                <span class="status-badge <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($status['status']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <h3>Belum ada riwayat transaksi</h3>
            <p>Donasi dan pengajuan bantuan yang Anda kirim akan muncul di sini.</p>
          </div>
        <?php endif; ?>
      </div>
      <div class="app-modal-footer">
        <button type="button" class="btn btn-secondary" data-modal-close>Tutup</button>
        <a href="donation.php" class="btn btn-primary">Beri Donasi Lagi</a>
      </div>
    </div>
  </div>

  <main>
    <!-- Hero Section -->
    <section class="hero-section" id="home">
      <div class="container-app hero-grid">
        <div>
          <span class="eyebrow-pill">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Penyaluran Terverifikasi &amp; Akuntabel
          </span>
          <h1 class="hero-title">
            Salurkan Kebaikan dengan <span class="accent">Transparan</span> untuk Anak Panti Asuhan
          </h1>
          <p class="hero-subtitle">
            Hubungkan kepedulian Anda langsung dengan panti asuhan dan posko bantuan yang terverifikasi—baik melalui donasi dana QRIS maupun bantuan logistik barang.
          </p>
          <div class="hero-cta-row">
            <a href="donation.php" class="btn btn-primary btn-lg">
              Beri Donasi Sekarang
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <a href="formpengajuan.php" class="btn btn-secondary btn-lg">Ajukan Bantuan</a>
          </div>

          <div class="hero-metrics tabular-nums">
            <div class="metric-item">
              <strong><?= $totalApprovedCenters ?></strong>
              <span>Panti &amp; Posko Aktif</span>
            </div>
            <div class="metric-item">
              <strong>Rp <?= number_format($totalConfirmedFunds, 0, ',', '.') ?></strong>
              <span>Dana Terkonfirmasi</span>
            </div>
            <div class="metric-item">
              <strong><?= $totalDonationsCount ?></strong>
              <span>Transaksi Tercatat</span>
            </div>
          </div>
        </div>

        <div class="hero-visual">
          <div class="hero-art-frame">
            <img src="assets/img/hero-donasi.jpg" alt="Relawan mahasiswa menyiapkan paket donasi buku, pakaian, dan kebutuhan pokok untuk anak panti asuhan">
          </div>
          <div class="hero-floating-tag">
            <span class="floating-icon" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            </span>
            <div>
              <strong>Terpantau Hingga Diterima</strong>
              <span>Setiap donasi diverifikasi oleh tim admin</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section class="section-pad section-surface" id="about">
      <div class="container-app about-grid">
        <div class="about-art-wrap">
          <img src="assets/img/about-community.jpg" alt="Anak-anak panti asuhan belajar dan membaca bersama relawan">
        </div>

        <div>
          <span class="section-kicker">Tentang Kami</span>
          <h2 class="section-heading">Menjembatani Donatur, Pengelola, dan Penerima Manfaat</h2>
          <p class="section-lead">
            Sistem Informasi Penyaluran Donasi dirancang untuk mempermudah pengumpulan dan distribusi bantuan agar tepat sasaran, terdokumentasi rapi, dan dapat dipantau statusnya.
          </p>

          <div class="vm-list">
            <div class="vm-item">
              <span class="vm-icon" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
              </span>
              <div>
                <h3>Visi</h3>
                <p>Meningkatkan kualitas hidup dan pendidikan anak-anak di panti asuhan melalui kolaborasi bantuan masyarakat yang berkelanjutan.</p>
              </div>
            </div>
            <div class="vm-item">
              <span class="vm-icon is-coral" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
              </span>
              <div>
                <h3>Misi</h3>
                <p>Menyalurkan setiap donasi dana maupun barang secara transparan dan akuntabel agar bantuan benar-benar sampai kepada pihak yang membutuhkan.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Donation Pathways Section -->
    <section class="section-pad" id="donation">
      <div class="container-app">
        <span class="section-kicker">Cara Berkontribusi</span>
        <h2 class="section-heading">Pilih Bentuk Bantuan yang Paling Sesuai</h2>
        <p class="section-lead">Anda dapat berdonasi dalam bentuk dana melalui QRIS maupun barang kebutuhan pokok melalui posko pengumpulan resmi.</p>

        <div class="pathway-grid">
          <article class="pathway-card">
            <div>
              <div class="pathway-top">
                <div class="pathway-icon-box" aria-hidden="true">
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                </div>
                <span class="pathway-tag">Instan via QRIS</span>
              </div>
              <h3 style="margin-top:18px;">Donasi Finansial</h3>
              <p>Bantuan dana disalurkan untuk memenuhi kebutuhan harian, nutrisi, perlengkapan sekolah, dan operasional panti asuhan pilihan Anda.</p>
            </div>
            <div>
              <div class="pathway-meta">
                <span>
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                  Nominal fleksibel
                </span>
                <span>
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                  Verifikasi tercatat
                </span>
              </div>
              <div style="margin-top:18px;">
                <a href="donation.php" class="btn btn-navy btn-block">Pilih Panti &amp; Donasi Dana</a>
              </div>
            </div>
          </article>

          <article class="pathway-card">
            <div>
              <div class="pathway-top">
                <div class="pathway-icon-box is-sage" aria-hidden="true">
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                </div>
                <span class="pathway-tag">Drop-Off Posko</span>
              </div>
              <h3 style="margin-top:18px;">Donasi Barang</h3>
              <p>Sumbangkan bahan pangan, pakaian layak pakai, buku bacaan, maupun mainan edukatif melalui titik pengumpulan di Dekanat Undip.</p>
            </div>
            <div>
              <div class="pathway-meta">
                <span>
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                  Pangan, pakaian, buku
                </span>
                <span>
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                  Status pengiriman jelas
                </span>
              </div>
              <div style="margin-top:18px;">
                <a href="donation.php" class="btn btn-secondary btn-block">Pilih Panti &amp; Donasi Barang</a>
              </div>
            </div>
          </article>
        </div>

        <div class="process-strip">
          <div class="process-step">
            <span class="process-num">1</span>
            <div>
              <strong>Pilih Panti atau Posko</strong>
              <span>Tentukan tujuan penyaluran dari daftar panti terverifikasi atau ajukan lokasi baru.</span>
            </div>
          </div>
          <div class="process-step">
            <span class="process-num">2</span>
            <div>
              <strong>Kirim Dana atau Barang</strong>
              <span>Selesaikan pembayaran QRIS atau daftarkan barang untuk diserahkan ke posko pengumpulan.</span>
            </div>
          </div>
          <div class="process-step">
            <span class="process-num">3</span>
            <div>
              <strong>Pantau Verifikasi</strong>
              <span>Tim admin memverifikasi setiap transaksi dan Anda dapat memantau statusnya di menu Riwayat.</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section class="section-pad section-surface" id="contact">
      <div class="container-app contact-grid">
        <div class="contact-info-card">
          <span class="section-kicker" style="color:var(--coral-400);">Hubungi Tim Kami</span>
          <h3>Ada Pertanyaan atau Ingin Berkolaborasi?</h3>
          <p>Kami siap membantu informasi seputar penyaluran donasi, pengajuan panti baru, maupun koordinasi logistik.</p>

          <div class="contact-detail-list">
            <div class="contact-detail-item">
              <span class="contact-detail-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              </span>
              <div>
                <strong>Posko Pengumpulan Utama</strong>
                <span>Dekanat Undip, Jl. Prof. Soedarto, SH, Tembalang, Semarang</span>
              </div>
            </div>
            <div class="contact-detail-item">
              <span class="contact-detail-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              </span>
              <div>
                <strong>Jam Layanan Penerimaan</strong>
                <span>Senin – Jumat, 08.00 – 16.00 WIB</span>
              </div>
            </div>
            <div class="contact-detail-item">
              <span class="contact-detail-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              </span>
              <div>
                <strong>Layanan Pengajuan Bantuan</strong>
                <span>Gunakan halaman Pengajuan Bantuan untuk permohonan resmi panti.</span>
              </div>
            </div>
          </div>
        </div>

        <div class="contact-form-card">
          <h3 style="font-size:1.3rem; margin-bottom:6px;">Kirim Pesan</h3>
          <p class="form-hint" style="margin-bottom:22px;">Pesan Anda akan langsung masuk ke dashboard pengelola kami.</p>
          <?php if ($flash): ?>
            <div class="alert-banner is-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
          <?php endif; ?>
          <form method="POST" action="submit_contact.php">
            <?= csrf_field() ?>
            <div class="form-field">
              <label class="form-label" for="contact_name">Nama Lengkap</label>
              <input type="text" id="contact_name" name="full_name" class="form-control-app" placeholder="Masukkan nama Anda" required>
            </div>
            <div class="form-field">
              <label class="form-label" for="contact_email">Alamat Email</label>
              <input type="email" id="contact_email" name="email" class="form-control-app" placeholder="nama@email.com" required>
            </div>
            <div class="form-field">
              <label class="form-label" for="contact_message">Pesan</label>
              <textarea id="contact_message" name="message" class="form-control-app" placeholder="Tuliskan pertanyaan atau masukan Anda..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-lg">
              Kirim Pesan
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
          </form>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="site-footer">
    <div class="container-app">
      <div class="footer-grid">
        <div>
          <div class="footer-brand">
            <span class="brand-mark" aria-hidden="true" style="background:rgba(255,255,255,0.12);">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M12 21C12 21 3 15.2 3 8.8C3 5.6 5.45 3 8.5 3C10.37 3 12 4.02 12 5.55C12 4.02 13.63 3 15.5 3C18.55 3 21 5.6 21 8.8C21 15.2 12 21 12 21Z" fill="#D95D39"/>
              </svg>
            </span>
            Donasi Panti Asuhan
          </div>
          <p style="font-size:0.9rem; max-width:340px; margin:0;">Platform digital penyaluran donasi dana dan barang yang terstruktur, transparan, dan akuntabel untuk panti asuhan.</p>
        </div>

        <div class="footer-col">
          <h4>Navigasi</h4>
          <ul class="footer-links">
            <li><a href="#home">Beranda</a></li>
            <li><a href="#about">Tentang Kami</a></li>
            <li><a href="donation.php">Tempat Donasi</a></li>
            <li><a href="formpengajuan.php">Pengajuan Bantuan</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Akses Akun</h4>
          <ul class="footer-links">
            <li><a href="login.html">Login Mahasiswa</a></li>
            <li><a href="signup.html">Daftar Akun Baru</a></li>
            <li><a href="login_admin.html">Login Admin</a></li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <span>&copy; 2024 Donasi Panti Asuhan. Semua Hak Dilindungi.</span>
        <span>Kebijakan Privasi &middot; Syarat dan Ketentuan</span>
      </div>
    </div>
  </footer>

  <script src="assets/js/app.js"></script>
</body>
</html>
