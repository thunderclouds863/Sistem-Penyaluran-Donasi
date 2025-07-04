<?php
session_start();
$isLoggedIn = isset($_SESSION['username']);

// Database connection
$host = 'localhost';
$dbname = 'donation_system';
$username = 'root';
$password = '';
try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
  die();
}

// Fetching data for the modal
$query = "
    SELECT 'Financial Donation' AS type, CONCAT('Amount: ', amount) AS description, status, id AS updated_at
    FROM financial_donations
    UNION ALL
    SELECT 'Item Donation', CONCAT('Item: ', item_type), status, created_at AS updated_at
    FROM item_donations
    UNION ALL
    SELECT 'Donation Request', CONCAT('Assistance: ', assistance_type), status, created_at AS updated_at
    FROM donation_requests
    ORDER BY updated_at DESC;
";






$stmt = $pdo->prepare($query);
$stmt->execute();
$donationStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Penyaluran Donasi Panti Asuhan</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="shortcut icon" href="img/don/salary.png" type="image/x-icon">
  <style>
    /* Custom CSS for modal and scrollbar */

    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700&display=swap');

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
      scroll-behavior: smooth;
    }

    body {
      padding-top: 70px;
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


    /* Heading */
    .heading h2 {
      font-size: 28px;
      font-weight: 500;
      border-left: 8px solid #303054;
      padding: 5px 15px;
      margin-bottom: 15px;
    }

    .heading p {
      font-size: 18px;
    }

    .heading p span {
      font-weight: 600;
      color: #303054;
    }

    /* Buttons */
    .btn1 {
      font-size: 1rem;
      font-weight: 400;
      background-color: #303054;
      color: #fff;
      padding: 10px 20px;
      border-radius: 5px;
      text-decoration: none !important;
      transition: all 0.5s;
    }

    .btn1:hover {
      background-color: #e6edff;
      color: #303054;
    }

    /* Header */
    header {
      background-color: #303054;
    }

    header .navbar-brand {
      font-size: 22px;
      font-weight: 600;
      color: #0000;
    }

    header .navbar-brand,
    header .navbar-nav .nav-link {
      color: #ffffff !important;
      font-weight: 500;
      transition: color 0.3s;
    }

    header .navbar-brand:hover,
    header .navbar-nav .nav-link:hover {
      color: #dcdcdc !important;
    }

    .dropdown-menu {
      min-width: 200px;
      /* Adjust width as necessary */
    }

    .dropdown-header {
      font-weight: bold;
      color: #303054;
    }

    .dropdown-item {
      color: #303054;
    }

    .dropdown-item:hover {
      background-color: #f1f1f1;
      /* Change background color on hover */
    }

    nav {
      padding: 8px 0 !important;
      position: fixed;
    }

    .cont-sec {
      padding: 8px;
      background-color: #e6edff;
      width: 100%;
    }

    .cont-sec .contact-cont {
      display: flex;
      flex-wrap: wrap;
    }

    .cont-sec p {
      margin: 0;
    }

    .cont-sec p a {
      text-decoration: none;
      color: #303054;
    }

    .cont-sec .social {
      text-align: right;
    }

    .cont-sec .social a img {
      width: 20px;
      margin-left: 15px;
    }

    .home-sec {
      padding: 100px 0;
    }

    h1,
    h2,
    p {
      color: black;
    }

    .buttons .btn1 {
      background-color: #ff6b6b;
      color: #ffffff;
      padding: 10px 20px;
      border-radius: 5px;
      text-decoration: none;
      transition: background-color 0.3s;
    }

    .buttons .btn1:hover {
      background-color: #ff8e8e;
    }

    .home-sec .home-content {
      align-items: center;
    }

    .home-sec .home-content h1 {
      font-size: 2.3rem;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .home-sec .home-content h2 span {
      color: #14e20d;
    }

    .home-sec .home-content h2 {
      font-size: 1.8rem;
      font-weight: 600;
      margin-bottom: 30px;
    }

    .home-sec .home-content p {
      font-size: 1.3rem;
      margin-bottom: 30px;
    }

    .home-sec .img-sec {
      display: flex;
      justify-content: center;
    }

    /* About section */
    .about-sec {
      padding: 150px 0;
      background-color: #e6edff;
    }

    .about-sec img {
      width: 350px;
      border: 5px solid #303054;
      border-radius: 5px;
    }

    .about-sec .about-txt p {
      font-size: 18px;
    }

    /* Donation Section */
    .don-sec {
      background-color: #e6edff;
      padding: 150px 0;
    }

    .don-sec .don-box {
      margin: 50px 0;
      background-color: #fff;
      padding: 50px;
      text-align: center;
      border-radius: 5px;
      box-shadow: 0 0 15px #b4afaf;
      transition: all 1s;
    }

    .don-sec .don-box img {
      width: 70px;
      transition: all 1s;
    }

    .don-sec .don-box h3 {
      margin: 15px 0 10px;
    }

    .don-sec .don-box p {
      margin-bottom: 25px;
    }

    .don-sec .don-box:hover img {
      transform: scale(1.1);
    }

    /* Missions */
    .mission {
      padding: 150px 0;
    }

    /* Gallery */
    .gallery-sec {
      position: relative;
      padding: 50px 0;
    }

    .gallery-sec .image-container {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      justify-content: center;
      padding: 10px;
    }

    .gallery-sec .image-container .image {
      height: 200px;
      width: 300px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
      cursor: pointer;
      overflow: hidden;
    }

    .gallery-sec .image-container .image img {
      height: 100%;
      width: 100%;
      border: 5px solid #d4d4d4;
      object-fit: cover;
      transition: 0.2s linear;
    }

    .gallery-sec .image-container .image:hover img {
      transform: scale(1.1);
    }

    .gallery-sec .pop-image {
      position: fixed;
      left: 0;
      top: 0;
      background: rgba(0, 0, 0, 0.8);
      height: 100%;
      width: 100%;
      z-index: 100;
      display: none;
    }

    .gallery-sec .pop-image span {
      position: absolute;
      top: 120px;
      right: 50px;
      font-size: 60px;
      font-weight: bolder;
      color: #ffffff;
      cursor: pointer;
      z-index: 100;
    }

    .gallery-sec .pop-image img {
      position: absolute;
      top: 55%;
      left: 50%;
      transform: translate(-50%, -50%);
      border: 3px solid #ffffff;
      width: 750px;
      object-fit: cover;
    }

    .row {
      display: -ms-flexbox;
      display: flex;
      -ms-flex-wrap: wrap;
      flex-wrap: wrap;
      margin-right: -15px;
      margin-left: -15px;
      justify-content: space-around;
    }

    /* Contact Us */
    .contact-section {
      padding: 150px 0 80px;
    }

    .contact-section .contact-form .form-group {
      margin-bottom: 20px;
    }

    .contact-section .contact-form .form-control {
      height: 50px;
      border-radius: 5px;
    }

    .contact-section .contact-form textarea.form-control {
      height: 122px;
      margin-bottom: 10px;
      resize: none;
    }

    .contact-section .contact-form .form-control:focus {
      box-shadow: none;
    }

    /* Footer */
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
      text-decoration: none;
      color: #e6edff;
      opacity: 0.7;
    }

    .dropdown-menu {
      background-color: #f8f9fa;
      /* Warna latar belakang dropdown */
      border: 1px solid #ddd;
      /* Border dropdown */
      border-radius: 5px;
      /* Sudut melengkung */
    }

    .dropdown-item {
      padding: 10px 20px;
      /* Padding item dropdown */
      transition: background-color 0.3s;
      /* Efek transisi */
    }

    .dropdown-item:hover {
      background-color: #007bff;
      /* Warna latar belakang saat hover */
      color: white;
      /* Warna teks saat hover */
    }

    .dropdown-item i {
      margin-right: 8px;
      /* Jarak antara ikon dan teks */
    }

    .modal-content {
      max-height: 70vh;
      overflow-y: scroll;
    }

    .modal-body {
      max-height: 50vh;
      overflow-y: auto;
    }

    .navbar-nav .nav-item {
      margin-left: 15px;
    }
  </style>
</head>

<body>
  <header>
    <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container">
        <a class="navbar-brand" href="#">Donasi Panti Asuhan</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
          aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item">
              <a class="nav-link" href="#home">Beranda</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#about">Tentang</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#donation">Donasi</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="formpengajuan.php">Pengajuan Bantuan</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#contact">Kontak</a>
            </li>
            <!-- Riwayat Button in Navbar -->
            <?php if ($isLoggedIn): ?>
              <li class="nav-item">
                <a class="nav-link" href="#" data-toggle="modal" data-target="#riwayatModal">
                  Riwayat
                </a>
              </li>
            <?php endif; ?>
            <!-- Show username and logout dropdown if logged in -->
            <?php if ($isLoggedIn): ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false">
                  <i class="fas fa-user"></i> <?= $_SESSION['username']; ?>
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                  <a class="dropdown-item" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                  </a>
                </div>
              </li>
            <?php else: ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false">
                  <i class="fas fa-user"></i> Login
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                  <a class="dropdown-item" href="login_admin.html">
                    <i class="fas fa-user-shield"></i> Admin
                  </a>
                  <a class="dropdown-item" href="login.html">
                    <i class="fas fa-user-graduate"></i> Mahasiswa
                  </a>
                </div>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>
  </header>

  <!-- Riwayat Modal -->
<div class="modal fade" id="riwayatModal" tabindex="-1" role="dialog" aria-labelledby="riwayatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="riwayatModalLabel">Riwayat Donasi dan Pengajuan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h4>Status Donasi:</h4>
                <ul class="list-group">
                    <?php foreach ($donationStatus as $status): ?>
                        <li class="list-group-item">
                            <strong><?= $status['type']; ?>:</strong> <?= $status['description']; ?>
                            <span class="badge badge-info"><?= $status['status']; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


  <!-- Home Section -->
  <section class="home-sec" id="home">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 col-md-12 home-content">
          <h1>Selamat Datang di <span>Donasi Panti Asuhan</span></h1>
          <h2>Bantu Kami Memberikan Kebahagiaan untuk Anak-Anak</h2>
          <p>Dengan donasi Anda, kami bisa memberikan kebutuhan yang lebih baik untuk anak-anak di panti asuhan.</p>
          <div class="buttons">
            <a href="donation.php" class="btn1">Beri Donasi Sekarang</a>
          </div>
        </div>
        <div class="col-lg-6 col-md-12 img-sec">
          <img src="https://image.freepik.com/free-vector/volunteers-packing-donation-boxes_74855-5299.jpg"
            alt="Anak Panti Asuhan" class="img-fluid">
        </div>
      </div>
    </div>
  </section>

    <!-- About Section -->
    <section class="about-sec" id="about">
    <div class="container">
      <div class="heading">
        <h2>Tentang Kami</h2>
        <p>Kami adalah sebuah organisasi yang berfokus pada pemberian donasi kepada panti asuhan untuk meningkatkan
          kualitas hidup anak-anak yang membutuhkan.</p>
      </div>
      <div class="row">
        <div class="col-md-6">
          <img src="https://th.bing.com/th/id/OIP.hSl1b7JeNaIUJScNKGxG3QHaE7?rs=1&pid=ImgDetMain" alt="Tentang Kami"
            class="img-fluid">
        </div>
        <div class="col-md-6 about-txt">
          <h3>Visi</h3>
          <p>Meningkatkan kualitas hidup anak-anak di panti asuhan melalui bantuan dan donasi dari masyarakat.</p>
          <h3>Misi</h3>
          <p>Kami berkomitmen untuk menyalurkan donasi dengan transparan dan akuntabel agar setiap donasi sampai kepada
            yang membutuhkan.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Donation Section -->
  <section class="don-sec" id="donation">
    <div class="container">
      <div class="heading">
        <h2>Bagaimana Anda Bisa Membantu</h2>
        <p>Kami menerima berbagai jenis donasi, baik berupa uang, barang, maupun jasa.</p>
      </div>
      <div class="row">
        <div class="col-lg-4 col-md-6 don-box">
          <img src="2.png" alt="Donasi Uang">
          <h3>Donasi Finansial</h3>
          <p>Bantuan dalam bentuk finansial akan digunakan untuk memenuhi kebutuhan sehari-hari anak-anak.</p>
        </div>
        <div class="col-lg-4 col-md-6 don-box">
          <img src="1.png" alt="Donasi Barang">
          <h3>Donasi Barang</h3>
          <p>Kami menerima berbagai barang kebutuhan sehari-hari seperti pakaian, makanan, dan mainan.</p>
        </div>
      </div>
    </div>
  </section>

<!-- Contact Section -->
<section class="contact-section" id="contact">
  <div class="container">
    <div class="heading">
      <h2>Kontak Kami</h2>
      <p>Jika Anda memiliki pertanyaan atau ingin berkontribusi, jangan ragu untuk menghubungi kami.</p>
    </div>
    <div class="contact-form">
      <form method="POST" action="submit_contact.php">
        <div class="form-group">
          <input type="text" name="full_name" class="form-control" placeholder="Nama" required>
        </div>
        <div class="form-group">
          <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>
        <div class="form-group">
          <textarea name="message" class="form-control" placeholder="Pesan" required></textarea>
        </div>
        <button type="submit" class="btn1">Kirim</button>
      </form>
    </div>
  </div>
</section>

  <!-- Footer -->
  <footer>
    <div class="container">
      <h4>Donasi Panti Asuhan</h4>
      <a>&copy; 2024. Semua Hak Dilindungi. Kebijakan Privasi | Syarat dan Ketentuan</a>
    </div>
  </footer>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>