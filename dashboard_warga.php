<?php
session_start();

// Cek apakah user sudah login dan role = warga
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'warga') {
    header("Location: login.php");
    exit();
}

$nik        = $_SESSION['nik'];       // NIK warga dari session
$user_name  = $_SESSION['user_name']; // Nama depan dari session

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

$warga = null;
$mutasi = [];
$error_message = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Ambil data pribadi dari data_warga berdasarkan NIK
    $sql = "SELECT * FROM data_warga WHERE nik = :nik LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':nik' => $nik]);
    $warga = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ambil riwayat mutasi dari data_mutasi
    $sql2 = "SELECT * FROM data_mutasi WHERE nik_warga = :nik ORDER BY tanggal_mutasi DESC";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([':nik' => $nik]);
    $mutasi = $stmt2->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Warga - Sistem Pendataan Warga</title>

    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        body {
            background-color: #f5f6fa;
        }
        .layout-wrapper {
            min-height: 100vh;
        }
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #1f2937;
            color: #e5e7eb;
        }
        .sidebar .brand {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar .brand h4 {
            font-size: 1.1rem;
            margin: 0;
        }
        .sidebar .nav-link {
            color: #e5e7eb;
            padding: .6rem 1.25rem;
            font-size: .95rem;
            border-radius: 0;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.06);
        }
        .sidebar .nav-link.active {
            background: #111827;
            font-weight: 600;
        }
        .sidebar .nav-section-title {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: .75rem 1.25rem .25rem;
            opacity: .6;
        }
        .main-content {
            flex: 1;
            padding: 1.5rem 1.5rem 2rem;
        }
        .topbar {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

<div class="d-flex layout-wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar d-flex flex-column">
        <div class="brand">
            <h4>Sistem Warga</h4>
            <small class="text-muted">Dashboard Warga</small>
        </div>

        <div class="flex-grow-1">
        <div class="nav-section-title">Menu Utama</div>
<nav class="nav flex-column">
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard_warga.php' ? 'active' : '' ?>" href="dashboard_warga.php">
        🏠 Dasbor
    </a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'kk_saya.php' ? 'active' : '' ?>" href="kk_saya.php">
        📄 Kartu Keluarga Saya
    </a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'pengajuan_mutasi.php' ? 'active' : '' ?>" href="pengajuan_mutasi.php">
        🔁 Pengajuan Mutasi
    </a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'pengaturan_akun.php' ? 'active' : '' ?>" href="pengaturan_akun.php">
        ⚙️ Pengaturan Akun
    </a>
</nav>




            <div class="nav-section-title">Lainnya</div>
            <nav class="nav flex-column">
                <!-- Nanti kalau kamu punya fitur baru, tinggal tambah di sini -->
                <!-- Contoh:
                <a class="nav-link" href="pengajuan_surat.php">📄 Pengajuan Surat</a>
                -->
                <a class="nav-link text-danger" href="logout.php">
                    🚪 Logout
                </a>
            </nav>
        </div>

        <div class="p-3 border-top border-secondary">
            <small class="d-block">
                Login sebagai:<br>
                <strong><?= htmlspecialchars($user_name) ?></strong><br>
                <span class="text-muted">NIK: <?= htmlspecialchars($nik) ?></span>
            </small>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- TOPBAR -->
        <div class="topbar d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-0">Selamat datang, <?= htmlspecialchars($user_name) ?> 👋</h3>
                <small class="text-muted">Beranda Dashboard Warga</small>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <?php if (!$warga): ?>
            <div class="alert alert-warning">
                Data pribadi untuk NIK <strong><?= htmlspecialchars($nik) ?></strong> tidak ditemukan
                di tabel <code>data_warga</code>.<br>
                Silakan hubungi admin RT/RW untuk memastikan data kamu sudah didaftarkan.
            </div>
        <?php else: ?>

            <!-- RINGKASAN 2 CARD -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Ringkasan Profil</h5>
                            <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($warga['nama_warga']) ?></p>
                            <p class="mb-1"><strong>NIK:</strong> <?= htmlspecialchars($warga['nik']) ?></p>
                            <p class="mb-1"><strong>Alamat:</strong> <?= htmlspecialchars($warga['alamat']) ?></p>
                            <p class="mb-0"><strong>RT/RW:</strong> <?= htmlspecialchars($warga['rt']) ?>/<?= htmlspecialchars($warga['rw']) ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Info Tambahan</h5>
                            <p class="mb-1"><strong>Jenis Kelamin:</strong> <?= htmlspecialchars($warga['jenis_kelamin']) ?></p>
                            <p class="mb-1"><strong>Tanggal Lahir:</strong> <?= htmlspecialchars($warga['tanggal_lahir']) ?></p>
                            <p class="mb-1"><strong>Pekerjaan:</strong> <?= htmlspecialchars($warga['pekerjaan']) ?></p>
                            <p class="mb-0"><strong>Status Tinggal:</strong> <?= htmlspecialchars($warga['status_tinggal']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DETAIL DATA PRIBADI -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    Data Pribadi Lengkap
                </div>
                <div class="card-body">
                    <table class="table table-striped mb-0">
                        <tr><th style="width: 25%;">NIK</th><td><?= htmlspecialchars($warga['nik']) ?></td></tr>
                        <tr><th>Nama Lengkap</th><td><?= htmlspecialchars($warga['nama_warga']) ?></td></tr>
                        <tr><th>Jenis Kelamin</th><td><?= htmlspecialchars($warga['jenis_kelamin']) ?></td></tr>
                        <tr><th>Tempat / Tanggal Lahir</th><td><?= htmlspecialchars($warga['tempat_lahir'] ?? '-') ?> / <?= htmlspecialchars($warga['tanggal_lahir']) ?></td></tr>
                        <tr><th>Alamat</th><td><?= htmlspecialchars($warga['alamat']) ?></td></tr>
                        <tr><th>RT / RW</th><td><?= htmlspecialchars($warga['rt']) ?> / <?= htmlspecialchars($warga['rw']) ?></td></tr>
                        <tr><th>Agama</th><td><?= htmlspecialchars($warga['agama'] ?? '-') ?></td></tr>
                        <tr><th>Pendidikan Terakhir</th><td><?= htmlspecialchars($warga['pendidikan_terakhir'] ?? '-') ?></td></tr>
                        <tr><th>Pekerjaan</th><td><?= htmlspecialchars($warga['pekerjaan']) ?></td></tr>
                        <tr><th>Status Perkawinan</th><td><?= htmlspecialchars($warga['status_perkawinan'] ?? '-') ?></td></tr>
                        <tr><th>Status Tinggal</th><td><?= htmlspecialchars($warga['status_tinggal']) ?></td></tr>
                    </table>
                </div>
            </div>

            <!-- RIWAYAT MUTASI -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-secondary text-white">
                    Riwayat Mutasi
                </div>
                <div class="card-body">
                    <?php if (empty($mutasi)): ?>
                        <p class="text-muted mb-0">Belum ada riwayat mutasi yang tercatat.</p>
                    <?php else: ?>
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Jenis Mutasi</th>
                                    <th>Alamat Asal</th>
                                    <th>Alamat Tujuan</th>
                                    <th>Tanggal Mutasi</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($mutasi as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['jenis_mutasi']) ?></td>
                                    <td><?= htmlspecialchars($m['alamat_asal']) ?></td>
                                    <td><?= htmlspecialchars($m['alamat_tujuan']) ?></td>
                                    <td><?= htmlspecialchars($m['tanggal_mutasi']) ?></td>
                                    <td><?= htmlspecialchars($m['keterangan']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        <?php endif; ?>

    </main>
</div>

</body>
</html>
