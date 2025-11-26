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
$data_user = [];

// Pagination
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$search_param = '%' . $search_query . '%';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Count total data user (filter search)
    $countSql = "SELECT COUNT(*) FROM users 
                 WHERE nama_depan LIKE :s 
                 OR nama_belakang LIKE :s 
                 OR email LIKE :s";

    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([':s' => $search_param]);
    $total_data = $countStmt->fetchColumn();
    $total_pages = ceil($total_data / $limit);

    // Ambil data user untuk tabel
    $sql = "SELECT * FROM users
            WHERE nama_depan LIKE :s 
            OR nama_belakang LIKE :s 
            OR email LIKE :s
            ORDER BY id ASC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':s', $search_param, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $data_user = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
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
            color: #ffffffff;
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

    <!-- SIDEBAR -->
    <aside class="sidebar d-flex flex-column">
        <div class="brand">
            <h4>Panel Admin</h4>
            <small>Sistem Pendataan Warga</small>
        </div>

        <div class="flex-grow-1">
        <div class="nav-section-title">Menu Utama</div>
        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link" href="dashboard_admin.php">🏠 Dasbor</a>
            <a class="nav-link" href="data_warga.php">👥 Data Warga</a>
            <a class="nav-link" href="data_kk.php">🧾 Data Kartu Keluarga</a>
            <a class="nav-link" href="data_mutasi.php">🔁 Data Mutasi</a>
            <a class="nav-link active" href="data_user.php">👤 User</a>
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
            <small>Login sebagai:<br><b><?= htmlspecialchars($admin_name) ?></b></small>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <h3 class="mb-3">👤 Data User Sistem</h3>
        <p class="text-muted">Berikut adalah daftar seluruh user terdaftar dalam sistem.</p>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <!-- Search + Add Button -->
        <div class="mb-3 d-flex justify-content-between">
            <a href="tambah_user.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah User
            </a>

            <form class="d-flex" method="GET">
                <input class="form-control" type="text" name="search"
                       placeholder="Cari user (nama/email)..." value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit" class="btn btn-dark ms-2">Cari</button>
            </form>
        </div>

        <!-- TABLE -->
        <div class="card shadow-sm">
            <div class="card-body">

                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nama Depan</th>
                            <th>Nama Belakang</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th width="150px">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (count($data_user) === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">Tidak ada data user ditemukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php $start_index = $offset + 1; ?>
                        <?php foreach ($data_user as $index => $user): ?>
                            <tr>
                                <td><?= $start_index + $index ?></td>
                                <td><?= htmlspecialchars($user['nama_depan']) ?></td>
                                <td><?= htmlspecialchars($user['nama_belakang']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge bg-primary"><?= $user['role'] ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-info">Edit</a>
                                        <a href="hapus_user.php?id=<?= $user['id'] ?>" class="btn btn-danger"
                                           onclick="return confirm('Yakin hapus user ini?');">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php
                        $base_url = "data_user.php?limit={$limit}" .
                                    (!empty($search_query) ? "&search=" . urlencode($search_query) : "");
                        ?>

                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $base_url ?>&page=<?= $page - 1 ?>">Previous</a>
                        </li>

                        <?php for ($i=1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $base_url ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $base_url ?>&page=<?= $page + 1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>

            </div>
        </div>

        <div class="mt-4 alert alert-info">
            <h5>📊 Statistik User</h5>
            <p><strong>Total User:</strong> <?= $total_data ?> orang</p>
        </div>

    </main>
</div>

</body>
</html>
