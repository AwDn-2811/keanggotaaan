<?php
session_start();

// Hanya admin yang boleh akses
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['user_name'];

// Ambil nomor KK dari parameter URL
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $kepala_keluarga = $_POST['kepala_keluarga'];
    $alamat = $_POST['alamat'];
    $rt = $_POST['rt'];
    $rw = $_POST['rw'];
    $desa_kelurahan = $_POST['desa_kelurahan'];
    $kecamatan = $_POST['kecamatan'];
    $kabupaten = $_POST['kabupaten'];
    $provinsi = $_POST['provinsi'];
    $negara = $_POST['negara'];
    $kode_pos = $_POST['kode_pos'];

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Update data KK
        $stmt = $pdo->prepare("UPDATE data_kk SET 
                                kepala_keluarga = :kepala_keluarga, 
                                alamat = :alamat, 
                                rt = :rt, 
                                rw = :rw, 
                                desa_kelurahan = :desa_kelurahan, 
                                kecamatan = :kecamatan, 
                                kabupaten = :kabupaten, 
                                provinsi = :provinsi, 
                                negara = :negara,
                                kode_pos = :kode_pos
                                WHERE nomor_kk = :nomor_kk");
        $stmt->execute([
            ':kepala_keluarga' => $kepala_keluarga,
            ':alamat' => $alamat,
            ':rt' => $rt,
            ':rw' => $rw,
            ':desa_kelurahan' => $desa_kelurahan,
            ':kecamatan' => $kecamatan,
            ':kabupaten' => $kabupaten,
            ':provinsi' => $provinsi,
            ':negara' => $negara,
            ':kode_pos' => $kode_pos,
            ':nomor_kk' => $nomor_kk
        ]);

        // Redirect ke halaman detail KK setelah berhasil update
        header("Location: detail_kk.php?nomor_kk=$nomor_kk");
        exit();

    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
} else {
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

        if (!$kk_detail) {
            $error_message = "Data Kartu Keluarga dengan Nomor KK $nomor_kk tidak ditemukan.";
        }

    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ubah Kartu Keluarga - Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        body { background-color: #f5f6fa; }
        .layout-wrapper { min-height: 100vh; }
        .sidebar { width: 240px; min-height: 100vh; background: #111827; color: #e5e7eb; }
        .sidebar .brand { padding: 1rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar .brand h4 { margin: 0; }
        .sidebar .nav-link { color: #e5e7eb; padding: .6rem 1.25rem; }
        .sidebar .nav-link.active { background: #020617; font-weight: bold; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,.06); }
        .sidebar .nav-section-title { padding: .75rem 1.25rem .25rem; opacity: .6; text-transform: uppercase; font-size: .75rem; }
        .main-content { flex: 1; padding: 1.5rem; }
    </style>
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
                <a class="nav-link" href="users.php">👤 User</a>
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
            <form method="POST">
                <h5>A. Data Kartu Keluarga</h5>
                <div class="mb-3">
                    <label for="nomor_kk" class="form-label">Nomor Kartu Keluarga</label>
                    <input type="text" name="nomor_kk" id="nomor_kk" class="form-control" value="<?= htmlspecialchars($kk_detail['nomor_kk']) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="kepala_keluarga" class="form-label">Nama Kepala Keluarga</label>
                    <input type="text" name="kepala_keluarga" id="kepala_keluarga" class="form-control" value="<?= htmlspecialchars($kk_detail['kepala_keluarga']) ?>" required>
                </div>

                <h5>B. Data Alamat</h5>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <input type="text" name="alamat" id="alamat" class="form-control" value="<?= htmlspecialchars($kk_detail['alamat']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="rt" class="form-label">RT</label>
                    <input type="text" name="rt" id="rt" class="form-control" value="<?= htmlspecialchars($kk_detail['rt']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="rw" class="form-label">RW</label>
                    <input type="text" name="rw" id="rw" class="form-control" value="<?= htmlspecialchars($kk_detail['rw']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="desa_kelurahan" class="form-label">Desa / Kelurahan</label>
                    <input type="text" name="desa_kelurahan" id="desa_kelurahan" class="form-control" value="<?= htmlspecialchars($kk_detail['desa_kelurahan']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="kecamatan" class="form-label">Kecamatan</label>
                    <input type="text" name="kecamatan" id="kecamatan" class="form-control" value="<?= htmlspecialchars($kk_detail['kecamatan']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="kabupaten" class="form-label">Kabupaten/Kota</label>
                    <input type="text" name="kabupaten" id="kabupaten" class="form-control" value="<?= htmlspecialchars($kk_detail['kabupaten']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="provinsi" class="form-label">Provinsi</label>
                    <input type="text" name="provinsi" id="provinsi" class="form-control" value="<?= htmlspecialchars($kk_detail['provinsi']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="negara" class="form-label">Negara</label>
                    <input type="text" name="negara" id="negara" class="form-control" value="<?= htmlspecialchars($kk_detail['negara']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="kode_pos" class="form-label">Kode Pos</label>
                    <input type="text" name="kode_pos" id="kode_pos" class="form-control" value="<?= htmlspecialchars($kk_detail['kode_pos']) ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        <?php endif; ?>

    </main>
</div>

</body>
</html>
