<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (empty($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: formpengajuan.php");
    exit();
}

if (!csrf_verify()) {
    set_flash('error', 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.');
    header("Location: formpengajuan.php");
    exit();
}

try {
    $pdo = get_pdo();
    $signup_id = get_current_user_id($pdo);
    if (!$signup_id) {
        header("Location: login.html");
        exit();
    }

    $assistance_type = trim($_POST['assistanceType'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $valid_assistance_types = ['Bantuan Barang dan Pangan', 'Bantuan Dana'];

    if (!in_array($assistance_type, $valid_assistance_types, true) || $description === '') {
        set_flash('error', 'Jenis bantuan atau deskripsi permohonan tidak valid.');
        header("Location: formpengajuan.php");
        exit();
    }

    // Securely handle uploaded supporting document
    $stored_document_path = null;
    if (isset($_FILES['supportingDocuments']) && $_FILES['supportingDocuments']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['supportingDocuments'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            set_flash('error', 'Gagal mengunggah dokumen pendukung.');
            header("Location: formpengajuan.php");
            exit();
        }

        $maxSize = 5 * 1024 * 1024; // 5 MB
        if ($file['size'] > $maxSize) {
            set_flash('error', 'Ukuran dokumen pendukung maksimal 5 MB.');
            header("Location: formpengajuan.php");
            exit();
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowedMime = ['application/pdf', 'image/jpeg', 'image/png'];

        if (!in_array($ext, $allowedExt, true) || !in_array($mime, $allowedMime, true)) {
            set_flash('error', 'Format dokumen harus berupa PDF, JPG, atau PNG.');
            header("Location: formpengajuan.php");
            exit();
        }

        $uploadDir = __DIR__ . '/uploads/documents';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $safeName;
        if (function_exists('demo_upload_store')) {
            $stored = demo_upload_store($file['tmp_name'], $targetPath, 'donasi/documents/' . $safeName, 'uploads/documents/' . $safeName, $mime);
            if ($stored === null) {
                set_flash('error', 'Tidak dapat menyimpan dokumen pendukung di server.');
                header("Location: formpengajuan.php");
                exit();
            }
            $stored_document_path = $stored;
        } else {
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                set_flash('error', 'Tidak dapat menyimpan dokumen pendukung di server.');
                header("Location: formpengajuan.php");
                exit();
            }
            $stored_document_path = 'uploads/documents/' . $safeName;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO donation_requests (assistance_type, description, supporting_documents, status, signup_id) VALUES (:type, :desc, :doc, 'Pending', :uid)");
    $stmt->execute([
        ':type' => $assistance_type,
        ':desc' => $description,
        ':doc' => $stored_document_path,
        ':uid' => $signup_id,
    ]);

    header("Location: pengajuan_berhasil.html");
    exit();
} catch (PDOException $e) {
    set_flash('error', 'Terjadi kesalahan saat menyimpan pengajuan bantuan.');
    header("Location: formpengajuan.php");
    exit();
}
