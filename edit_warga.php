<?php
session_start();

// Hanya admin yang boleh akses
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['user_name'];

// Ambil NIK dari parameter URL
if (!isset($_GET['nik']) || empty($_GET['nik'])) {
    die('NIK tidak ditemukan. Kembali ke <a href="data_warga.php">Data Warga</a>.');
}

$nik = $_GET['nik'];

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

$error_message = '';
$success_message = '';
$warga = null;

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Ambil data warga berdasarkan NIK
    $sql = "SELECT * FROM data_warga WHERE nik = :nik LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':nik' => $nik]);
    $warga = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$warga) {
        $error_message = "Data warga dengan NIK $nik tidak ditemukan.";
    }

    // Proses update data warga jika form disubmit
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Ambil data yang diubah
        $nama_warga = $_POST['nama_warga'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $tempat_lahir = $_POST['tempat_lahir'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $alamat = $_POST['alamat'];
        $alamat_ktp = $_POST['alamat_ktp'];
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
        $kk_id = $_POST['kk_id'];

        // Update data warga ke database
        $sql_update = "UPDATE data_warga SET
                        nama_warga = :nama_warga,
                        jenis_kelamin = :jenis_kelamin,
                        tempat_lahir = :tempat_lahir,
                        tanggal_lahir = :tanggal_lahir,
                        alamat = :alamat,
                        alamat_ktp = :alamat_ktp,
                        desa_kelurahan = :desa_kelurahan,
                        kecamatan = :kecamatan,
                        kabupaten = :kabupaten,
                        provinsi = :provinsi,
                        negara = :negara,
                        rt = :rt,
                        rw = :rw,
                        agama = :agama,
                        pendidikan_terakhir = :pendidikan_terakhir,
                        pekerjaan = :pekerjaan,
                        status_perkawinan = :status_perkawinan,
                        status_tinggal = :status_tinggal,
                        kk_id = :kk_id
                      WHERE nik = :nik";
        
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([
            ':nik' => $nik,
            ':nama_warga' => $nama_warga,
            ':jenis_kelamin' => $jenis_kelamin,
            ':tempat_lahir' => $tempat_lahir,
            ':tanggal_lahir' => $tanggal_lahir,
            ':alamat' => $alamat,
            ':alamat_ktp' => $alamat_ktp,
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
            ':kk_id' => $kk_id
        ]);

        // Set pesan sukses jika berhasil
        $success_message = "Data warga berhasil diperbarui.";
        header("Location: data_warga.php"); // Redirect ke Data Warga setelah berhasil update
        exit();
    }

} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Warga - Admin</title>

    <link rel="stylesheet" href="styles.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        body { background-color: #f5f6fa; }
        .layout-wrapper { min-height: 100vh; }

        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #111827;
            color: #e5e7eb;
        }
        .sidebar .brand {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar .brand h4 { margin: 0; }
        .sidebar .nav-link { color: #e5e7eb; padding: .6rem 1.25rem; }
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

        label {
            font-weight: 600;
        }

        .btn-group {
            margin-top: 20px;
        }

        .text-muted {
            font-size: 14px;
        }
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

                <a class="nav-link active" href="data_warga.php">👥 Data Warga</a>

                <a class="nav-link" href="data_kk.php">🧾 Data Kartu Keluarga</a>

                <a class="nav-link" href="data_mutasi.php">🔁 Data Mutasi</a>

                <a class="nav-link" href="users.php">👤 User</a>
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

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-0">👤 Edit Warga</h3>
                <small class="text-muted">Edit data warga dengan NIK: <b><?= htmlspecialchars($nik) ?></b></small>
            </div>
            
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php elseif (!empty($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php endif; ?>

        <form action="edit_warga.php?nik=<?= htmlspecialchars($nik) ?>" method="POST">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    Form Edit Data Warga
                </div>
                <div class="card-body">
                    
                    <!-- Nama Warga -->
                    <div class="mb-3">
                        <label for="nama_warga" class="form-label">Nama Warga</label>
                        <input type="text" class="form-control" id="nama_warga" name="nama_warga"
                               value="<?= htmlspecialchars($warga['nama_warga']) ?>" required>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div class="mb-3">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="Laki-laki" <?= $warga['jenis_kelamin'] == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= $warga['jenis_kelamin'] == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>

                    <!-- Tempat Lahir -->
                    <div class="mb-3">
                        <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir"
                               value="<?= htmlspecialchars($warga['tempat_lahir']) ?>" required>
                    </div>

                    <!-- Tanggal Lahir -->
                    <div class="mb-3">
                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir"
                               value="<?= htmlspecialchars($warga['tanggal_lahir']) ?>" required>
                    </div>

                    <!-- Alamat -->
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($warga['alamat']) ?></textarea>
                    </div>

                    <!-- Alamat KTP -->
                    <div class="mb-3">
                        <label for="alamat_ktp" class="form-label">Alamat KTP</label>
                        <textarea class="form-control" id="alamat_ktp" name="alamat_ktp" rows="3" required><?= htmlspecialchars($warga['alamat_ktp']) ?></textarea>
                    </div>

                    <!-- Desa / Kelurahan -->
                    <div class="mb-3">
                        <label for="desa_kelurahan" class="form-label">Desa / Kelurahan</label>
                        <input type="text" class="form-control" id="desa_kelurahan" name="desa_kelurahan"
                               value="<?= htmlspecialchars($warga['desa_kelurahan']) ?>" required>
                    </div>

                    <!-- Kecamatan -->
                    <div class="mb-3">
                        <label for="kecamatan" class="form-label">Kecamatan</label>
                        <input type="text" class="form-control" id="kecamatan" name="kecamatan"
                               value="<?= htmlspecialchars($warga['kecamatan']) ?>" required>
                    </div>

                    <!-- Kabupaten -->
                    <div class="mb-3">
                        <label for="kabupaten" class="form-label">Kabupaten</label>
                        <input type="text" class="form-control" id="kabupaten" name="kabupaten"
                               value="<?= htmlspecialchars($warga['kabupaten']) ?>" required>
                    </div>

                    <!-- Provinsi -->
                    <div class="mb-3">
                        <label for="provinsi" class="form-label">Provinsi</label>
                        <input type="text" class="form-control" id="provinsi" name="provinsi"
                               value="<?= htmlspecialchars($warga['provinsi']) ?>" required>
                    </div>

                    <!-- Negara -->
                    <div class="mb-3">
                        <label for="negara" class="form-label">Negara</label>
                        <input type="text" class="form-control" id="negara" name="negara"
                               value="<?= htmlspecialchars($warga['negara']) ?>" required>
                    </div>

                    <!-- RT / RW -->
                    <div class="mb-3">
                        <label for="rt" class="form-label">RT</label>
                        <input type="text" class="form-control" id="rt" name="rt"
                               value="<?= htmlspecialchars($warga['rt']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="rw" class="form-label">RW</label>
                        <input type="text" class="form-control" id="rw" name="rw"
                               value="<?= htmlspecialchars($warga['rw']) ?>" required>
                    </div>

                    <!-- Status Perkawinan -->
                    <div class="mb-3">
                        <label for="status_perkawinan" class="form-label">Status Perkawinan</label>
                        <input type="text" class="form-control" id="status_perkawinan" name="status_perkawinan"
                               value="<?= htmlspecialchars($warga['status_perkawinan']) ?>" required>
                    </div>

                    <!-- Status Tinggal -->
                    <div class="mb-3">
                        <label for="status_tinggal" class="form-label">Status Tinggal</label>
                        <input type="text" class="form-control" id="status_tinggal" name="status_tinggal"
                               value="<?= htmlspecialchars($warga['status_tinggal']) ?>" required>
                    </div>

                    <!-- Agama -->
                    <div class="mb-3">
                        <label for="agama" class="form-label">Agama</label>
                        <input type="text" class="form-control" id="agama" name="agama"
                               value="<?= htmlspecialchars($warga['agama']) ?>" required>
                    </div>

                    <!-- Pendidikan Terakhir -->
                    <div class="mb-3">
                        <label for="pendidikan_terakhir" class="form-label">Pendidikan Terakhir</label>
                        <input type="text" class="form-control" id="pendidikan_terakhir" name="pendidikan_terakhir"
                               value="<?= htmlspecialchars($warga['pendidikan_terakhir']) ?>" required>
                    </div>

                    <!-- Pekerjaan -->
                    <div class="mb-3">
                        <label for="pekerjaan" class="form-label">Pekerjaan</label>
                        <input type="text" class="form-control" id="pekerjaan" name="pekerjaan"
                               value="<?= htmlspecialchars($warga['pekerjaan']) ?>" required>
                    </div>

                    <!-- No. KK -->
                    <div class="mb-3">
                        <label for="kk_id" class="form-label">No. KK</label>
                        <input type="text" class="form-control" id="kk_id" name="kk_id"
                               value="<?= htmlspecialchars($warga['kk_id']) ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>

                    <a href="data_warga.php" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Data Warga
            </a>
                </div>
            </div>
        </form>

    </main>
</div>

</body>
</html>
