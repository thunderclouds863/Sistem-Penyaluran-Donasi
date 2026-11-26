<?php
require_once __DIR__ . '/includes/bootstrap.php';
$loggedIn = isset($_SESSION['username']);
$conn = get_mysqli();
$flash = get_flash();

// Get donation status for logged-in user
$donation_status = null;
if ($loggedIn) {
  $sessionUser = $_SESSION['username'];
  $query = "SELECT donation_centers.status
              FROM donation_centers
              JOIN signup ON donation_centers.signup_id = signup.id
              WHERE signup.username = ?
              ORDER BY donation_centers.id DESC
              LIMIT 1";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $sessionUser);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $donation_status = $row['status'];
  }
}

$defaultCenters = [
  ['name' => 'Panti Asuhan Harapan Bangsa', 'address' => 'Jl. Kebangkitan No. 45, Jakarta', 'contact' => '(021) 555-1234'],
  ['name' => 'Panti Asuhan Cinta Kasih', 'address' => 'Jl. Damai No. 12, Bandung', 'contact' => '(022) 666-5678'],
  ['name' => 'Panti Asuhan Bintang Kecil', 'address' => 'Jl. Mentari No. 8, Yogyakarta', 'contact' => '(0274) 889-1111'],
  ['name' => 'Panti Asuhan Pelita Hati', 'address' => 'Jl. Cinta No. 25, Surabaya', 'contact' => '(031) 123-4567'],
  ['name' => 'Panti Asuhan Kasih Ibu', 'address' => 'Jl. Bahagia No. 32, Medan', 'contact' => '(061) 777-8900'],
];

$approvedCenters = [];
if ($loggedIn) {
  $sql = "SELECT * FROM donation_centers WHERE status = 'Approved'";
  $res = $conn->query($sql);
  if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
      $approvedCenters[] = $row;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pilih Tempat Donasi — Donasi Panti Asuhan</title>
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
        <li><a href="index.php">Beranda</a></li>
        <li><a href="index.php#about">Tentang</a></li>
        <li><a href="donation.php" class="active">Tempat Donasi</a></li>
        <li><a href="formpengajuan.php">Pengajuan Bantuan</a></li>
        <?php if ($loggedIn): ?>
          <li><a href="#" data-modal-open="donationStatusModal">Status Pengajuan Posko</a></li>
        <?php endif; ?>
      </ul>

      <div class="nav-actions">
        <?php if ($loggedIn): ?>
          <a href="logout.php" class="btn btn-secondary btn-sm">Keluar (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
        <?php else: ?>
          <a href="login.html" class="btn btn-primary btn-sm">Masuk</a>
        <?php endif; ?>
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
      <?php if ($loggedIn): ?>
        <li><a href="#" data-modal-open="donationStatusModal">Status Pengajuan Posko</a></li>
      <?php endif; ?>
    </ul>
    <div class="mobile-nav-divider"></div>
    <?php if ($loggedIn): ?>
      <a href="logout.php" class="btn btn-danger-outline btn-block">Keluar</a>
    <?php else: ?>
      <a href="login.html" class="btn btn-primary btn-block">Masuk</a>
    <?php endif; ?>
  </nav>

  <!-- Banner & Stepper -->
  <section class="page-banner">
    <div class="container-app">
      <h1>Pilih Tujuan Penyaluran Donasi</h1>
      <p>Pilih panti asuhan atau posko bantuan terverifikasi yang ingin Anda bantu, atau ajukan tempat donasi baru untuk ditinjau admin.</p>
      <div class="stepper" aria-label="Langkah donasi">
        <span class="step-pill is-active">1. Pilih Tempat Donasi</span>
        <span class="step-pill">2. Pilih Jenis Donasi</span>
        <span class="step-pill">3. Selesaikan Donasi</span>
      </div>
    </div>
  </section>

  <!-- Main Content -->
  <main class="container-app">
    <?php if ($flash): ?>
      <div class="alert-banner is-<?= e($flash['type']) ?>" style="margin-top:24px; margin-bottom:0;" role="status"><?= e($flash['message']) ?></div>
    <?php endif; ?>
    <?php if ($loggedIn): ?>
      <div class="centers-layout">
        <!-- Left Column: Donation Centers List -->
        <div>
          <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
            <h2 style="font-size:1.35rem; margin:0;">Daftar Panti &amp; Posko Terverifikasi</h2>
            <button type="button" class="btn btn-secondary btn-sm" data-modal-open="donationStatusModal">
              Cek Status Pengajuan Saya
            </button>
          </div>

          <div class="search-bar-wrap">
            <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="search" id="centerSearchInput" class="form-control-app" placeholder="Cari nama panti atau kota..." aria-label="Cari tempat donasi">
          </div>

          <div class="center-card-list">
            <?php foreach ($defaultCenters as $c): ?>
              <article class="center-card" data-center-card data-search-text="<?= htmlspecialchars(strtolower($c['name'] . ' ' . $c['address'])) ?>">
                <div class="center-card-info">
                  <h3><?= htmlspecialchars($c['name']) ?></h3>
                  <div class="center-meta-row">
                    <span>
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                      <?= htmlspecialchars($c['address']) ?>
                    </span>
                    <span>
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                      <?= htmlspecialchars($c['contact']) ?>
                    </span>
                  </div>
                </div>
                <button type="button" class="btn btn-primary"
                  onclick='goToJenisDonasi(<?= json_encode($c["name"]) ?>, <?= json_encode($c["address"]) ?>, <?= json_encode($c["contact"]) ?>)'>
                  Pilih Tujuan &rarr;
                </button>
              </article>
            <?php endforeach; ?>

            <?php foreach ($approvedCenters as $row): ?>
              <article class="center-card" data-center-card data-search-text="<?= htmlspecialchars(strtolower($row['name'] . ' ' . $row['address'])) ?>">
                <div class="center-card-info">
                  <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:6px;">
                    <h3 style="margin:0;"><?= htmlspecialchars($row['name']) ?></h3>
                    <span class="status-badge status-approved">Terverifikasi</span>
                  </div>
                  <div class="center-meta-row">
                    <span>
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                      <?= htmlspecialchars($row['address']) ?>
                    </span>
                    <span>
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                      <?= htmlspecialchars($row['contact_info']) ?>
                    </span>
                  </div>
                </div>
                <button type="button" class="btn btn-primary"
                  onclick='goToJenisDonasi(<?= json_encode($row["name"]) ?>, <?= json_encode($row["address"]) ?>, <?= json_encode($row["contact_info"]) ?>)'>
                  Pilih Tujuan &rarr;
                </button>
              </article>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Right Column: Add New Donation Center Form -->
        <aside class="sidebar-card">
          <h3>Ajukan Tempat Donasi Baru</h3>
          <p>Belum menemukan panti asuhan atau posko yang Anda tuju? Daftarkan di sini untuk ditinjau oleh admin.</p>
          <form id="newDonationCenterForm" action="process_donation_center.php" method="POST">
            <?= csrf_field() ?>
            <div class="form-field">
              <label class="form-label" for="new_name">Nama Panti Asuhan / Posko</label>
              <input type="text" class="form-control-app" id="new_name" name="new_name" placeholder="Contoh: Panti Asuhan Nurul Huda" required>
            </div>
            <div class="form-field">
              <label class="form-label" for="new_address">Alamat Lengkap</label>
              <input type="text" class="form-control-app" id="new_address" name="new_address" placeholder="Jalan, Kecamatan, Kota" required>
            </div>
            <div class="form-field">
              <label class="form-label" for="new_contact">Kontak Info</label>
              <input type="text" class="form-control-app" id="new_contact" name="new_contact" placeholder="Nomor telepon / WhatsApp" required>
            </div>
            <button type="submit" class="btn btn-navy btn-block">Ajukan Tempat Donasi</button>
          </form>
        </aside>
      </div>
    <?php else: ?>
      <div style="max-width:540px; margin:56px auto; text-align:center;" class="surface-card">
        <div class="empty-state-icon" style="background:var(--coral-100); color:var(--coral-600);">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <h2 style="font-size:1.35rem; margin-bottom:8px;">Login Diperlukan untuk Berdonasi</h2>
        <p style="color:var(--ink-500); margin-bottom:24px;">Masuk terlebih dahulu menggunakan akun mahasiswa/donatur agar donasi Anda tercatat di riwayat penyaluran.</p>
        <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
          <a href="login.html" class="btn btn-primary">Masuk Sekarang</a>
          <a href="signup.html" class="btn btn-secondary">Daftar Akun Baru</a>
        </div>
      </div>
    <?php endif; ?>
  </main>

  <!-- Status Modal -->
  <div class="app-modal-backdrop" id="donationStatusModal" aria-hidden="true" role="dialog" aria-labelledby="donationStatusTitle">
    <div class="app-modal" style="max-width:480px;">
      <div class="app-modal-header">
        <h3 id="donationStatusTitle">Status Pengajuan Tempat Donasi</h3>
        <button type="button" class="app-modal-close" data-modal-close aria-label="Tutup">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div class="app-modal-body">
        <?php if ($loggedIn): ?>
          <?php if ($donation_status): ?>
            <p style="margin-bottom:14px;">Status pengajuan terakhir Anda: <span class="status-badge status-<?= htmlspecialchars(strtolower($donation_status)) ?>"><?= htmlspecialchars($donation_status) ?></span></p>
            <?php if ($donation_status == 'Approved'): ?>
              <div class="alert-banner is-success">Tempat donasi Anda sudah disetujui dan dapat dipilih untuk penyaluran.</div>
            <?php elseif ($donation_status == 'Pending'): ?>
              <div class="alert-banner is-warning">Pengajuan Anda masih dalam proses peninjauan oleh admin.</div>
            <?php elseif ($donation_status == 'Rejected'): ?>
              <div class="alert-banner is-error">Maaf, pengajuan Anda ditolak. Silakan periksa kembali data dan ajukan ulang.</div>
            <?php else: ?>
              <div class="alert-banner is-info">Anda sudah memilih tempat donasi aktif untuk penyaluran.</div>
            <?php endif; ?>
          <?php else: ?>
            <p style="color:var(--ink-500); margin:0;">Anda belum mengajukan tempat donasi baru.</p>
          <?php endif; ?>
        <?php else: ?>
          <p style="color:var(--ink-500); margin:0;">Silakan login terlebih dahulu untuk melihat status pengajuan.</p>
        <?php endif; ?>
      </div>
      <div class="app-modal-footer">
        <button type="button" class="btn btn-secondary" data-modal-close>Tutup</button>
      </div>
    </div>
  </div>

  <footer class="site-footer">
    <div class="container-app">
      <div class="footer-bottom" style="padding-top:0; border:none;">
        <span>&copy; 2024 Donasi Panti Asuhan. Semua Hak Dilindungi.</span>
        <a href="index.php" style="color:rgba(255,255,255,0.75);">Kembali ke Beranda</a>
      </div>
    </div>
  </footer>

  <script src="assets/js/app.js"></script>
  <script>
    function goToJenisDonasi(name, address, contact) {
      const form = document.createElement("form");
      form.method = "POST";
      form.action = "process_donation_center_approval.php";

      const fields = {
        donation_center_name: name,
        donation_center_address: address,
        donation_center_contact: contact
      };

      Object.keys(fields).forEach(function (key) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = fields[key];
        form.appendChild(input);
      });

      document.body.appendChild(form);
      form.submit();
    }
  </script>
</body>
</html>
<?php $conn->close(); ?>
