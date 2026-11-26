<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (empty($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: qris.php");
    exit();
}

if (!csrf_verify()) {
    set_flash('error', 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.');
    header("Location: qris.php");
    exit();
}

try {
    $pdo = get_pdo();
    $signup_id = get_current_user_id($pdo);
    if (!$signup_id) {
        header("Location: login.html");
        exit();
    }

    $donation_amount = filter_var($_POST['donation_amount'] ?? null, FILTER_VALIDATE_FLOAT);
    $contact_info = trim($_POST['contact_info'] ?? '');

    if ($donation_amount === false || $donation_amount < 1000 || $contact_info === '') {
        set_flash('error', 'Masukkan nominal donasi minimal Rp 1.000 dan informasi kontak yang valid.');
        header("Location: qris.php");
        exit();
    }

    // Determine target donation_center_id from session or fallback to latest approved/default center
    $donation_center_id = !empty($_SESSION['selected_donation_center_id']) ? (int) $_SESSION['selected_donation_center_id'] : null;
    if (!$donation_center_id) {
        $stmt = $pdo->prepare("SELECT id FROM donation_centers WHERE signup_id = :uid ORDER BY id DESC LIMIT 1");
        $stmt->execute([':uid' => $signup_id]);
        $row = $stmt->fetch();
        if ($row) {
            $donation_center_id = (int) $row['id'];
        } else {
            $fallback = $pdo->query("SELECT id FROM donation_centers WHERE status IN ('Approved', 'Default') ORDER BY id ASC LIMIT 1")->fetch();
            $donation_center_id = $fallback ? (int) $fallback['id'] : null;
        }
    }

    if (!$donation_center_id) {
        set_flash('error', 'Silakan pilih tempat donasi tujuan terlebih dahulu.');
        header("Location: donation.php");
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO financial_donations (amount, contact_info, status, signup_id, donation_center_id) VALUES (:amount, :contact, 'Pending', :uid, :cid)");
    $stmt->execute([
        ':amount' => $donation_amount,
        ':contact' => $contact_info,
        ':uid' => $signup_id,
        ':cid' => $donation_center_id,
    ]);

    header("Location: payment-success.html");
    exit();
} catch (PDOException $e) {
    set_flash('error', 'Gagal memproses donasi QRIS. Silakan coba kembali.');
    header("Location: qris.php");
    exit();
}
