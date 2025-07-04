<?php
session_start();
$loggedIn = isset($_SESSION['username']);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "donation_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}


// Get donation status for logged-in user
$donation_status = null;
if ($loggedIn) {
  $username = $_SESSION['username'];
  $query = "SELECT donation_centers.status
              FROM donation_centers
              JOIN signup ON donation_centers.signup_id = signup.id
              WHERE signup.username = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $donation_status = $row['status'];
  }
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
    }

    header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 1000;
      background-color: #303054;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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

    .home-sec {
      color: #303054;
      text-align: center;
      padding: 40px 0;
      background: linear-gradient(to right, #f5f7fa, #c3cfe2);
    }

    .btn1 {
      background-color: #303054;
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      border: none;
      transition: background-color 0.3s;
    }

    .btn1:hover {
      background-color: #464666;
      color: white;
    }

    .list-group-item {
      margin-bottom: 10px;
      border-radius: 5px !important;
    }
  </style>
</head>

<body>

  <!-- Header -->
  <header>
    <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container">
        <a class="navbar-brand" href="index.php">Donasi Panti Asuhan</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item">
              <a class="nav-link" href="index.php">Beranda</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="index.php#about">Tentang</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="index.php#donation">Donasi</a>
            </li>
            <?php if ($loggedIn): ?>
              <li class="nav-item">
                <a class="nav-link" href="#" data-toggle="modal" data-target="#donationStatusModal">
                  Status Pengajuan Tempat Donasi
                </a>
              </li>
            <?php endif; ?>
            <li class="nav-item">
              <a class="nav-link" href="index.php#contact">Kontak</a>
            </li>
            <?php if ($loggedIn): ?>
              <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
              </li>
            <?php else: ?>
              <li class="nav-item">
                <a class="nav-link" href="login.html">Login</a>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>
  </header>

  <!-- Home Section -->
  <section class="home-sec" id="home">
    <div class="container">
      <h1>Selamat Datang di Sistem Penyaluran Donasi</h1>
      <p>Mari bersama membangun harapan untuk masa depan yang lebih baik.</p>
    </div>
  </section>

  <!-- Main Content -->
  <section class="container my-5">
    <?php if ($loggedIn): ?>
      <h2 class="text-center mb-4">Tempat Donasi</h2>

      <!-- Existing Donation Centers -->
      <ul class="list-group mb-4">
        <li class="list-group-item">
          <strong>Panti Asuhan Harapan Bangsa</strong><br>
          Alamat: Jl. Kebangkitan No. 45, Jakarta<br>
          Kontak: (021) 555-1234
          <button class="btn1 float-right"
            onclick="goToJenisDonasi('Panti Asuhan Harapan Bangsa', 'Jl. Kebangkitan No. 45, Jakarta', '(021) 555-1234')">Pilih</button>
        </li>
        <li class="list-group-item">
          <strong>Panti Asuhan Cinta Kasih</strong><br>
          Alamat: Jl. Damai No. 12, Bandung<br>
          Kontak: (022) 666-5678
          <button class="btn1 float-right"
            onclick="goToJenisDonasi('Panti Asuhan Cinta Kasih', 'Jl. Damai No. 12, Bandung', '(022) 666-5678')">Pilih</button>
        </li>
        <li class="list-group-item">
          <strong>Panti Asuhan Bintang Kecil</strong><br>
          Alamat: Jl. Mentari No. 8, Yogyakarta<br>
          Kontak: (0274) 889-1111
          <button class="btn1 float-right"
            onclick="goToJenisDonasi('Panti Asuhan Bintang Kecil', 'Jl. Mentari No. 8, Yogyakarta', '(0274) 889-1111')">Pilih</button>
        </li>
        <li class="list-group-item">
          <strong>Panti Asuhan Pelita Hati</strong><br>
          Alamat: Jl. Cinta No. 25, Surabaya<br>
          Kontak: (031) 123-4567
          <button class="btn1 float-right"
            onclick="goToJenisDonasi('Panti Asuhan Pelita Hati', 'Jl. Cinta No. 25, Surabaya', '(031) 123-4567')">Pilih</button>
        </li>
        <li class="list-group-item">
          <strong>Panti Asuhan Kasih Ibu</strong><br>
          Alamat: Jl. Bahagia No. 32, Medan<br>
          Kontak: (061) 777-8900
          <button class="btn1 float-right"
            onclick="goToJenisDonasi('Panti Asuhan Harapan Bangsa', 'Jl. Kebangkitan No. 45, Jakarta', '(021) 555-1234')">Pilih</button>
        </li>
        <?php
        $sql = "SELECT * FROM donation_centers WHERE status = 'Approved'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo '<li class="list-group-item">
                            <strong>' . htmlspecialchars($row['name']) . '</strong><br>
                            Alamat: ' . htmlspecialchars($row['address']) . '<br>
                            Kontak: ' . htmlspecialchars($row['contact_info']) . '
                            <button class="btn1 float-right" onclick="goToJenisDonasi(\'' .
              htmlspecialchars($row['name']) . '\', \'' .
              htmlspecialchars($row['address']) . '\', \'' .
              htmlspecialchars($row['contact_info']) . '\')">Pilih</button>
                          </li>';
          }
        }
        ?>
      </ul>

      <!-- Add New Donation Center Form -->
      <div class="card mt-4">
        <div class="card-header">
          <h4 class="mb-0">Tambah Tempat Donasi Baru</h4>
        </div>
        <div class="card-body">
          <form id="newDonationCenterForm" action="process_donation_center.php" method="POST">
            <div class="form-group">
              <label for="new_name">Nama Panti Asuhan:</label>
              <input type="text" class="form-control" id="new_name" name="new_name" required>
            </div>
            <div class="form-group">
              <label for="new_address">Alamat:</label>
              <input type="text" class="form-control" id="new_address" name="new_address" required>
            </div>
            <div class="form-group">
              <label for="new_contact">Kontak Info:</label>
              <input type="text" class="form-control" id="new_contact" name="new_contact" required>
            </div>
            <button type="submit" class="btn1">Tambahkan</button>
          </form>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-info text-center">
        Silakan <a href="login.html">login</a> untuk memilih tempat donasi.
      </div>
    <?php endif; ?>
  </section>

  <!-- Status Modal -->
  <div class="modal fade" id="donationStatusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Status Pengajuan Tempat Donasi Baru</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <?php if ($loggedIn): ?>
            <?php if ($donation_status): ?>
              <p>Status pengajuan Anda: <strong><?php echo htmlspecialchars($donation_status); ?></strong></p>
              <?php if ($donation_status == 'Approved'): ?>
                <div class="alert alert-success">
                  Tempat donasi Anda sudah disetujui dan bisa dipilih.
                </div>
              <?php elseif ($donation_status == 'Pending'): ?>
                <div class="alert alert-warning">
                  Pengajuan Anda masih dalam proses review.
                </div>
                <?php elseif ($donation_status == 'Rejected'): ?>
                <div class="alert alert-danger">
                  Maaf, pengajuan Anda ditolak. Silakan ajukan kembali.
                </div>
                <?php else: ?>
                <div class="alert alert-success">
                  belum ada Pengajuan.
                </div>
              <?php endif; ?>
            <?php else: ?>
              <p>Anda belum mengajukan tempat donasi baru.</p>
            <?php endif; ?>
          <?php else: ?>
            <p>Silakan login terlebih dahulu untuk melihat status pengajuan.</p>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function goToJenisDonasi(name, address, contact) {
      const form = document.createElement("form");
      form.method = "POST";
      form.action = "process_donation_center_approval.php";

      const nameInput = document.createElement("input");
      nameInput.type = "hidden";
      nameInput.name = "donation_center_name";
      nameInput.value = name;

      const addressInput = document.createElement("input");
      addressInput.type = "hidden";
      addressInput.name = "donation_center_address";
      addressInput.value = address;

      const contactInput = document.createElement("input");
      contactInput.type = "hidden";
      contactInput.name = "donation_center_contact";
      contactInput.value = contact;

      form.appendChild(nameInput);
      form.appendChild(addressInput);
      form.appendChild(contactInput);

      document.body.appendChild(form);
      form.submit();
    }

  </script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $conn->close(); ?>