<?php
session_start();

// Cek role admin
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
$info = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // Pastikan ada id warga di URL
    if (!isset($_GET['id'])) {
        header("Location: data_warga.php");
        exit();
    }

    $id_warga = (int) $_GET['id'];

    // Ambil data warga + nomor KK (kalau ada relasi kk_id)
    $sql = "
        SELECT w.*, kk.nomor_kk
        FROM data_warga AS w
        LEFT JOIN data_kk AS kk ON kk.id = w.kk_id
        WHERE w.id = :id
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_warga]);
    $warga = $stmt->fetch();

    if (!$warga) {
        header("Location: data_warga.php");
        exit();
    }

    // Default nilai form (saat GET)
    $nomor_kk       = $warga['nomor_kk'] ?? '';
    $nik_warga      = $warga['nik'];
    $nama_warga     = $warga['nama_warga'];
    $alamat_asal    = $warga['alamat'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ambil data dari form
        $nomor_kk       = trim($_POST['nomor_kk'] ?? '');
        $nik_warga      = trim($_POST['nik_warga'] ?? '');
        $nama_warga     = trim($_POST['nama_warga'] ?? ''); // hanya untuk tampilan
        $jenis_mutasi   = $_POST['jenis_mutasi'] ?? '';
        $alamat_asal    = trim($_POST['alamat_asal'] ?? '');
        $alamat_tujuan  = trim($_POST['alamat_tujuan'] ?? '');
        $tanggal_mutasi = $_POST['tanggal_mutasi'] ?? '';
        $keterangan     = trim($_POST['keterangan'] ?? '');

        // Validasi sederhana
        if (
            $nomor_kk === '' ||
            $nik_warga === '' ||
            $jenis_mutasi === '' ||
            $alamat_asal === '' ||
            $alamat_tujuan === '' ||
            $tanggal_mutasi === ''
        ) {
            $error = "Field bertanda * wajib diisi.";
        } else {
            // Pakai transaksi supaya rapi (kalau 1 step gagal bisa dibatalkan semua)
            $pdo->beginTransaction();
            try {
                // 1. Insert ke tabel data_mutasi
                $ins = $pdo->prepare("
                    INSERT INTO data_mutasi
                    (nomor_kk, nik_warga, jenis_mutasi, alamat_asal, alamat_tujuan, tanggal_mutasi, keterangan)
                    VALUES
                    (:nomor_kk, :nik_warga, :jenis_mutasi, :alamat_asal, :alamat_tujuan, :tanggal_mutasi, :keterangan)
                ");

                $ins->execute([
                    ':nomor_kk'       => $nomor_kk,
                    ':nik_warga'      => $nik_warga,
                    ':jenis_mutasi'   => $jenis_mutasi,
                    ':alamat_asal'    => $alamat_asal,
                    ':alamat_tujuan'  => $alamat_tujuan,
                    ':tanggal_mutasi' => $tanggal_mutasi,
                    ':keterangan'     => $keterangan,
                ]);

                // 2. Cek apakah warga ini kepala keluarga di data_kk
                $cek = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM data_kk 
                    WHERE id_kepala = :id_warga
                ");
                $cek->execute([':id_warga' => $id_warga]);
                $jumlah_kk = (int) $cek->fetchColumn();

                if ($jumlah_kk > 0) {
                    // Masih jadi kepala keluarga → TIDAK dihapus dari data_warga
                    // supaya tidak nabrak foreign key fk_kk_kepala
                    $pdo->commit();

                    // Tambah parameter info untuk ditampilkan di data_mutasi / data_warga
                    header("Location: data_mutasi.php?status=added&info=kepala");
                    exit();
                }

                // 3. Kalau bukan kepala keluarga → boleh hapus dari data_warga
                $del = $pdo->prepare("DELETE FROM data_warga WHERE id = :id_warga");
                $del->execute([':id_warga' => $id_warga]);

                $pdo->commit();

                header("Location: data_mutasi.php?status=added");
                exit();
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Error: " . $e->getMessage();
            }
        }
    }

} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mutasi Warga</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Sistem Pendataan Warga</a>
        <div class="navbar-nav ml-auto">
            <span class="nav-item nav-link text-white">Hai, <?= htmlspecialchars($user_name) ?></span>
            <a class="nav-item nav-link text-white" href="dashboard_admin.php">Dasbor</a>
            <a class="nav-item nav-link text-white" href="logout.php">Keluar</a>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar">
    <nav>
        <ul>
            <li><a href="dashboard_admin.php">Dasbor</a></li>
            <li><a href="data_warga.php">Data Warga</a></li>
            <li><a href="data_kk.php">Data Kartu Keluarga</a></li>
            <li><a href="data_mutasi.php" class="active">Data Mutasi</a></li>
            <li><a href="data_user.php">User</a></li>
        </ul>
    </nav>
</div>

<!-- Main content -->
<div class="main-content">
    <header>
        <h1>Mutasi Warga</h1>
        <p>Warga: <strong><?= htmlspecialchars($nama_warga) ?></strong> (NIK: <?= htmlspecialchars($nik_warga) ?>)</p>
    </header>

    <div class="content">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <h4>A. Data Warga</h4>

            <div class="form-group">
                <label>Nama Warga</label>
                <input type="text" name="nama_warga" class="form-control"
                       value="<?= htmlspecialchars($nama_warga) ?>" readonly>
            </div>

            <div class="form-group">
                <label>NIK Warga *</label>
                <input type="text" name="nik_warga" class="form-control"
                       value="<?= htmlspecialchars($nik_warga) ?>" readonly>
            </div>

            <div class="form-group">
                <label>Nomor Kartu Keluarga *</label>
                <input type="text" name="nomor_kk" class="form-control"
                       value="<?= htmlspecialchars($nomor_kk) ?>" required>
                <small class="form-text text-muted">
                    Jika belum ada relasi KK, isikan secara manual.
                </small>
            </div>

            <h4>B. Data Mutasi</h4>
            <div class="form-group">
                <label>Jenis Mutasi *</label>
                <select name="jenis_mutasi" class="form-control" required>
                    <option value="">- pilih -</option>
                    <option value="Pindah Alamat">Pindah Alamat</option>
                    <option value="Pindah Status">Pindah Status</option>
                    <option value="Pindah Tempat Tinggal">Pindah Tempat Tinggal</option>
                </select>
            </div>

            <div class="form-group">
                <label>Alamat Asal *</label>
                <textarea name="alamat_asal" class="form-control" rows="2" required><?= htmlspecialchars($alamat_asal) ?></textarea>
            </div>

            <div class="form-group">
                <label>Alamat Tujuan *</label>
                <textarea name="alamat_tujuan" class="form-control" rows="2" required></textarea>
            </div>

            <div class="form-group">
                <label>Tanggal Mutasi *</label>
                <input type="date" name="tanggal_mutasi" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Keterangan (opsional)</label>
                <textarea name="keterangan" class="form-control" rows="2"></textarea>
            </div>

            <button type="submit" class="btn btn-success">Simpan Mutasi</button>
            <a href="data_warga.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
