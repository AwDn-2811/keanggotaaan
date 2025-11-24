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

$error_message = '';
$data_kk = [];
$search_query = '';

// Jumlah data yang akan ditampilkan per halaman
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Cek apakah ada query pencarian
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = '%' . $_GET['search'] . '%';
} else {
    $search_query = '%';
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Ambil data kartu keluarga berdasarkan pencarian dan pagination
    $stmt = $pdo->prepare("SELECT * FROM data_kk WHERE nomor_kk LIKE :search_query OR kepala_keluarga LIKE :search_query ORDER BY nomor_kk ASC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':search_query', $search_query, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data_kk = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hitung total data kartu keluarga
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM data_kk WHERE nomor_kk LIKE :search_query OR kepala_keluarga LIKE :search_query");
    $countStmt->bindParam(':search_query', $search_query, PDO::PARAM_STR);
    $countStmt->execute();
    $total_records = $countStmt->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    // Hitung rekap data kartu keluarga
    $rekapStmt = $pdo->prepare("SELECT
                                    COUNT(*) AS total_kartu_keluarga
                                  FROM data_kk WHERE nomor_kk LIKE :search_query OR kepala_keluarga LIKE :search_query");
    $rekapStmt->bindParam(':search_query', $search_query, PDO::PARAM_STR);
    $rekapStmt->execute();
    $rekap_data = $rekapStmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kartu Keluarga - Admin</title>
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

        .btn-group {
            margin-top: 20px;
        }

        .text-muted {
            font-size: 14px;
        }

        .btn-group .btn {
            font-size: 13px;
        }

        .btn-group .btn i {
            margin-right: 5px;
        }

        .btn-group .btn:hover {
            background-color: #0056b3;
            border-color: #0056b3;
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
                <a class="nav-link" href="data_warga.php">👥 Data Warga</a>
                <a class="nav-link active" href="data_kk.php">🧾 Data Kartu Keluarga</a>
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

        <h3 class="mb-3">🧾 Data Kartu Keluarga</h3>
        <p class="text-muted">Berikut adalah daftar seluruh data kartu keluarga yang terdaftar dalam sistem.</p>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>



        <!-- Tabel Rekap Data Kartu Keluarga -->
        <div class="mb-4">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Total Kartu Keluarga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= $rekap_data['total_kartu_keluarga'] ?> Kartu Keluarga</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

                <!-- Search Form -->
        <form method="GET" class="mb-3">
            <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="form-control" placeholder="Cari berdasarkan nomor KK atau nama kepala keluarga">
            <button type="submit" class="btn btn-primary mt-2">Cari</button>
        </form>

        <!-- Tabel Data Kartu Keluarga -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="mb-3">
                    <a href="tambah_kk.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Data KK
                    </a>
                </div>

                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Nomor KK</th>
                            <th>Nama Kepala Keluarga</th>
                            <th>Alamat</th>
                            <th>RT</th>
                            <th>RW</th>
                            <th width="220px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($data_kk) === 0): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">Tidak ada data kartu keluarga.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data_kk as $kk): ?>
                                <tr>
                                    <td><?= htmlspecialchars($kk['nomor_kk']) ?></td>
                                    <td><?= htmlspecialchars($kk['kepala_keluarga']) ?></td>
                                    <td><?= htmlspecialchars($kk['alamat']) ?></td>
                                    <td><?= htmlspecialchars($kk['rt']) ?></td>
                                    <td><?= htmlspecialchars($kk['rw']) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="detail_kk.php?nomor_kk=<?= $kk['nomor_kk'] ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-info-circle"></i> Detail
                                            </a>
                                            <a href="edit_kk.php?nomor_kk=<?= $kk['nomor_kk'] ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil-square"></i> Ubah
                                            </a>
                                            <a href="ubah_kk.php?nomor_kk=<?= $kk['nomor_kk'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-person-plus"></i> Ubah Anggota
                                            </a>
                                            <a href="hapus_kk.php?nomor_kk=<?= $kk['nomor_kk'] ?>" class="btn btn-sm btn-danger"
                                               onclick="return confirm('Yakin ingin menghapus?')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&limit=<?= $limit ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>">Previous</a>
                </li>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&limit=<?= $limit ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>">Next</a>
                </li>
            </ul>
        </nav>

    </main>
</div>

</body>
</html>
