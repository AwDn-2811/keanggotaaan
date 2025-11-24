<?php
// ========================================================================
// [1] LOGIKA PHP UTAMA & KEAMANAN
// ========================================================================
session_start();

// Hanya admin yang boleh akses
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['user_name'];
$active_link = "data_warga.php"; // Tautan aktif tetap ke Data Warga

// Ambil NIK dari parameter URL (NIK yang gagal dihapus)
if (!isset($_GET['nik']) || empty($_GET['nik'])) {
    die('NIK tidak ditemukan. Kembali ke <a href="data_warga.php">Data Warga</a>.');
}
$nik_terkait = $_GET['nik'];

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

$kk_terkait = []; // Array untuk menampung data KK yang ditemukan
$error_message = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Query untuk mencari data Kartu Keluarga (data_kk) di mana NIK ini menjadi Kepala Keluarga (fk_kk_kepala)
    $sql_cari_kk = "
        SELECT 
            kk.no_kk, 
            kk.alamat_kk, 
            kk.rt_kk, 
            kk.rw_kk 
        FROM data_kk kk
        JOIN data_warga dw ON kk.fk_kk_kepala = dw.nik
        WHERE kk.fk_kk_kepala = :nik
    ";
    
    $stmt_kk = $pdo->prepare($sql_cari_kk);
    $stmt_kk->execute([':nik' => $nik_terkait]);
    $kk_terkait = $stmt_kk->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Error database: " . $e->getMessage();
}

// ========================================================================
// [2] TAMPILAN HALAMAN
// ========================================================================
$page_title = "Data Kartu Keluarga yang Terkait";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?> - Admin</title>

    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        body { background-color: #f5f6fa; }
        .layout-wrapper { min-height: 100vh; }
        .sidebar {
            width: 240px; min-height: 100vh; background: #111827; color: #e5e7eb;
        }
        .sidebar .brand {
            padding: 1rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar .brand h4 { margin: 0; }
        .sidebar .nav-link { color: #e5e7eb; padding: .6rem 1.25rem; }
        .sidebar .nav-link.active { background: #020617; font-weight: bold; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,.06); }
        .sidebar .nav-section-title {
            padding: .75rem 1.25rem .25rem; opacity: .6; text-transform: uppercase; font-size: .75rem;
        }
        .main-content {
            flex: 1; padding: 1.5rem;
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

    <main class="main-content">

        <h3 class="mb-3"><i class="bi bi-link-45deg"></i> <?= $page_title ?></h3>
        <p class="text-muted">NIK **<?= htmlspecialchars($nik_terkait) ?>** masih terdaftar sebagai **Kepala Keluarga** di KK berikut. Anda harus **mengubah kepala keluarga** atau **menghapus KK** ini sebelum dapat menghapus data warganya.</p>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php elseif (empty($kk_terkait)): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> **Hebat!** NIK **<?= htmlspecialchars($nik_terkait) ?>** tidak ditemukan sebagai Kepala Keluarga di Kartu Keluarga mana pun. Sekarang Anda bisa mencoba menghapusnya kembali.
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> Ditemukan **<?= count($kk_terkait) ?>** Kartu Keluarga yang masih terkait dengan NIK ini:
            </div>
            
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-light">
                            <tr>
                                <th>No. KK</th>
                                <th>Alamat</th>
                                <th>RT/RW</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kk_terkait as $kk): ?>
                                <tr>
                                    <td><?= htmlspecialchars($kk['no_kk']) ?></td>
                                    <td><?= htmlspecialchars($kk['alamat_kk']) ?></td>
                                    <td><?= htmlspecialchars($kk['rt_kk']) ?>/<?= htmlspecialchars($kk['rw_kk']) ?></td>
                                    <td>
                                        <a href="edit_kk.php?no_kk=<?= htmlspecialchars($kk['no_kk']) ?>" class="btn btn-sm btn-info text-white">
                                            <i class="bi bi-pencil"></i> Edit KK
                                        </a>
                                        <a href="hapus_kk.php?no_kk=<?= htmlspecialchars($kk['no_kk']) ?>" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Hapus KK
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="data_warga.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Data Warga
            </a>
        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>