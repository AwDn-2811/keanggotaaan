<?php
session_start();

// Hanya admin yang boleh akses
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['user_name'];

// Ambil Nomor KK dari parameter URL
if (!isset($_GET['nomor_kk']) || empty($_GET['nomor_kk'])) {
    die('Nomor KK tidak ditemukan. Kembali ke <a href="data_kk.php">Data Kartu Keluarga</a>.');
}

$nomor_kk = $_GET['nomor_kk'];

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

$error_message = '';
$kk_detail = null;
$warga = [];

// Ambil data Kartu Keluarga dan Anggota Keluarga
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Ambil data KK berdasarkan nomor_kk
    $stmt = $pdo->prepare("SELECT * FROM data_kk WHERE nomor_kk = :nomor_kk LIMIT 1");
    $stmt->execute([':nomor_kk' => $nomor_kk]);
    $kk_detail = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ambil data anggota keluarga berdasarkan nomor_kk
    $stmtAnggota = $pdo->prepare("SELECT * FROM anggota_kk WHERE nomor_kk = :nomor_kk");
    $stmtAnggota->execute([':nomor_kk' => $nomor_kk]);
    $anggota = $stmtAnggota->fetchAll(PDO::FETCH_ASSOC);

    // Ambil data warga (untuk dropdown)
    $stmtWarga = $pdo->prepare("SELECT * FROM data_warga");
    $stmtWarga->execute();
    $warga = $stmtWarga->fetchAll(PDO::FETCH_ASSOC);

    if (!$kk_detail) {
        $error_message = "Data Kartu Keluarga dengan Nomor KK $nomor_kk tidak ditemukan.";
    }

} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}

// Proses form jika ada perubahan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $nama_anggota = $_POST['nama_anggota'];
    $hubungan = $_POST['hubungan'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];

    try {
        // Insert anggota baru
        $stmt = $pdo->prepare("INSERT INTO anggota_kk (nomor_kk, nama_anggota, hubungan, jenis_kelamin, tempat_lahir, tanggal_lahir) 
                               VALUES (:nomor_kk, :nama_anggota, :hubungan, :jenis_kelamin, :tempat_lahir, :tanggal_lahir)");
        $stmt->execute([
            ':nomor_kk' => $nomor_kk,
            ':nama_anggota' => $nama_anggota,
            ':hubungan' => $hubungan,
            ':jenis_kelamin' => $jenis_kelamin,
            ':tempat_lahir' => $tempat_lahir,
            ':tanggal_lahir' => $tanggal_lahir
        ]);

        // Redirect ke halaman detail KK setelah berhasil update
        header("Location: detail_kk.php?nomor_kk=$nomor_kk");
        exit();

    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ubah Anggota Keluarga - Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>

<div class="d-flex layout-wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar d-flex flex-column">
        <div class="brand">
            <h4>Panel Admin</h4>
            <small class="text-muted">Sistem Pendataan Warga</small>
        </div>

        <div class="flex-grow-1">
            <div class="nav-section-title">Menu Utama</div>
            <nav class="nav flex-column">
                <a class="nav-link" href="dashboard_admin.php">🏠 Dasbor</a>
                <a class="nav-link" href="data_warga.php">👥 Data Warga</a>
                <a class="nav-link active" href="data_kk.php">🧾 Data Kartu Keluarga</a>
                <a class="nav-link" href="data_mutasi.php">🔁 Data Mutasi</a>
                <a class="nav-link" href="data_user.php">👤 User</a>
            </nav>

            <div class="nav-section-title">Lainnya</div>
            <nav class="nav flex-column">
                <a class="nav-link text-danger" href="logout.php">🚪 Logout</a>
            </nav>
        </div>

        <div class="p-3 border-top border-secondary">
            <small>Login sebagai:<br><b><?= htmlspecialchars($admin_name) ?></b></small>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <h3 class="mb-3">🧾 Ubah Data Kartu Keluarga</h3>
        <p class="text-muted">Berikut adalah form untuk mengubah data Kartu Keluarga dengan Nomor KK: <b><?= htmlspecialchars($nomor_kk) ?></b></p>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <?php if ($kk_detail): ?>
            <!-- Tampilkan Data Kartu Keluarga -->
            <div class="mb-3">
                <label class="form-label">Nomor KK</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($kk_detail['nomor_kk']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Kepala Keluarga</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($kk_detail['kepala_keluarga']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">NIK Kepala Keluarga</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($kk_detail['nik']) ?>" readonly>
            </div>

            <h5>Daftar Nama Warga</h5>
            <div class="mb-3">
                <label for="nama_anggota" class="form-label">Nama Anggota</label>
                <select name="nama_anggota" id="nama_anggota" class="form-select">
                    <option value="">- pilih -</option>
                    <?php foreach ($warga as $w): ?>
                        <option value="<?= $w['nik'] ?>"><?= $w['nama_warga'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <h5>Data Anggota Keluarga</h5>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>NIK</th>
                        <th>Nama Warga</th>
                        <th>Hubungan</th>
                        <th>Jenis Kelamin</th>
                        <th>Tempat Lahir</th>
                        <th>Tanggal Lahir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($anggota as $index => $a): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= $a['nik'] ?></td>
                            <td><?= $a['nama_anggota'] ?></td>
                            <td><?= $a['hubungan'] ?></td>
                            <td><?= $a['jenis_kelamin'] ?></td>
                            <td><?= $a['tempat_lahir'] ?></td>
                            <td><?= $a['tanggal_lahir'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Form Tambah Anggota Keluarga -->
            <h5>Tambah Anggota Keluarga</h5>
            <form method="POST">
                <div class="mb-3">
                    <label for="nama_anggota" class="form-label">Nama Anggota</label>
                    <input type="text" name="nama_anggota" id="nama_anggota" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="hubungan" class="form-label">Hubungan</label>
                    <input type="text" name="hubungan" id="hubungan" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Tambah Anggota</button>
            </form>
        <?php endif; ?>

    </main>
</div>

</body>
</html>
