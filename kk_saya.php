<?php
session_start();

// Hanya untuk warga yang sudah login
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'warga') {
    header("Location: login.php");
    exit();
}

$nik        = $_SESSION['nik'];
$user_name  = $_SESSION['user_name'];

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

$warga = null;
$kk    = null;
$anggota = [];
$error_message = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 1) Ambil data warga dulu (buat tahu kk_id)
    $sqlW = "SELECT * FROM data_warga WHERE nik = :nik LIMIT 1";
    $stmtW = $pdo->prepare($sqlW);
    $stmtW->execute([':nik' => $nik]);
    $warga = $stmtW->fetch(PDO::FETCH_ASSOC);

    if (!$warga) {
        $error_message = "Data warga dengan NIK {$nik} tidak ditemukan.";
    } else {
        $kk_id = $warga['kk_id'] ?? null;

        if (empty($kk_id)) {
            $error_message = "Kamu belum terdaftar dalam data Kartu Keluarga (KK). Silakan hubungi admin RT/RW.";
        } else {
            // 2) Ambil data KK
            $sqlKK = "SELECT * FROM data_kk WHERE id = :id LIMIT 1";
            $stmtKK = $pdo->prepare($sqlKK);
            $stmtKK->execute([':id' => $kk_id]);
            $kk = $stmtKK->fetch(PDO::FETCH_ASSOC);

            if (!$kk) {
                $error_message = "Data KK dengan ID {$kk_id} tidak ditemukan.";
            } else {
                // 3) Ambil semua anggota keluarga (warga dengan kk_id yang sama)
                $sqlA = "SELECT * FROM data_warga WHERE kk_id = :kk_id ORDER BY nama_warga ASC";
                $stmtA = $pdo->prepare($sqlA);
                $stmtA->execute([':kk_id' => $kk_id]);
                $anggota = $stmtA->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    }
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Keluarga Saya - Sistem Pendataan Warga</title>

    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        body { background-color: #f5f6fa; }
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
    </style>
</head>
<body>

<div class="d-flex layout-wrapper">

    <!-- SIDEBAR (sama style dengan dashboard_warga) -->
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

        <div class="mb-4">
            <h3 class="mb-0">Kartu Keluarga Saya</h3>
            <small class="text-muted">Menampilkan data KK dan anggota keluarga yang terdaftar</small>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-warning">
                <?= $error_message ?>
            </div>
        <?php elseif ($kk): ?>

            <!-- INFO KK -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    Informasi Kartu Keluarga
                </div>
                <div class="card-body">
                    <table class="table table-striped mb-0">
                        <tr><th style="width: 30%;">Nomor KK</th><td><?= htmlspecialchars($kk['nomor_kk']) ?></td></tr>
                        <tr><th>Kepala Keluarga</th><td><?= htmlspecialchars($kk['kepala_keluarga']) ?></td></tr>
                        <tr><th>NIK Kepala</th><td><?= htmlspecialchars($kk['nik_kepala']) ?></td></tr>
                        <tr><th>Alamat</th><td><?= htmlspecialchars($kk['alamat']) ?></td></tr>
                        <tr><th>Desa/Kelurahan</th><td><?= htmlspecialchars($kk['desa_kelurahan']) ?></td></tr>
                        <tr><th>Kecamatan</th><td><?= htmlspecialchars($kk['kecamatan']) ?></td></tr>
                        <tr><th>Kabupaten</th><td><?= htmlspecialchars($kk['kabupaten']) ?></td></tr>
                        <tr><th>Provinsi</th><td><?= htmlspecialchars($kk['provinsi']) ?></td></tr>
                        <tr><th>Negara</th><td><?= htmlspecialchars($kk['negara']) ?></td></tr>
                        <tr><th>RT / RW</th><td><?= htmlspecialchars($kk['rt']) ?> / <?= htmlspecialchars($kk['rw']) ?></td></tr>
                        <tr><th>Kode Pos</th><td><?= htmlspecialchars($kk['kode_pos']) ?></td></tr>
                    </table>
                </div>
            </div>

            <!-- ANGGOTA KELUARGA -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    Anggota Keluarga dalam KK Ini
                </div>
                <div class="card-body">
                    <?php if (empty($anggota)): ?>
                        <p class="text-muted mb-0">Belum ada anggota keluarga yang terdaftar pada KK ini.</p>
                    <?php else: ?>
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Alamat</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($anggota as $a): ?>
                                <tr>
                                    <td><?= htmlspecialchars($a['nik']) ?></td>
                                    <td><?= htmlspecialchars($a['nama_warga']) ?></td>
                                    <td><?= htmlspecialchars($a['jenis_kelamin']) ?></td>
                                    <td><?= htmlspecialchars($a['tanggal_lahir']) ?></td>
                                    <td><?= htmlspecialchars($a['alamat']) ?></td>
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
