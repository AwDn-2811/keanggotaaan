<?php
// ========================================================================
// [1] LOGIKA PHP UTAMA & KEAMANAN
// ========================================================================
session_start();

// Memeriksa apakah pengguna sudah login, jika tidak, arahkan ke halaman login
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['user_name']; // Ganti $user_name menjadi $admin_name untuk konsistensi

// Koneksi ke database
$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

$error_message = ''; // Variabel untuk menampung pesan error

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $nik = $_POST['nik'];
    $nama_warga = $_POST['nama_warga'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat_ktp = $_POST['alamat_ktp'];
    $alamat = $_POST['alamat'];
    $desa_kelurahan = $_POST['desa_kelurahan'];
    $kecamatan = $_POST['kecamatan'];
    $kabupaten = $_POST['kabupaten'];
    $provinsi = $_POST['provinsi'];
    $negara = $_POST['negara'];
    $rt = $_POST['rt'];
    $rw = $_POST['rw'];
    $agama = $_POST['agama'];
    $pendidikan_terakhir = $_POST['pendidikan_terakhir'];
    $pekerjaan = $_POST['pekerjaan'];
    $status_perkawinan = $_POST['status_perkawinan'];
    $status_tinggal = $_POST['status_tinggal'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Query untuk menambahkan data warga ke dalam tabel
        $sql = "INSERT INTO data_warga (nik, nama_warga, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat_ktp, alamat, desa_kelurahan, kecamatan, kabupaten, provinsi, negara, rt, rw, agama, pendidikan_terakhir, pekerjaan, status_perkawinan, status_tinggal)
                 VALUES (:nik, :nama_warga, :tempat_lahir, :tanggal_lahir, :jenis_kelamin, :alamat_ktp, :alamat, :desa_kelurahan, :kecamatan, :kabupaten, :provinsi, :negara, :rt, :rw, :agama, :pendidikan_terakhir, :pekerjaan, :status_perkawinan, :status_tinggal)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nik' => $nik,
            ':nama_warga' => $nama_warga,
            ':tempat_lahir' => $tempat_lahir,
            ':tanggal_lahir' => $tanggal_lahir,
            ':jenis_kelamin' => $jenis_kelamin,
            ':alamat_ktp' => $alamat_ktp,
            ':alamat' => $alamat,
            ':desa_kelurahan' => $desa_kelurahan,
            ':kecamatan' => $kecamatan,
            ':kabupaten' => $kabupaten,
            ':provinsi' => $provinsi,
            ':negara' => $negara,
            ':rt' => $rt,
            ':rw' => $rw,
            ':agama' => $agama,
            ':pendidikan_terakhir' => $pendidikan_terakhir,
            ':pekerjaan' => $pekerjaan,
            ':status_perkawinan' => $status_perkawinan,
            ':status_tinggal' => $status_tinggal,
        ]);

        // Redirect setelah data berhasil ditambahkan
        header("Location: data_warga.php?status=success");
        exit();
    } catch (PDOException $e) {
        $error_message = "Error: Gagal menyimpan data. " . $e->getMessage();
    }
}

// ========================================================================
// [2] PENGATURAN TAMPILAN HALAMAN
// ========================================================================
$page_title = "Tambah Warga Baru";
$page_description = "Isi form di bawah untuk menambahkan data warga baru.";
$active_link = "data_warga.php"; // Ini file form tambah, tapi menu yang aktif adalah Data Warga
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin</title>

    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        body { background-color: #f5f6fa; }
        .layout-wrapper { min-height: 100vh; }

        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #111827; /* Warna latar sidebar gelap */
            color: #e5e7eb;
        }
        .sidebar .brand {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar .brand h4 { margin: 0; }
        .sidebar .nav-link { color: #e5e7eb; padding: .6rem 1.25rem; }
        /* Logika active link tetap ada, meskipun form ini bukan link utama */
        .sidebar .nav-link.active { background: #020617; font-weight: bold; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,.06); }

        .sidebar .nav-section-title {
            padding: .75rem 1.25rem .25rem;
            opacity: .6;
            text-transform: uppercase;
            font-size: .75rem;
        }

        .main-content {
            flex: 1;
            padding: 1.5rem;
        }

        /* Styling form agar lebih rapi di dalam card */
        .card-form .form-group {
            margin-bottom: 1rem;
        }
        .card-form h4 {
            margin-top: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e0e0e0;
            color: #007bff; /* Warna biru untuk judul bagian form */
        }
    </style>
</head>
<body>

<div class="d-flex layout-wrapper">

    <aside class="sidebar d-flex flex-column">
        <div class="brand">
            <h4>Panel Admin</h4>
            <small class="text-muted">Sistem Pendataan Warga</small>
        </div>

        <div class="flex-grow-1">

            <div class="nav-section-title">Menu Utama</div>
            <nav class="nav flex-column">
                <a class="nav-link <?= ($active_link == 'dashboard_admin.php') ? 'active' : '' ?>" href="dashboard_admin.php">🏠 Dasbor</a>

                <a class="nav-link <?= ($active_link == 'data_warga.php') ? 'active' : '' ?>" href="data_warga.php">👥 Data Warga</a>

                <a class="nav-link <?= ($active_link == 'data_kk.php') ? 'active' : '' ?>" href="data_kk.php">🧾 Data Kartu Keluarga</a>

                <a class="nav-link <?= ($active_link == 'data_mutasi.php') ? 'active' : '' ?>" href="data_mutasi.php">🔁 Data Mutasi</a>

                <a class="nav-link <?= ($active_link == 'data_user.php') ? 'active' : '' ?>" href="data_user.php">👤 User</a>
            </nav>

            <div class="nav-section-title">Lainnya</div>
            <nav class="nav flex-column">
                <a class="nav-link text-danger" href="logout.php">
                    🚪 Logout
                </a>
            </nav>
        </div>

        <div class="p-3 border-top border-secondary">
            <small>Login sebagai:<br><b><?= htmlspecialchars($admin_name) ?></b></small>
        </div>
    </aside>

    <main class="main-content">

        <h3 class="mb-3"><i class="bi bi-person-plus"></i> <?= $page_title ?></h3>
        <p class="text-muted"><?= $page_description ?></p>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <div class="card shadow-sm mb-5">
            <div class="card-body card-form">
                <form action="tambah_warga.php" method="POST">
                    
                    <h4>A. Data Pribadi</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nik">NIK</label>
                                <input type="text" class="form-control" name="nik" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_warga">Nama Warga</label>
                                <input type="text" class="form-control" name="nama_warga" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tempat_lahir">Tempat Lahir</label>
                                <input type="text" class="form-control" name="tempat_lahir" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tanggal_lahir">Tanggal Lahir</label>
                                <input type="date" class="form-control" name="tanggal_lahir" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="jenis_kelamin">Jenis Kelamin</label>
                                <select class="form-select" name="jenis_kelamin" required>
                                    <option value="" disabled selected>- pilih -</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <h4 class="mt-4">B. Data Alamat</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="alamat_ktp">Alamat KTP</label>
                                <textarea class="form-control" name="alamat_ktp" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="alamat">Alamat Tinggal Saat Ini</label>
                                <textarea class="form-control" name="alamat" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="desa_kelurahan">Desa/Kelurahan</label>
                                <input type="text" class="form-control" name="desa_kelurahan" required>
                            </div>
                            <div class="form-group">
                                <label for="kabupaten">Kabupaten/Kota</label>
                                <input type="text" class="form-control" name="kabupaten" required>
                            </div>
                            <div class="form-group">
                                <label for="rt">RT</label>
                                <input type="text" class="form-control" name="rt" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kecamatan">Kecamatan</label>
                                <input type="text" class="form-control" name="kecamatan" required>
                            </div>
                            <div class="form-group">
                                <label for="provinsi">Provinsi</label>
                                <input type="text" class="form-control" name="provinsi" required>
                            </div>
                            <div class="form-group">
                                <label for="rw">RW</label>
                                <input type="text" class="form-control" name="rw" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                             <div class="form-group">
                                <label for="negara">Negara</label>
                                <input type="text" class="form-control" name="negara" required value="Indonesia">
                            </div>
                        </div>
                    </div>


                    <h4 class="mt-4">C. Data Lain-lain</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="agama">Agama</label>
                                <select class="form-select" name="agama" required>
                                    <option value="" disabled selected>- pilih -</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen">Kristen</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="status_perkawinan">Status Perkawinan</label>
                                <select class="form-select" name="status_perkawinan" required>
                                    <option value="Belum Menikah">Belum Menikah</option>
                                    <option value="Menikah">Menikah</option>
                                    <option value="Duda/Janda">Duda/Janda</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="pendidikan_terakhir">Pendidikan Terakhir</label>
                                <select class="form-select" name="pendidikan_terakhir" required>
                                    <option value="" disabled selected>- pilih -</option>
                                    <option value="SD">SD</option>
                                    <option value="SMP">SMP</option>
                                    <option value="SMA">SMA</option>
                                    <option value="D3">D3</option>
                                    <option value="S1">S1</option>
                                    <option value="S2">S2</option>
                                    <option value="S3">S3</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="status_tinggal">Status Tinggal</label>
                                <select class="form-select" name="status_tinggal" required>
                                    <option value="Tinggal Bersama Keluarga">Tinggal Bersama Keluarga</option>
                                    <option value="Tinggal Mandiri">Tinggal Mandiri</option>
                                    <option value="Kos/Kontrak">Kos/Kontrak</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="pekerjaan">Pekerjaan</label>
                                <input type="text" class="form-control" name="pekerjaan" required>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Data Warga
                        </button>
                        <a href="data_warga.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>