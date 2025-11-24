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
$anggota = [];

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // -------- 1. Ambil data KK berdasarkan nomor_kk --------
    $stmt = $pdo->prepare("SELECT * FROM data_kk WHERE nomor_kk = :nomor_kk LIMIT 1");
    $stmt->execute([':nomor_kk' => $nomor_kk]);
    $kk_detail = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kk_detail) {
        $error_message = "Data Kartu Keluarga dengan Nomor KK $nomor_kk tidak ditemukan.";
        $kk_id = 0; 
    } else {
        $kk_id = $kk_detail['id'];
        
        // -------- 2. Ambil data anggota keluarga dari data_warga (Sudah pasti benar) --------
        $stmtAnggota = $pdo->prepare("
            SELECT nik, nama_warga, jenis_kelamin, tempat_lahir, tanggal_lahir, status_perkawinan, status_hubungan_kk 
            FROM data_warga 
            WHERE kk_id = :kk_id 
            ORDER BY FIELD(status_hubungan_kk, 'Kepala Keluarga', 'Istri', 'Anak', 'Cucu', 'Orang Tua', 'Anggota Lain'), nama_warga ASC
        "); 

        $stmtAnggota->execute([':kk_id' => $kk_id]);
        $anggota = $stmtAnggota->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $error_message = "Error Database: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail KK <?= htmlspecialchars($nomor_kk) ?> - Admin</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* Gaya Konsisten Sidebar */
        body { background-color: #f5f6fa; }
        .layout-wrapper { min-height: 100vh; }
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #111827;
            color: #e5e7eb;
            flex-shrink: 0; 
        }
        .sidebar .brand { padding: 1rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar .brand h4 { margin: 0; }
        .sidebar .nav-link { color: #e5e7eb; padding: .6rem 1.25rem; }
        .sidebar .nav-link.active { background: #020617; font-weight: bold; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,.06); }
        .sidebar .nav-section-title { padding: .75rem 1.25rem .25rem; opacity: .6; text-transform: uppercase; font-size: .75rem; }
        .main-content { flex: 1; padding: 1.5rem; }
        
        .detail-item {
            display: flex;
            margin-bottom: 5px;
        }
        .detail-item strong {
            width: 150px; /* Lebar label yang konsisten */
            flex-shrink: 0;
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

    <main class="main-content">

        <h3 class="mb-4">🧾 Detail Kartu Keluarga</h3>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <p class="text-muted mb-0">
                Informasi lengkap untuk Nomor KK: <b><?= htmlspecialchars($nomor_kk) ?></b>
            </p>

        </div>
        

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle"></i> **Error:** <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($kk_detail): ?>
            <div class="row mb-5">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Informasi Kartu Keluarga</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="detail-item"><strong>Nomor KK</strong> : <?= htmlspecialchars($kk_detail['nomor_kk']) ?></div>
                                    <div class="detail-item"><strong>Kepala Keluarga</strong> : <?= htmlspecialchars($kk_detail['kepala_keluarga']) ?></div>
                                    <div class="detail-item"><strong>NIK Kepala</strong> : <?= htmlspecialchars($kk_detail['nik_kepala']) ?></div>
                                    <div class="detail-item"><strong>Alamat</strong> : <?= htmlspecialchars($kk_detail['alamat']) ?></div>
                                    <div class="detail-item"><strong>RT/RW</strong> : <?= htmlspecialchars($kk_detail['rt']) ?>/<?= htmlspecialchars($kk_detail['rw']) ?></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-item"><strong>Desa/Kel.</strong> : <?= htmlspecialchars($kk_detail['desa_kelurahan']) ?></div>
                                    <div class="detail-item"><strong>Kecamatan</strong> : <?= htmlspecialchars($kk_detail['kecamatan']) ?></div>
                                    <div class="detail-item"><strong>Kab./Kota</strong> : <?= htmlspecialchars($kk_detail['kabupaten']) ?></div>
                                    <div class="detail-item"><strong>Provinsi</strong> : <?= htmlspecialchars($kk_detail['provinsi']) ?></div>
                                    <div class="detail-item"><strong>Negara</strong> : <?= htmlspecialchars($kk_detail['negara']) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="mb-3">Anggota Keluarga (Total: <?= count($anggota) ?>)</h4>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>NIK</th>
                                <th>Nama Warga</th>
                                <th>Hubungan</th>
                                <th>Jenis Kelamin</th>
                                <th>Tempat Lahir</th>
                                <th>Tgl. Lahir</th>
                                <th>Status Perkawinan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($anggota)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Tidak ada anggota keluarga yang terdaftar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($anggota as $index => $a): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($a['nik'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($a['nama_warga'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($a['status_hubungan_kk'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($a['jenis_kelamin'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($a['tempat_lahir'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($a['tanggal_lahir'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($a['status_perkawinan'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">Aksi cepat untuk Kartu Keluarga ini:</span>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                                    <a href="data_kk.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Data KK
            </a>
                        <a href="ubah_kk.php?id=<?= $kk_detail['id'] ?>"
                           class="btn btn-warning">
                             <i class="bi bi-pencil-square"></i> Edit Detail KK
                        </a>
                        <a href="hapus_kk.php?nomor_kk=<?= $kk_detail['nomor_kk'] ?>"
                           class="btn btn-danger"
                           onclick="return confirm('Yakin ingin menghapus Kartu Keluarga ini beserta anggotanya? Anggota keluarga akan kehilangan ikatan KK.');">
                             <i class="bi bi-trash"></i> Hapus KK
                        </a>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>