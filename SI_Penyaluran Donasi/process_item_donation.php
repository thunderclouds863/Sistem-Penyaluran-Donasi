<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (empty($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: barang.php");
    exit();
}

if (!csrf_verify()) {
    set_flash('error', 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.');
    header("Location: barang.php");
    exit();
}

try {
    $pdo = get_pdo();
    $user_id = get_current_user_id($pdo);
    if (!$user_id) {
        header("Location: login.html");
        exit();
    }

    $location = trim($_POST['location'] ?? '');
    $item_type = trim($_POST['item_type'] ?? '');
    $contact_info = trim($_POST['contact_info'] ?? '');

    if ($location === '' || $item_type === '' || $contact_info === '') {
        set_flash('error', 'Mohon lengkapi lokasi pengumpulan, jenis barang, dan kontak info.');
        header("Location: barang.php");
        exit();
    }

    // Use selected donation center from session, falling back to latest approved/default center
    $donation_center_id = !empty($_SESSION['selected_donation_center_id']) ? (int) $_SESSION['selected_donation_center_id'] : null;
    if (!$donation_center_id) {
        $fallback = $pdo->query("SELECT id FROM donation_centers WHERE status IN ('Approved', 'Default') ORDER BY id ASC LIMIT 1")->fetch();
        $donation_center_id = $fallback ? (int) $fallback['id'] : null;
    }

    if (!$donation_center_id) {
        set_flash('error', 'Silakan pilih tempat donasi tujuan terlebih dahulu.');
        header("Location: donation.php");
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO item_donations (location, item_type, contact_info, status, signup_id, donation_center_id) VALUES (:location, :item_type, :contact, 'pending', :uid, :cid)");
    $stmt->execute([
        ':location' => $location,
        ':item_type' => $item_type,
        ':contact' => $contact_info,
        ':uid' => $user_id,
        ':cid' => $donation_center_id,
    ]);

    header("Location: barang_confirmation.html");
    exit();
} catch (PDOException $e) {
    set_flash('error', 'Gagal menyimpan data donasi barang. Silakan coba lagi.');
    header("Location: barang.php");
    exit();
}
