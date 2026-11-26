<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.html");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "donation_system";

$pending_centers = [];
$financial_donations = [];
$item_donations = [];
$donation_requests = [];
$contact_messages = [];
$confirmed_funds = 0;
$approved_centers_count = 0;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle form submissions
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id = $_POST['id'] ?? null;
        $action = $_POST['action'] ?? null;

        if ($action === 'approve_center') {
            $stmt = $conn->prepare("UPDATE donation_centers SET status = 'Approved' WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } elseif ($action === 'reject_center') {
            $reason = $_POST['reason'] ?? '';
            $stmt = $conn->prepare("UPDATE donation_centers SET status = 'Rejected', rejection_reason = :reason WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':reason', $reason);
            $stmt->execute();
        } elseif ($action === 'confirm_financial') {
            $stmt = $conn->prepare("UPDATE financial_donations SET status = 'Confirmed' WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } elseif ($action === 'confirm_item') {
            $stmt = $conn->prepare("UPDATE item_donations SET status = 'confirmed' WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } elseif ($action === 'approve_request') {
            $stmt = $conn->prepare("UPDATE donation_requests SET status = 'Confirmed' WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } elseif ($action === 'reject_request') {
            $reason = $_POST['reason'] ?? '';
            $stmt = $conn->prepare("UPDATE donation_requests SET status = 'Reject', rejection_reason = :reason WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':reason', $reason);
            $stmt->execute();
        }
        header("Location: admin_dashboard.php");
        exit();
    }

    // Fetch pending donation centers
    $stmt = $conn->prepare("SELECT dc.id, dc.name, dc.address, dc.contact_info, dc.signup_id, su.full_name
                            FROM donation_centers dc
                            INNER JOIN signup su ON dc.signup_id = su.id
                            WHERE dc.status = 'Pending'");
    $stmt->execute();
    $pending_centers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch pending financial donations
    $stmt = $conn->prepare("SELECT fd.id, fd.amount, fd.contact_info, fd.status, su.full_name, dc.name AS donation_center_name
                            FROM financial_donations fd
                            INNER JOIN signup su ON fd.signup_id = su.id
                            INNER JOIN donation_centers dc ON dc.id = fd.donation_center_id
                            WHERE fd.status = 'Pending'");
    $stmt->execute();
    $financial_donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch pending item donations
    $stmt = $conn->prepare("SELECT id.id, id.item_type, id.location, id.contact_info, id.status, su.full_name, dc.name AS donation_center_name
                            FROM item_donations id
                            INNER JOIN signup su ON id.signup_id = su.id
                            INNER JOIN donation_centers dc ON dc.id = id.donation_center_id
                            WHERE LOWER(id.status) = 'pending'");
    $stmt->execute();
    $item_donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch pending donation requests
    $stmt = $conn->prepare("SELECT dr.id, dr.assistance_type, dr.description, dr.status, su.full_name AS donor_name
                            FROM donation_requests dr
                            INNER JOIN signup su ON dr.signup_id = su.id
                            WHERE dr.status = 'Pending'");
    $stmt->execute();
    $donation_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch contact messages
    $stmt = $conn->prepare("SELECT cm.full_name, cm.email, cm.message FROM contact_messages cm ORDER BY cm.id DESC");
    $stmt->execute();
    $contact_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Live KPI summaries
    $confirmed_funds = (float) $conn->query("SELECT COALESCE(SUM(amount), 0) FROM financial_donations WHERE status = 'Confirmed'")->fetchColumn();
    $approved_centers_count = (int) $conn->query("SELECT COUNT(*) FROM donation_centers WHERE status = 'Approved'")->fetchColumn();
} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}

$total_pending_queue = count($pending_centers) + count($financial_donations) + count($item_donations) + count($donation_requests);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — Donasi Panti Asuhan</title>
  <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="assets/css/design-system.css">
</head>
<body>
  <div class="admin-shell">
    <!-- Left Sidebar (Desktop) -->
    <aside class="admin-sidebar">
      <div>
        <div class="admin-brand">
          <span class="brand-mark" aria-hidden="true" style="background:rgba(255,255,255,0.12);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M12 21C12 21 3 15.2 3 8.8C3 5.6 5.45 3 8.5 3C10.37 3 12 4.02 12 5.55C12 4.02 13.63 3 15.5 3C18.55 3 21 5.6 21 8.8C21 15.2 12 21 12 21Z" fill="#D95D39"/>
              <path d="M8.5 11.8L11 14.3L16 9.2" stroke="#FAF8F5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          <span>Admin Konsol</span>
        </div>

        <div class="admin-nav-label">Antrean Verifikasi</div>
        <ul class="admin-nav">
          <li>
            <button type="button" class="admin-nav-btn is-active" data-section-btn="centers" onclick="showSection('centers')">
              <span class="admin-nav-left">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Posko Donasi
              </span>
              <span class="admin-count-pill tabular-nums"><?= count($pending_centers) ?></span>
            </button>
          </li>
          <li>
            <button type="button" class="admin-nav-btn" data-section-btn="financial" onclick="showSection('financial')">
              <span class="admin-nav-left">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                Donasi Finansial
              </span>
              <span class="admin-count-pill tabular-nums"><?= count($financial_donations) ?></span>
            </button>
          </li>
          <li>
            <button type="button" class="admin-nav-btn" data-section-btn="items" onclick="showSection('items')">
              <span class="admin-nav-left">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                Donasi Barang
              </span>
              <span class="admin-count-pill tabular-nums"><?= count($item_donations) ?></span>
            </button>
          </li>
          <li>
            <button type="button" class="admin-nav-btn" data-section-btn="requests" onclick="showSection('requests')">
              <span class="admin-nav-left">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Pengajuan Bantuan
              </span>
              <span class="admin-count-pill tabular-nums"><?= count($donation_requests) ?></span>
            </button>
          </li>
        </ul>

        <div class="admin-nav-label" style="margin-top:16px;">Komunikasi</div>
        <ul class="admin-nav">
          <li>
            <button type="button" class="admin-nav-btn" data-section-btn="messages" onclick="showSection('messages')">
              <span class="admin-nav-left">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Pesan Kontak
              </span>
              <span class="admin-count-pill tabular-nums"><?= count($contact_messages) ?></span>
            </button>
          </li>
        </ul>
      </div>

      <div class="admin-sidebar-foot">
        <a href="index.php" class="admin-nav-btn" style="text-decoration:none;">
          <span class="admin-nav-left">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Lihat Situs Publik
          </span>
        </a>
        <a href="logout.php" class="admin-nav-btn" style="color:#FCA5A5; text-decoration:none;">
          <span class="admin-nav-left">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Keluar
          </span>
        </a>
      </div>
    </aside>

    <!-- Main Operations Workspace -->
    <div class="admin-main">
      <header class="admin-topbar">
        <div class="admin-topbar-title">
          <h1>Dashboard Penyaluran Donasi</h1>
          <p>Masuk sebagai <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong> &middot; Kelola verifikasi donasi dan posko bantuan</p>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
          <a href="index.php" class="btn btn-secondary btn-sm">Situs Publik</a>
          <a href="logout.php" class="btn btn-danger-outline btn-sm">Logout</a>
        </div>
      </header>

      <!-- Mobile / Tablet Section Switcher -->
      <div class="admin-mobile-tabs" role="tablist">
        <button type="button" class="admin-nav-btn is-active" data-section-btn="centers" onclick="showSection('centers')">
          Posko <span class="admin-count-pill"><?= count($pending_centers) ?></span>
        </button>
        <button type="button" class="admin-nav-btn" data-section-btn="financial" onclick="showSection('financial')">
          Finansial <span class="admin-count-pill"><?= count($financial_donations) ?></span>
        </button>
        <button type="button" class="admin-nav-btn" data-section-btn="items" onclick="showSection('items')">
          Barang <span class="admin-count-pill"><?= count($item_donations) ?></span>
        </button>
        <button type="button" class="admin-nav-btn" data-section-btn="requests" onclick="showSection('requests')">
          Pengajuan <span class="admin-count-pill"><?= count($donation_requests) ?></span>
        </button>
        <button type="button" class="admin-nav-btn" data-section-btn="messages" onclick="showSection('messages')">
          Pesan <span class="admin-count-pill"><?= count($contact_messages) ?></span>
        </button>
      </div>

      <main class="admin-content">
        <!-- Live KPI Summary -->
        <section class="kpi-grid tabular-nums" aria-label="Ringkasan operasional">
          <div class="kpi-card">
            <div class="kpi-card-label">
              <span>Menunggu Verifikasi</span>
              <span class="status-badge status-pending">Pending</span>
            </div>
            <div class="kpi-card-value"><?= $total_pending_queue ?></div>
            <div class="kpi-card-sub">Total item di seluruh antrean</div>
          </div>

          <div class="kpi-card">
            <div class="kpi-card-label">
              <span>Dana Terkonfirmasi</span>
              <span class="status-badge status-confirmed">Confirmed</span>
            </div>
            <div class="kpi-card-value">Rp <?= number_format($confirmed_funds, 0, ',', '.') ?></div>
            <div class="kpi-card-sub">Akumulasi donasi finansial sah</div>
          </div>

          <div class="kpi-card">
            <div class="kpi-card-label">
              <span>Posko Disetujui</span>
            </div>
            <div class="kpi-card-value"><?= $approved_centers_count ?></div>
            <div class="kpi-card-sub">Tempat donasi tambahan aktif</div>
          </div>

          <div class="kpi-card">
            <div class="kpi-card-label">
              <span>Pesan Masuk</span>
            </div>
            <div class="kpi-card-value"><?= count($contact_messages) ?></div>
            <div class="kpi-card-sub">Dari formulir kontak publik</div>
          </div>
        </section>

        <!-- Section 1: Pending Donation Centers -->
        <section id="centers" class="admin-section is-active">
          <div class="data-card">
            <div class="data-card-header">
              <h2>Pengajuan Posko / Tempat Donasi Menunggu Persetujuan</h2>
              <span class="status-badge status-pending"><?= count($pending_centers) ?> Pending</span>
            </div>
            <?php if (count($pending_centers) > 0): ?>
              <div class="table-scroll">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Nama Posko / Panti</th>
                      <th>Alamat</th>
                      <th>Kontak</th>
                      <th>Diajukan Oleh</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($pending_centers as $center): ?>
                      <tr>
                        <td><strong><?= htmlspecialchars($center['name']) ?></strong></td>
                        <td><?= htmlspecialchars($center['address']) ?></td>
                        <td><?= htmlspecialchars($center['contact_info']) ?></td>
                        <td><?= htmlspecialchars($center['full_name']) ?></td>
                        <td>
                          <div class="action-group">
                            <form method="POST" style="display:inline;">
                              <input type="hidden" name="id" value="<?= $center['id'] ?>">
                              <button type="submit" class="btn btn-success btn-sm" name="action" value="approve_center">Setujui</button>
                            </form>
                            <button type="button" class="btn btn-danger-outline btn-sm"
                              onclick='openRejectModal(<?= (int)$center["id"] ?>, "reject_center", <?= json_encode($center["name"]) ?>)'>
                              Tolak
                            </button>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="empty-state">
                <div class="empty-state-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h3>Semua pengajuan posko telah ditinjau</h3>
                <p>Tidak ada pengajuan tempat donasi baru yang menunggu persetujuan saat ini.</p>
              </div>
            <?php endif; ?>
          </div>
        </section>

        <!-- Section 2: Pending Financial Donations -->
        <section id="financial" class="admin-section">
          <div class="data-card">
            <div class="data-card-header">
              <h2>Donasi Finansial Menunggu Konfirmasi</h2>
              <span class="status-badge status-pending"><?= count($financial_donations) ?> Pending</span>
            </div>
            <?php if (count($financial_donations) > 0): ?>
              <div class="table-scroll">
                <table class="data-table tabular-nums">
                  <thead>
                    <tr>
                      <th>Donatur</th>
                      <th>Nominal</th>
                      <th>Kontak Info</th>
                      <th>Tujuan Donasi</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($financial_donations as $donation): ?>
                      <tr>
                        <td><strong><?= htmlspecialchars($donation['full_name']) ?></strong></td>
                        <td><strong>Rp <?= number_format((float)$donation['amount'], 0, ',', '.') ?></strong></td>
                        <td><?= htmlspecialchars($donation['contact_info']) ?></td>
                        <td><?= htmlspecialchars($donation['donation_center_name']) ?></td>
                        <td>
                          <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $donation['id'] ?>">
                            <button type="submit" class="btn btn-success btn-sm" name="action" value="confirm_financial">Konfirmasi Dana</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="empty-state">
                <div class="empty-state-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h3>Tidak ada donasi finansial tertunda</h3>
                <p>Semua transaksi donasi QRIS sudah diverifikasi.</p>
              </div>
            <?php endif; ?>
          </div>
        </section>

        <!-- Section 3: Pending Item Donations -->
        <section id="items" class="admin-section">
          <div class="data-card">
            <div class="data-card-header">
              <h2>Donasi Barang Menunggu Konfirmasi</h2>
              <span class="status-badge status-pending"><?= count($item_donations) ?> Pending</span>
            </div>
            <?php if (count($item_donations) > 0): ?>
              <div class="table-scroll">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Donatur</th>
                      <th>Jenis Barang</th>
                      <th>Lokasi Drop-Off</th>
                      <th>Kontak</th>
                      <th>Tujuan Donasi</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($item_donations as $donation): ?>
                      <tr>
                        <td><strong><?= htmlspecialchars($donation['full_name']) ?></strong></td>
                        <td><?= htmlspecialchars($donation['item_type']) ?></td>
                        <td><?= htmlspecialchars($donation['location']) ?></td>
                        <td><?= htmlspecialchars($donation['contact_info']) ?></td>
                        <td><?= htmlspecialchars($donation['donation_center_name']) ?></td>
                        <td>
                          <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $donation['id'] ?>">
                            <button type="submit" class="btn btn-success btn-sm" name="action" value="confirm_item">Konfirmasi Terima</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="empty-state">
                <div class="empty-state-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h3>Tidak ada donasi barang tertunda</h3>
                <p>Semua donasi barang masuk telah dikonfirmasi.</p>
              </div>
            <?php endif; ?>
          </div>
        </section>

        <!-- Section 4: Pending Assistance Requests -->
        <section id="requests" class="admin-section">
          <div class="data-card">
            <div class="data-card-header">
              <h2>Permohonan Pengajuan Bantuan</h2>
              <span class="status-badge status-pending"><?= count($donation_requests) ?> Pending</span>
            </div>
            <?php if (count($donation_requests) > 0): ?>
              <div class="table-scroll">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Jenis Bantuan</th>
                      <th>Deskripsi Permohonan</th>
                      <th>Pemohon</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($donation_requests as $request): ?>
                      <tr>
                        <td><strong><?= htmlspecialchars($request['assistance_type']) ?></strong></td>
                        <td style="max-width:380px;"><?= htmlspecialchars($request['description']) ?></td>
                        <td><?= htmlspecialchars($request['donor_name']) ?></td>
                        <td>
                          <div class="action-group">
                            <form method="POST" style="display:inline;">
                              <input type="hidden" name="id" value="<?= $request['id'] ?>">
                              <button type="submit" class="btn btn-success btn-sm" name="action" value="approve_request">Setujui</button>
                            </form>
                            <button type="button" class="btn btn-danger-outline btn-sm"
                              onclick='openRejectModal(<?= (int)$request["id"] ?>, "reject_request", <?= json_encode($request["assistance_type"]) ?>)'>
                              Tolak
                            </button>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="empty-state">
                <div class="empty-state-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h3>Tidak ada pengajuan bantuan tertunda</h3>
                <p>Semua permohonan bantuan yang masuk telah diproses.</p>
              </div>
            <?php endif; ?>
          </div>
        </section>

        <!-- Section 5: Contact Messages -->
        <section id="messages" class="admin-section">
          <div class="data-card">
            <div class="data-card-header">
              <h2>Pesan dari Pengunjung</h2>
              <span class="status-badge status-default"><?= count($contact_messages) ?> Pesan</span>
            </div>
            <?php if (count($contact_messages) > 0): ?>
              <div class="table-scroll">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Nama Pengirim</th>
                      <th>Email</th>
                      <th>Isi Pesan</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($contact_messages as $message): ?>
                      <tr>
                        <td><strong><?= htmlspecialchars($message['full_name']) ?></strong></td>
                        <td><a href="mailto:<?= htmlspecialchars($message['email']) ?>"><?= htmlspecialchars($message['email']) ?></a></td>
                        <td style="max-width:460px;"><?= htmlspecialchars($message['message']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="empty-state">
                <div class="empty-state-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3>Belum ada pesan kontak</h3>
                <p>Pesan yang dikirim melalui formulir kontak di halaman utama akan tampil di sini.</p>
              </div>
            <?php endif; ?>
          </div>
        </section>
      </main>
    </div>
  </div>

  <!-- Reject Reason Modal -->
  <div class="app-modal-backdrop" id="rejectModal" aria-hidden="true" role="dialog" aria-labelledby="rejectModalTitle">
    <div class="app-modal" style="max-width:460px;">
      <div class="app-modal-header">
        <h3 id="rejectModalTitle">Tolak Pengajuan</h3>
        <button type="button" class="app-modal-close" data-modal-close aria-label="Tutup">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <form method="POST">
        <div class="app-modal-body">
          <p style="font-size:0.9rem; color:var(--ink-500); margin-bottom:14px;">
            Berikan alasan penolakan untuk <strong id="rejectTargetName" style="color:var(--navy-900);"></strong>:
          </p>
          <input type="hidden" name="id" id="rejectRecordId">
          <input type="hidden" name="action" id="rejectActionType">
          <div class="form-field" style="margin-bottom:0;">
            <label class="form-label" for="rejectReasonInput">Alasan Penolakan</label>
            <textarea id="rejectReasonInput" name="reason" class="form-control-app" placeholder="Tuliskan alasan penolakan secara singkat dan jelas..." required></textarea>
          </div>
        </div>
        <div class="app-modal-footer">
          <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
          <button type="submit" class="btn btn-danger">Konfirmasi Penolakan</button>
        </div>
      </form>
    </div>
  </div>

  <script src="assets/js/app.js"></script>
  <script>
    function showSection(section) {
      document.querySelectorAll('.admin-section').forEach(function (sec) {
        sec.classList.remove('is-active');
      });
      var target = document.getElementById(section);
      if (target) target.classList.add('is-active');

      document.querySelectorAll('[data-section-btn]').forEach(function (btn) {
        btn.classList.toggle('is-active', btn.getAttribute('data-section-btn') === section);
      });
    }

    function openRejectModal(id, action, label) {
      document.getElementById('rejectRecordId').value = id;
      document.getElementById('rejectActionType').value = action;
      document.getElementById('rejectTargetName').textContent = label;
      document.getElementById('rejectReasonInput').value = '';
      window.AppModal.open('rejectModal');
    }
  </script>
</body>
</html>
