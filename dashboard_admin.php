<?php
session_start();

// Hanya admin yang boleh akses
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['user_name'];

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

$total_warga          = 0;
$total_mutasi         = 0;
$total_mutasi_pending = 0;
$error_message        = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Hitung total warga
    $q1 = $pdo->query("SELECT COUNT(*) FROM data_warga");
    $total_warga = (int) $q1->fetchColumn();

    // Hitung total mutasi
    $q2 = $pdo->query("SELECT COUNT(*) FROM data_mutasi");
    $total_mutasi = (int) $q2->fetchColumn();

    // Hitung mutasi pending
    $q3 = $pdo->query("SELECT COUNT(*) FROM data_mutasi WHERE status = 'Pending'");
    $total_mutasi_pending = (int) $q3->fetchColumn();

} catch (PDOException $e) {
    $error_message = 'Error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Sistem Pendataan Warga</title>

    <link rel="stylesheet" href="styles.css">
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
        .sidebar .brand h4 { font-size: 1.1rem; margin: 0; }
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
            background: #020617;
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

    <!-- SIDEBAR ADMIN -->
    <aside class="sidebar d-flex flex-column">
        <div class="brand">
            <h4>Panel Admin</h4>
            <small class="text-muted">Sistem Pendataan Warga</small>
        </div>

        <div class="flex-grow-1">
            <div class="nav-section-title">Menu Utama</div>
            <nav class="nav flex-column">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard_admin.php' ? 'active' : '' ?>"
                   href="dashboard_admin.php">
                    🏠 Dasbor
                </a>

                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'data_warga.php' ? 'active' : '' ?>"
                   href="data_warga.php">
                    👥 Data Warga
                </a>

                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'data_kk.php' ? 'active' : '' ?>"
                   href="data_kk.php">
                    🧾 Data Kartu Keluarga
                </a>

                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'data_mutasi.php' ? 'active' : '' ?>"
                   href="data_mutasi.php">
                    🔁 Data Mutasi
                </a>

                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>"
                   href="users.php">
                    👤 User
                </a>
            </nav>

            <!-- MENU BANSOS -->
<div class="nav-section-title mt-3">Program Bansos</div>
<nav class="nav flex-column">

    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'program_bantuan_list.php' ? 'active' : '' ?>"
       href="program_bantuan/program_bantuan_list.php">
       <i class="fa-solid fa-gift"></i> Program Bantuan
    </a>

    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'pengajuan_list.php' ? 'active' : '' ?>"
       href="pengajuan_bantuan/pengajuan_list.php">
       <i class="fa-solid fa-file-circle-plus"></i> Pengajuan Bantuan
    </a>

    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'penyaluran_list.php' ? 'active' : '' ?>"
       href="penyaluran_bantuan/penyaluran_list.php">
       <i class="fa-solid fa-truck"></i> Penyaluran Bantuan
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
                Login sebagai Admin:<br>
                <strong><?= htmlspecialchars($admin_name) ?></strong>
            </small>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-0">Selamat datang, <?= htmlspecialchars($admin_name) ?> 👋</h3>
                <small class="text-muted">
                    Ringkasan data pendataan warga dan mutasi.
                </small>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <!-- KARTU RINGKASAN -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Total Warga</span>
                            <span>👥</span>
                        </div>
                        <h3 class="mb-0"><?= $total_warga ?></h3>
                        <small class="text-muted">Jumlah record di tabel data_warga</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Total Mutasi</span>
                            <span>📄</span>
                        </div>
                        <h3 class="mb-0"><?= $total_mutasi ?></h3>
                        <small class="text-muted">Semua data di tabel data_mutasi</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Mutasi Pending</span>
                            <span>⏳</span>
                        </div>
                        <h3 class="mb-0"><?= $total_mutasi_pending ?></h3>
                        <small class="text-muted">Menunggu persetujuan admin</small>

                        <div class="mt-2">
                            <a href="pengajuan_mutasi_admin.php" class="btn btn-sm btn-outline-primary">
                                Kelola Pengajuan Mutasi
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL AKSI CEPAT -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                Aksi Cepat
            </div>
            <div class="card-body">
                <p class="text-muted">Pilih salah satu menu untuk mulai mengelola data:</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="data_warga.php" class="btn btn-outline-secondary btn-sm">👥 Kelola Data Warga</a>
                    <a href="data_mutasi.php" class="btn btn-outline-secondary btn-sm">📄 Kelola Data Mutasi</a>
                    <a href="pengajuan_mutasi_admin.php" class="btn btn-outline-secondary btn-sm">🔁 Lihat Pengajuan Mutasi</a>
                </div>
            </div>
        </div>

    </main>
</div>

</body>
</html>
