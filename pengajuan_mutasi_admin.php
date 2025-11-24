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

$error_message   = '';
$success_message = '';
$mutasi_list     = [];

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 1) Proses aksi approve / reject jika ada
    if (isset($_GET['action'], $_GET['id'])) {
        $action = $_GET['action'];
        $id     = (int) $_GET['id'];

        if ($id > 0 && in_array($action, ['approve', 'reject'])) {
            $status_baru = ($action === 'approve') ? 'Disetujui' : 'Ditolak';

            $sqlU = "UPDATE data_mutasi SET status = :status WHERE id = :id";
            $stmtU = $pdo->prepare($sqlU);
            $stmtU->execute([
                ':status' => $status_baru,
                ':id'     => $id
            ]);

            $success_message = "Status pengajuan mutasi ID {$id} berhasil diubah menjadi {$status_baru}.";
        }
    }

    // 2) Ambil daftar semua pengajuan mutasi + nama warga
    $sql = "SELECT m.*, w.nama_warga 
            FROM data_mutasi m
            LEFT JOIN data_warga w ON m.nik_warga = w.nik
            ORDER BY 
                FIELD(m.status, 'Pending','Disetujui','Ditolak'),
                m.tanggal_mutasi DESC,
                m.id DESC";
    $stmt = $pdo->query($sql);
    $mutasi_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = 'Error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengajuan Mutasi - Admin</title>

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
            <small class="text-muted">Kelola Pengajuan Mutasi</small>
        </div>

        <div class="flex-grow-1">
            <div class="nav-section-title">Menu Utama</div>
            <nav class="nav flex-column">
                <a class="nav-link" href="dashboard_admin.php">
                    🏠 Dashboard Admin
                </a>
                <a class="nav-link active" href="pengajuan_mutasi_admin.php">
                    🔁 Pengajuan Mutasi
                </a>
                <!-- Tambah menu admin lain di sini kalau perlu -->
                <!-- <a class="nav-link" href="data_warga.php">👥 Data Warga</a> -->
                <!-- <a class="nav-link" href="data_mutasi.php">📄 Data Mutasi</a> -->
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
                <h3 class="mb-0">Kelola Pengajuan Mutasi</h3>
                <small class="text-muted">
                    Admin dapat menyetujui atau menolak pengajuan mutasi yang diajukan warga.
                </small>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?= $success_message ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                Daftar Pengajuan Mutasi
            </div>
            <div class="card-body">
                <?php if (empty($mutasi_list)): ?>
                    <p class="text-muted mb-0">Belum ada pengajuan mutasi yang tercatat.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>NIK</th>
                                    <th>Nama Warga</th>
                                    <th>Jenis Mutasi</th>
                                    <th>Alamat Asal</th>
                                    <th>Alamat Tujuan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th style="width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($mutasi_list as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['id']) ?></td>
                                    <td><?= htmlspecialchars($m['nik_warga']) ?></td>
                                    <td><?= htmlspecialchars($m['nama_warga'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($m['jenis_mutasi']) ?></td>
                                    <td><?= htmlspecialchars($m['alamat_asal']) ?></td>
                                    <td><?= htmlspecialchars($m['alamat_tujuan']) ?></td>
                                    <td><?= htmlspecialchars($m['tanggal_mutasi']) ?></td>
                                    <td>
                                        <?php
                                            $status = $m['status'];
                                            if ($status === 'Pending') {
                                                echo '<span class="badge bg-warning text-dark">Pending</span>';
                                            } elseif ($status === 'Disetujui') {
                                                echo '<span class="badge bg-success">Disetujui</span>';
                                            } elseif ($status === 'Ditolak') {
                                                echo '<span class="badge bg-danger">Ditolak</span>';
                                            } else {
                                                echo htmlspecialchars($status);
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($m['status'] === 'Pending'): ?>
                                            <a href="pengajuan_mutasi_admin.php?action=approve&id=<?= $m['id'] ?>"
                                               class="btn btn-success btn-sm mb-1"
                                               onclick="return confirm('Setujui pengajuan mutasi ID <?= $m['id'] ?>?');">
                                                Setujui
                                            </a>
                                            <a href="pengajuan_mutasi_admin.php?action=reject&id=<?= $m['id'] ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Tolak pengajuan mutasi ID <?= $m['id'] ?>?');">
                                                Tolak
                                            </a>
                                        <?php else: ?>
                                            <small class="text-muted">Tidak ada aksi</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>

</body>
</html>
