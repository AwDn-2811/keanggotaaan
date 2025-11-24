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

$nik = $_GET['nik']; // NIK yang akan dihapus

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

$error_message = '';
$success_message = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // ====================================================================
    // [ LOGIKA YANG DIPERBAIKI ]
    // Hapus data hanya jika ada parameter 'action=confirm_delete' di URL
    // ====================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'confirm_delete') {
        
        $sql = "DELETE FROM data_warga WHERE nik = :nik";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nik' => $nik]);

        // Redirect setelah data berhasil dihapus
        header("Location: data_warga.php?status=deleted&nik=" . htmlspecialchars($nik)); 
        exit();
    }

} catch (PDOException $e) {
    // Tangani error database jika terjadi
    $error_message = "Error: Gagal menghapus data. Pastikan tidak ada data lain (seperti Kartu Keluarga) yang terkait dengan NIK ini. Detail: " . $e->getMessage();
}

// ====================================================================
// [ PENGATURAN TAMPILAN ]
// ====================================================================
$page_title = "Hapus Data Warga";
$page_description = "Anda akan menghapus data warga dengan NIK: " . htmlspecialchars($nik) . ". Tindakan ini tidak dapat dibatalkan.";
$active_link = "data_warga.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?> - Admin</title>

    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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
        
        /* Menghapus .btn-group styling yang tidak diperlukan */
        .btn-group .btn {
            font-size: 13px;
        }
        .btn-group .btn i {
            margin-right: 5px;
        }
        .text-muted {
            font-size: 14px;
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

        <h3 class="mb-3">🚨 <?= $page_title ?></h3>
        <p class="text-muted"><?= $page_description ?></p>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger card shadow-sm p-4">
                <h4><i class="bi bi-exclamation-triangle"></i> Gagal Hapus Data</h4>
                <p><?= $error_message ?></p>
                <a href="data_warga.php" class="btn btn-secondary mt-2">
                    <i class="bi bi-arrow-left"></i> Kembali ke Data Warga
                </a>
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Konfirmasi Penghapusan</h5>
                    <p class="card-text">Apakah Anda **YAKIN** ingin menghapus data warga dengan **NIK: <?= htmlspecialchars($nik) ?>**?</p>
                    <p class="text-danger">Penghapusan ini bersifat permanen dan tidak dapat dibatalkan.</p>

                    <div class="btn-group mt-3">
                        <a href="hapus_warga.php?nik=<?= htmlspecialchars($nik) ?>&action=confirm_delete" 
                           class="btn btn-danger">
                            <i class="bi bi-trash"></i> YA, HAPUS PERMANEN
                        </a>
                        
                        <a href="data_warga.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> BATAL
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </main>
</div>

</body>
</html>