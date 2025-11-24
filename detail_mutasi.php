<?php
session_start();

// Cek apakah user sudah login dan peran admin
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
$mutasi = null;

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: data_mutasi.php");
    exit();
}

$id_mutasi = (int) $_GET['id'];

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

    // Ambil data mutasi berdasarkan id
    $sql_mutasi = "
        SELECT * 
        FROM data_mutasi
        WHERE id = :id_mutasi
        LIMIT 1
    ";
    $stmt_mutasi = $pdo->prepare($sql_mutasi);
    $stmt_mutasi->execute([':id_mutasi' => $id_mutasi]);
    $mutasi = $stmt_mutasi->fetch();

    if (!$mutasi) {
        echo "Data mutasi tidak ditemukan";
        header("Location: data_mutasi.php");
        exit();
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
    <title>Detail Mutasi Warga</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <div class="navbar-nav ml-auto">
            <span class="nav-item nav-link text-white">Hai, <?= htmlspecialchars($user_name) ?></span>
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
        <h1>Detail Mutasi Warga</h1>
        <p>Mutasi ID: <strong><?= htmlspecialchars($mutasi['id']) ?></strong></p>
    </header>

    <div class="content">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- A. Data Pribadi -->
        <h4>A. Data Pribadi</h4>
        <table class="table table-bordered">
            <tr>
                <th style="width:200px;">NIK</th>
                <td><?= isset($mutasi['nik_warga']) ? htmlspecialchars($mutasi['nik_warga']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Nama Mutasi</th>
                <td><?= isset($mutasi['jenis_mutasi']) ? htmlspecialchars($mutasi['jenis_mutasi']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Tempat Lahir</th>
                <td><?= isset($mutasi['tempat_lahir']) ? htmlspecialchars($mutasi['tempat_lahir']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Tanggal Lahir</th>
                <td><?= isset($mutasi['tanggal_lahir']) ? htmlspecialchars($mutasi['tanggal_lahir']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Jenis Kelamin</th>
                <td><?= isset($mutasi['jenis_kelamin']) ? htmlspecialchars($mutasi['jenis_kelamin']) : 'Data tidak tersedia' ?></td>
            </tr>
        </table>

        <!-- B. Data Alamat -->
        <h4>B. Data Alamat</h4>
        <table class="table table-bordered">
            <tr>
                <th>Alamat KTP</th>
                <td><?= isset($mutasi['alamat_ktp']) ? htmlspecialchars($mutasi['alamat_ktp']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Alamat</th>
                <td><?= isset($mutasi['alamat_asal']) ? htmlspecialchars($mutasi['alamat_asal']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Desa/Kelurahan</th>
                <td><?= isset($mutasi['desa_kelurahan']) ? htmlspecialchars($mutasi['desa_kelurahan']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Kecamatan</th>
                <td><?= isset($mutasi['kecamatan']) ? htmlspecialchars($mutasi['kecamatan']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Kabupaten/Kota</th>
                <td><?= isset($mutasi['kabupaten']) ? htmlspecialchars($mutasi['kabupaten']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Provinsi</th>
                <td><?= isset($mutasi['provinsi']) ? htmlspecialchars($mutasi['provinsi']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Negara</th>
                <td><?= isset($mutasi['negara']) ? htmlspecialchars($mutasi['negara']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>RT/RW</th>
                <td><?= isset($mutasi['rt']) ? htmlspecialchars($mutasi['rt']) : 'Data tidak tersedia' ?>/<?= isset($mutasi['rw']) ? htmlspecialchars($mutasi['rw']) : 'Data tidak tersedia' ?></td>
            </tr>
        </table>

        <!-- C. Data Lain-lain -->
        <h4>C. Data Lain-lain</h4>
        <table class="table table-bordered">
            <tr>
                <th>Agama</th>
                <td><?= isset($mutasi['agama']) ? htmlspecialchars($mutasi['agama']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Pendidikan</th>
                <td><?= isset($mutasi['pendidikan']) ? htmlspecialchars($mutasi['pendidikan']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Pekerjaan</th>
                <td><?= isset($mutasi['pekerjaan']) ? htmlspecialchars($mutasi['pekerjaan']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Status Perkawinan</th>
                <td><?= isset($mutasi['status_perkawinan']) ? htmlspecialchars($mutasi['status_perkawinan']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Status Tinggal</th>
                <td><?= isset($mutasi['status_tinggal']) ? htmlspecialchars($mutasi['status_tinggal']) : 'Data tidak tersedia' ?></td>
            </tr>
        </table>

        <!-- D. Data Aplikasi -->
        <h4>D. Data Aplikasi</h4>
        <table class="table table-bordered">
            <tr>
                <th>Diinput oleh</th>
                <td><?= isset($mutasi['diinput_oleh']) ? htmlspecialchars($mutasi['diinput_oleh']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Diinput tanggal</th>
                <td><?= isset($mutasi['diinput_tgl']) ? htmlspecialchars($mutasi['diinput_tgl']) : 'Data tidak tersedia' ?></td>
            </tr>
            <tr>
                <th>Terakhir diperbarui</th>
                <td><?= isset($mutasi['diperbaharui']) ? htmlspecialchars($mutasi['diperbaharui']) : 'Data tidak tersedia' ?></td>
            </tr>
        </table>

        <a href="data_mutasi.php" class="btn btn-secondary mt-3">Kembali</a>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
