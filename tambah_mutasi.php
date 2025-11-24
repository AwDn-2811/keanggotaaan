<?php
session_start();

// Memeriksa jika pengguna sudah login dan memiliki peran admin
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['user_name'];

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';
$error = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ambil data dari warga
    $warga_stmt = $pdo->query("SELECT id, nik, nama_warga FROM data_warga ORDER BY nama_warga ASC");
    $warga_list = $warga_stmt->fetchAll();

    // Proses form saat POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ambil data dari form
        $nomor_kk       = trim($_POST['nomor_kk']);
        $nik_warga      = trim($_POST['nik_warga']);
        $jenis_mutasi   = $_POST['jenis_mutasi'];
        $alamat_asal    = $_POST['alamat_asal'];
        $alamat_tujuan  = $_POST['alamat_tujuan'];
        $tanggal_mutasi = $_POST['tanggal_mutasi'];
        $keterangan     = $_POST['keterangan'];

        // Validasi input
        if (empty($nomor_kk) || empty($nik_warga) || empty($jenis_mutasi) || empty($alamat_asal) || empty($alamat_tujuan) || empty($tanggal_mutasi)) {
            $error = 'Semua kolom wajib diisi!';
        } else {
            // Menyimpan data mutasi ke database
            $sql = "INSERT INTO data_mutasi (nomor_kk, nik_warga, jenis_mutasi, alamat_asal, alamat_tujuan, tanggal_mutasi, keterangan)
                    VALUES (:nomor_kk, :nik_warga, :jenis_mutasi, :alamat_asal, :alamat_tujuan, :tanggal_mutasi, :keterangan)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nomor_kk' => $nomor_kk,
                ':nik_warga' => $nik_warga,
                ':jenis_mutasi' => $jenis_mutasi,
                ':alamat_asal' => $alamat_asal,
                ':alamat_tujuan' => $alamat_tujuan,
                ':tanggal_mutasi' => $tanggal_mutasi,
                ':keterangan' => $keterangan
            ]);

            header("Location: data_mutasi.php?status=added");
            exit();
        }
    }

} catch (PDOException $e) {
    $error = 'Error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Mutasi</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <div class="navbar-nav ml-auto">
                <span class="nav-item nav-link text-white">Hai, <?= htmlspecialchars($user_name) ?></span>
                <a class="nav-item nav-link text-white" href="dashboard_admin.php">Dasbor</a>
                <a class="nav-item nav-link text-white" href="logout.php">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <header>
            <h1>Tambah Data Mutasi</h1>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nomor_kk">Nomor KK</label>
                <input type="text" name="nomor_kk" class="form-control" maxlength="16" required>
            </div>
            <div class="form-group">
                <label for="nik_warga">NIK Warga</label>
                <input type="text" name="nik_warga" class="form-control" maxlength="20" required>
            </div>
            <div class="form-group">
                <label for="jenis_mutasi">Jenis Mutasi</label>
                <select name="jenis_mutasi" class="form-control" required>
                    <option value="Pindah Alamat">Pindah Alamat</option>
                    <option value="Pindah Status">Pindah Status</option>
                    <option value="Pindah Tempat Tinggal">Pindah Tempat Tinggal</option>
                </select>
            </div>
            <div class="form-group">
                <label for="alamat_asal">Alamat Asal</label>
                <textarea name="alamat_asal" class="form-control" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="alamat_tujuan">Alamat Tujuan</label>
                <textarea name="alamat_tujuan" class="form-control" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="tanggal_mutasi">Tanggal Mutasi</label>
                <input type="date" name="tanggal_mutasi" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="keterangan">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="data_mutasi.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>
