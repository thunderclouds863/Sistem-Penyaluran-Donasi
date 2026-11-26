# Sistem Informasi Penyaluran Donasi Panti Asuhan

Aplikasi web berbasis **PHP 8.2 + Apache + MariaDB** untuk pengumpulan dan penyaluran donasi (dana via QRIS maupun barang drop-off) secara transparan, terstruktur, dan akuntabel.

## Fitur Utama (MVP)
- **Halaman Publik & Transparansi** (`index.php`): Informasi visi/misi, metrik penyaluran langsung dari database, alur donasi, dan formulir kontak.
- **Autentikasi & Pemulihan Akun**: Registrasi mahasiswa/donatur (`signup.html`), verifikasi kode (`verify.html`), login terpisah untuk Donatur (`login.html`) dan Admin (`login_admin.html`), serta reset password aman berbasis token sekali pakai (`forgot_password.php`, `reset_password.php`).
- **Alur Donasi 3 Langkah**:
  1. Pilih Panti/Posko terverifikasi atau ajukan posko baru (`donation.php`).
  2. Pilih jenis bantuan (`jenis_donasi.html`).
  3. Donasi dana via QRIS (`qris.php`) atau donasi barang drop-off (`barang.php`).
- **Pengajuan Bantuan** (`formpengajuan.php`): Pengajuan permohonan bantuan dana/logistik beserta unggahan dokumen pendukung (PDF/JPG/PNG, maks. 5 MB).
- **Riwayat Pribadi Donatur**: Modal riwayat donasi & pengajuan terfilter per akun pengguna.
- **Admin Operations Console** (`admin_dashboard.php`): KPI real-time, persetujuan/penolakan posko & pengajuan bantuan dengan alasan penolakan, konfirmasi donasi dana & barang, tautan dokumen pendukung, dan kotak masuk pesan kontak.

## Menjalankan Aplikasi (Docker — Direkomendasikan)

Prasyarat: **Docker** & **Docker Compose**.

```bash
docker compose up --build
```

Atau langsung dengan Docker:

```bash
docker build -t penyaluran-donasi .
docker run -p 10000:10000 -e PORT=10000 penyaluran-donasi
```

Container akan otomatis menjalankan MariaDB, mengimpor skema (`deploy/schema.sql`) dan data contoh (`deploy/demo_seed.sql`), lalu menyajikan aplikasi di:

- **URL**: `http://localhost:10000`

### Akun Demo Bawaan
| Role | Username | Password | Halaman Login |
| :--- | :--- | :--- | :--- |
| Donatur / Mahasiswa | `donatur1` | `demo123` | `/login.html` |
| Pengurus Posko | `posko1` | `demo123` | `/login.html` |
| Administrator | `admin` | `1234` | `/login_admin.html` |

## Konfigurasi Database (Environment Variables)
Default koneksi (sesuai container):
- `DB_HOST=localhost`
- `DB_NAME=donation_system`
- `DB_USER=root`
- `DB_PASS=` (kosong)
