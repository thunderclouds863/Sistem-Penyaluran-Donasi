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

try {
    $pdo = get_pdo();
    $signup_id = get_current_user_id($pdo);
    if (!$signup_id) {
        header("Location: login.html");
        exit();
    }

    $name = trim($_POST['donation_center_name'] ?? '');
    $address = trim($_POST['donation_center_address'] ?? '');
    $contact = trim($_POST['donation_center_contact'] ?? '');

    if ($name === '' || $address === '' || $contact === '') {
        set_flash('error', 'Detail tempat donasi tidak lengkap.');
        header("Location: donation.php");
        exit();
    }

    // Reuse existing center if already registered with same name & address
    $find = $pdo->prepare("SELECT id FROM donation_centers WHERE name = :name AND address = :address LIMIT 1");
    $find->execute([':name' => $name, ':address' => $address]);
    $existing = $find->fetch();

    if ($existing) {
        $centerId = (int) $existing['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO donation_centers (signup_id, name, address, contact_info, status) VALUES (:signup_id, :name, :address, :contact, 'Default')");
        $stmt->execute([
            ':signup_id' => $signup_id,
            ':name' => $name,
            ':address' => $address,
            ':contact' => $contact,
        ]);
        $centerId = (int) $pdo->lastInsertId();
    }

    $_SESSION['selected_donation_center_id'] = $centerId;
    $_SESSION['selected_donation_center_name'] = $name;

    header("Location: jenis_donasi.html");
    exit();
} catch (PDOException $e) {
    set_flash('error', 'Gagal memproses pilihan tempat donasi.');
    header("Location: donation.php");
    exit();
}
