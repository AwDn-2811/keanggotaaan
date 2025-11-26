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

} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Warga - Admin</title>

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
        dt {
            font-weight: 600;
        }
        dd {
            margin-bottom: .4rem;
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

                <a class="nav-link" href="data_user.php">👤 User</a>
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
                <h3 class="mb-0">👤 Detail Warga</h3>
                <small class="text-muted">
                    Informasi lengkap untuk NIK: <b><?= htmlspecialchars($nik) ?></b>
                </small>
            </div>
            <a href="data_warga.php" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Data Warga
            </a>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php elseif ($warga): ?>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    Biodata Warga
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Kolom kiri -->
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">NIK</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['nik']) ?></dd>

                                <dt class="col-sm-4">Nama Warga</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['nama_warga']) ?></dd>

                                <dt class="col-sm-4">Jenis Kelamin</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['jenis_kelamin']) ?></dd>

                                <dt class="col-sm-4">Tempat Lahir</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['tempat_lahir']) ?></dd>

                                <dt class="col-sm-4">Tanggal Lahir</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['tanggal_lahir']) ?></dd>

                                <dt class="col-sm-4">Alamat</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['alamat']) ?></dd>

                                <dt class="col-sm-4">Alamat KTP</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['alamat_ktp']) ?></dd>

                                <dt class="col-sm-4">Desa / Kelurahan</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['desa_kelurahan']) ?></dd>

                                <dt class="col-sm-4">Kecamatan</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['kecamatan']) ?></dd>

                                <dt class="col-sm-4">Kabupaten</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['kabupaten']) ?></dd>
                            </dl>
                        </div>

                        <!-- Kolom kanan -->
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Provinsi</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['provinsi']) ?></dd>

                                <dt class="col-sm-4">Negara</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['negara']) ?></dd>

                                <dt class="col-sm-4">RT / RW</dt>
                                <dd class="col-sm-8">
                                    RT <?= htmlspecialchars($warga['rt']) ?> /
                                    RW <?= htmlspecialchars($warga['rw']) ?>
                                </dd>

                                <dt class="col-sm-4">Status Perkawinan</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['status_perkawinan']) ?></dd>

                                <dt class="col-sm-4">Status Tinggal</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['status_tinggal']) ?></dd>

                                <dt class="col-sm-4">Agama</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['agama']) ?></dd>

                                <dt class="col-sm-4">Pendidikan Terakhir</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['pendidikan_terakhir']) ?></dd>

                                <dt class="col-sm-4">Pekerjaan</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['pekerjaan']) ?></dd>

                                <dt class="col-sm-4">No. KK</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($warga['kk_id']) ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aksi cepat -->
            <div class="card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">Aksi cepat untuk warga ini:</span>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="edit_warga.php?nik=<?= $warga['nik'] ?>"
                           class="btn btn-warning">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <a href="hapus_warga.php?nik=<?= $warga['nik'] ?>"
                           class="btn btn-danger"
                           onclick="return confirm('Yakin ingin menghapus data warga ini?');">
                            <i class="bi bi-trash"></i> Hapus
                        </a>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </main>
</div>

</body>
</html>
