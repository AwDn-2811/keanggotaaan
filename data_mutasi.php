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
$data_mutasi = [];
$rekap_data = []; // Untuk menyimpan hasil rekapitulasi

// Jumlah data yang akan ditampilkan per halaman
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Cek apakah ada query pencarian
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$sql_search_param = '%' . $search_query . '%';

// Fungsi untuk menghitung umur
function hitungUmur($tanggal_lahir) {
    if (!$tanggal_lahir || $tanggal_lahir === '0000-00-00') return null;
    $tgl_lahir = new DateTime($tanggal_lahir);
    $hari_ini = new DateTime();
    $umur = $hari_ini->diff($tgl_lahir);
    return $umur->y;
}


try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // --- 1. Ambil Semua Data (untuk Rekapitulasi & Total Count) ---
    // Gunakan query yang sama untuk menghitung total data dan statistik.
    $rekap_sql = "
        SELECT
            mutasi.id,
            warga.jenis_kelamin,
            warga.tanggal_lahir
        FROM data_mutasi AS mutasi
        LEFT JOIN data_warga AS warga
            ON warga.nik = mutasi.nik_warga
        WHERE 
            mutasi.nik_warga LIKE :search_param 
            OR warga.nama_warga LIKE :search_param
            OR mutasi.nomor_kk LIKE :search_param
    ";
    
    $rekapStmt = $pdo->prepare($rekap_sql);
    $rekapStmt->bindParam(':search_param', $sql_search_param, PDO::PARAM_STR);
    $rekapStmt->execute();
    $all_mutasi_data = $rekapStmt->fetchAll(PDO::FETCH_ASSOC);

    // Hitung rekap data mutasi
    $total_data = count($all_mutasi_data);
    $total_pages = ceil($total_data / $limit);
    $mutasi_laki_laki = 0;
    $mutasi_perempuan = 0;
    $mutasi_dibawah_17 = 0;
    $mutasi_diatas_sama_17 = 0;

    foreach ($all_mutasi_data as $m) {
        if (isset($m['jenis_kelamin'])) {
            if ($m['jenis_kelamin'] === 'Laki-laki') {
                $mutasi_laki_laki++;
            } elseif ($m['jenis_kelamin'] === 'Perempuan') {
                $mutasi_perempuan++;
            }
        }
        $umur = hitungUmur($m['tanggal_lahir']);
        if ($umur !== null) {
            if ($umur < 17) {
                $mutasi_dibawah_17++;
            } else {
                $mutasi_diatas_sama_17++;
            }
        }
    }
    
    $rekap_data = [
        'total_mutasi' => $total_data,
        'mutasi_laki_laki' => $mutasi_laki_laki,
        'mutasi_perempuan' => $mutasi_perempuan,
        'mutasi_dibawah_17' => $mutasi_dibawah_17,
        'mutasi_diatas_sama_17' => $mutasi_diatas_sama_17,
    ];

    // --- 2. Ambil Data untuk Tampilan Tabel (dengan Join, Search, dan Pagination) ---
    $sql = "
        SELECT
            mutasi.id,
            mutasi.nomor_kk,
            mutasi.nik_warga,
            mutasi.jenis_mutasi,
            mutasi.alamat_asal,
            mutasi.alamat_tujuan,
            mutasi.tanggal_mutasi,
            mutasi.keterangan,
            warga.nama_warga
        FROM data_mutasi AS mutasi
        LEFT JOIN data_warga AS warga
            ON warga.nik = mutasi.nik_warga
        WHERE 
            mutasi.nik_warga LIKE :search_param 
            OR warga.nama_warga LIKE :search_param
            OR mutasi.nomor_kk LIKE :search_param
        ORDER BY mutasi.id DESC
        LIMIT :limit OFFSET :offset
    ";
    $query = $pdo->prepare($sql);
    $query->bindParam(':search_param', $sql_search_param, PDO::PARAM_STR);
    $query->bindParam(':limit', $limit, PDO::PARAM_INT);
    $query->bindParam(':offset', $offset, PDO::PARAM_INT);
    $query->execute();
    $data_mutasi = $query->fetchAll(PDO::FETCH_ASSOC);


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
                <a class="nav-link" href="data_kk.php">🧾 Data Kartu Keluarga</a>
                <a class="nav-link active" href="data_mutasi.php">🔁 Data Mutasi</a>
                <a class="nav-link" href="data_user.php">👤 User</a>
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

    <main class="main-content">

        <h3 class="mb-3">🔁 Data Mutasi Warga</h3>
        <p class="text-muted">Berikut adalah daftar seluruh data mutasi yang terdaftar dalam sistem.</p>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-bar-chart-fill"></i> Rekapitulasi Mutasi (Hasil Pencarian: <?= $rekap_data['total_mutasi'] ?> data)
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h5>Total Mutasi: <b><?= $rekap_data['total_mutasi'] ?> data</b></h5>
                        <hr>
                    </div>
                    <div class="col-md-4">
                        <h6>Jenis Kelamin</h6>
                        <p class="mb-1"><i class="bi bi-person-fill"></i> Laki-laki: <b><?= $rekap_data['mutasi_laki_laki'] ?> data</b></p>
                        <p><i class="bi bi-person-fill"></i> Perempuan: <b><?= $rekap_data['mutasi_perempuan'] ?> data</b></p>
                    </div>
                    <div class="col-md-4">
                        <h6>Usia (≥ 17 tahun dianggap dewasa)</h6>
                        <p class="mb-1"><i class="bi bi-people-fill"></i> Warga < 17 tahun: <b><?= $rekap_data['mutasi_dibawah_17'] ?> data</b></p>
                        <p><i class="bi bi-people-fill"></i> Warga ≥ 17 tahun: <b><?= $rekap_data['mutasi_diatas_sama_17'] ?> data</b></p>
                    </div>
                </div>
            </div>
        </div>


        <div class="mb-3 d-flex justify-content-between">
            <a href="tambah_mutasi.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Data Mutasi
            </a>
            <form class="d-flex form-inline" method="GET" action="data_mutasi.php">
                <input class="form-control" type="text" name="search" placeholder="Cari mutasi (NIK/Nama/KK)..." value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit" class="btn btn-primary">Cari</button>
            </form>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Tgl. Mutasi</th>
                            <th>Jenis Mutasi</th>
                            <th>NIK Warga</th>
                            <th>Nama Warga</th>
                            <th>No. KK</th>
                            <th>Alamat Asal</th>
                            <th>Alamat Tujuan</th>
                            <th>Keterangan</th>
                            <th width="150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($data_mutasi) === 0): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">Tidak ada data mutasi yang ditemukan.</td>
                            </tr>
                        <?php else: ?>
                            <?php $start_index = $offset + 1; ?>
                            <?php foreach ($data_mutasi as $index => $mutasi): ?>
                                <tr>
                                    <td><?= $start_index + $index ?></td>
                                    <td><?= htmlspecialchars($mutasi['tanggal_mutasi'] ?? '-') ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($mutasi['jenis_mutasi'] ?? '-') ?></span></td>
                                    <td><?= htmlspecialchars($mutasi['nik_warga'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($mutasi['nama_warga'] ?? 'NIK tidak terdaftar') ?></td>
                                    <td><?= htmlspecialchars($mutasi['nomor_kk'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($mutasi['alamat_asal'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($mutasi['alamat_tujuan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($mutasi['keterangan'] ?? '-') ?></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="detail_mutasi.php?id=<?= $mutasi['id'] ?>" class="btn btn-info" title="Detail">
                                                <i class="bi bi-info-circle"></i> Detail
                                            </a>
                                            <a href="hapus_mutasi.php?id=<?= $mutasi['id'] ?>" class="btn btn-danger" title="Hapus"
                                               onclick="return confirm('Yakin ingin menghapus data mutasi ID: <?= $mutasi['id'] ?>? Tindakan ini tidak dapat dibatalkan.');">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php 
                            $base_url = "data_mutasi.php?limit={$limit}" . (empty($search_query) ? '' : "&search=" . urlencode($search_query));
                        ?>
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $base_url ?>&page=<?= $page - 1 ?>" tabindex="-1">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
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
    </main>
</div>

</body>
</html>