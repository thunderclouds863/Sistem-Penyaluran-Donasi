<?php
session_start();

// Redirect if the user is not logged in
if (!isset($_SESSION['username'])) {
  header("Location: login.html"); // Change to your login page
  exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Penyaluran Donasi Panti Asuhan</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
  <style>
    body {
      padding-top: 50px;
      /* Sesuaikan dengan tinggi navbar */
    }

    header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 1000;
      /* Pastikan tetap berada di atas konten lainnya */
      background-color: #303054;
      /* Pastikan warna latar belakang terlihat */
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      /* Tambahkan bayangan untuk efek */
    }
    header .navbar-brand,
    header .navbar-nav .nav-link {
      color: #ffffff !important;
      font-weight: 500;
      transition: color 0.3s;
    }

    h2 {
      font-family: 'Poppins', sans-serif;
      scroll-behavior: smooth;
      margin: 0;
      background-color: #f3f4f6;
    }

    header {
      background-color: #303054;
      box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    }

    header .navbar-brand {
      font-size: 22px;
      font-weight: 600;
      color: #fff;
    }

    header .navbar-nav .nav-link {
      color: #ffffff !important;
      font-weight: 500;
      transition: color 0.3s;
    }

    header .navbar-brand:hover,
    header .navbar-nav .nav-link:hover {
      color: #dcdcdc !important;
    }

    .home-sec {
      color: #303054;
      text-align: center;
      padding: 40px 0;
      background: linear-gradient(to right, #f5f7fa, #c3cfe2);
    }

    .home-sec h1 {
      font-size: 2.5rem;
      font-weight: 600;
    }

    .home-sec p {
      font-size: 1.3rem;
      margin-bottom: 20px;
    }

    .form-background {
      padding: 30px 0;
      background-color: #f3f4f6;
    }

    .registration-form {
      max-width: 600px;
      margin: auto;
      background-color: #ffffff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }

    .registration-form h2 {
      color: #303054;
      font-weight: bold;
      margin-bottom: 20px;
    }

    .registration-form label {
      font-weight: bold;
      margin-top: 10px;
      color: #555;
    }

    .registration-form input,
    .registration-form select,
    .registration-form textarea,
    .registration-form button {
      width: 100%;
      padding: 12px;
      margin: 8px 0;
      border-radius: 5px;
      border: 1px solid #ddd;
      transition: border-color 0.3s ease-in-out;
    }

    .registration-form button {
      background-color: #303054;
      color: #fff;
      border: none;
      font-size: 1.1rem;
      font-weight: bold;
      transition: background-color 0.3s;
    }

    .registration-form button:hover {
      background-color: #4a4a75;
    }

    footer {
      background-color: #303054;
      color: #e6edff;
      padding: 50px 0;
    }

    footer h4 {
      margin-bottom: 20px;
    }

    footer p a {
      text-decoration: none;
      color: #e6edff;
    }

    footer p a:hover {
      color: #e6edff;
      opacity: 0.7;
    }
  </style>
</head>

<body>
  <!-- Header -->
  <header>
  <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
      <a class="navbar-brand" href="index.php">Donasi Panti Asuhan</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link" href="index.php">Beranda</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="index.php/#about">Tentang</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="index.php/#donation">Donasi</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="formpengajuan.php">Pengajuan Bantuan</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="index.php/#contact">Kontak</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>


  <!-- Home Section -->
  <section class="home-sec" id="home">
    <h1>Selamat Datang di Sistem Penyaluran Donasi</h1>
    <p>Mari bersama membangun harapan untuk masa depan yang lebih baik.</p>
  </section>

  <!-- Formulir Pengajuan Bantuan -->
  <section class="form-background" id="register">
    <div class="registration-form">
      <h2 class="text-center">Formulir Pengajuan Bantuan</h2>
      <form action="pengajuan.php" method="POST" enctype="multipart/form-data">
        <label for="assistanceType">Jenis Bantuan:</label>
        <select id="assistanceType" name="assistanceType" required>
          <option value="">Pilih Jenis Bantuan</option>
          <option value="Bantuan Barang dan Pangan">Bantuan Barang dan Pangan</option>
          <option value="Bantuan Dana">Bantuan Dana</option>
        </select>

        <label for="description">Deskripsi Permohonan:</label>
        <textarea id="description" name="description" placeholder="Jelaskan bantuan yang Anda butuhkan"
          required></textarea>

        <label for="supportingDocuments">Unggah Dokumen Pendukung:</label>
        <input type="file" id="supportingDocuments" name="supportingDocuments" required>

        <button type="submit">Ajukan Bantuan</button>
        <p class="note">Kami akan menghubungi Anda melalui email atau nomor telepon yang Anda berikan.</p>
      </form>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="container">
      <h4>Donasi Panti Asuhan</h4>
      <p>&copy; 2024. Semua Hak Dilindungi. <a href="#">Kebijakan Privasi</a> | <a href="#">Syarat dan Ketentuan</a></p>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>