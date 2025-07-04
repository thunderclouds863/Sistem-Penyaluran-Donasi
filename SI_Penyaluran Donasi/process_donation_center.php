<?php
session_start();

// Cek apakah pengguna sudah login
$loggedIn = isset($_SESSION['username']);

if ($loggedIn) {
    // Ambil ID user yang sedang login berdasarkan username
    $conn = mysqli_connect("localhost", "root", "", "donation_system");
    if ($conn) {
        $username = $_SESSION['username'];  // username from session
        $query = "SELECT id FROM signup WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $signup_id = $user['id'];  // Get the user's ID
        } else {
            // Handle error if no user found
            echo "User not found.";
            exit;
        }
    }

    // Process donation center data (adding new one or selecting an existing one)
    if (isset($_POST['donation_center_name'], $_POST['donation_center_address'], $_POST['donation_center_contact'])) {
        // Check if it's an existing donation center
        if (isset($_POST['existing_donation_center_id'])) {
            // Use the existing donation center (do nothing except redirect)
            $donation_center_id = (int) $_POST['existing_donation_center_id'];
            echo "<script>
        alert('Terima kasih. Data Anda telah kami terima. Proses Bisa Dilanjutkan Setelah Tempat Donasi Disetujui Admin!');
        window.location.href = 'donation.php'; // Redirect setelah alert
    </script>";
            exit();
        }

        // If it's a new donation center, insert it into the database
        $donation_center_name = htmlspecialchars(trim($_POST['donation_center_name']));
        $donation_center_address = htmlspecialchars(trim($_POST['donation_center_address']));
        $donation_center_contact = htmlspecialchars(trim($_POST['donation_center_contact']));

        // Insert the new donation center into the database
        $sql = "INSERT INTO donation_centers (signup_id, name, address, contact_info) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $signup_id, $donation_center_name, $donation_center_address, $donation_center_contact);

        if ($stmt->execute()) {
            echo "<script>
        alert('Terima kasih. Data Anda telah kami terima. Proses Bisa Dilanjutkan Setelah Tempat Donasi Disetujui Admin!');
        window.location.href = 'donation.php'; // Redirect setelah alert
    </script>";
        } else {
            echo "Terjadi kesalahan saat memproses donasi.";
        }
    }
    if (
        isset($_POST['new_name'], $_POST['new_address'], $_POST['new_contact']) &&
        !empty($_POST['new_name']) &&
        !empty($_POST['new_address']) &&
        !empty($_POST['new_contact'])
    ) {

        // Ambil data yang dikirimkan
        $new_name = htmlspecialchars(trim($_POST['new_name']));
        $new_address = htmlspecialchars(trim($_POST['new_address']));
        $new_contact = htmlspecialchars(trim($_POST['new_contact']));

        // Proses penyimpanan ke database (misalnya)
        // Cek koneksi database dan simpan data ke tabel donation_centers
        $conn = mysqli_connect("localhost", "root", "", "donation_system");

        if ($conn) {
            $sql = "INSERT INTO donation_centers (signup_id, name, address, contact_info) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $signup_id, $new_name, $new_address, $new_contact);

            if ($stmt->execute()) {
                echo "<script>
        alert('Terima kasih. Data Anda telah kami terima. Proses Bisa Dilanjutkan Setelah Tempat Donasi Disetujui Admin!');
        window.location.href = 'donation.php'; // Redirect setelah alert
    </script>";
            } else {
                echo "Terjadi kesalahan saat menambahkan tempat donasi.";
            }
        } else {
            echo "Koneksi database gagal.";
        }
    } else {
        echo "Data tidak lengkap. Pastikan semua kolom diisi.";
    }
} else {
    echo "Anda harus login terlebih dahulu untuk memilih tempat donasi.";
}
?>