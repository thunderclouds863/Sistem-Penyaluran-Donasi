<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (empty($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: donation.php");
    exit();
}

if (!csrf_verify()) {
    set_flash('error', 'Sesi formulir tidak valid. Silakan coba lagi.');
    header("Location: donation.php");
    exit();
}

try {
    $pdo = get_pdo();
    $signup_id = get_current_user_id($pdo);
    if (!$signup_id) {
        header("Location: login.html");
        exit();
    }

    $name = trim($_POST['new_name'] ?? ($_POST['donation_center_name'] ?? ''));
    $address = trim($_POST['new_address'] ?? ($_POST['donation_center_address'] ?? ''));
    $contact = trim($_POST['new_contact'] ?? ($_POST['donation_center_contact'] ?? ''));

    if ($name === '' || $address === '' || $contact === '') {
        set_flash('error', 'Data tidak lengkap. Pastikan nama, alamat, dan kontak posko diisi.');
        header("Location: donation.php");
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO donation_centers (signup_id, name, address, contact_info, status) VALUES (:signup_id, :name, :address, :contact, 'Pending')");
    $stmt->execute([
        ':signup_id' => $signup_id,
        ':name' => $name,
        ':address' => $address,
        ':contact' => $contact,
    ]);

    set_flash('success', 'Pengajuan tempat donasi "' . $name . '" berhasil dikirim dan menunggu persetujuan admin.');
    header("Location: donation.php");
    exit();
} catch (PDOException $e) {
    set_flash('error', 'Terjadi kesalahan saat menyimpan tempat donasi baru.');
    header("Location: donation.php");
    exit();
}
