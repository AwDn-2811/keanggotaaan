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
$success_message = '';
$kk_detail = null;
$anggota = [];
$warga_available = []; // Daftar warga yang belum terikat KK

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
        $kk_id = 0; // Set 0 jika tidak ditemukan
    } else {
        $kk_id = $kk_detail['id'];
        
        // -------- 2. Ambil daftar warga yang BELUM terikat KK (atau Kepala KK itu sendiri) --------
        $stmtWargaAvailable = $pdo->prepare("
            SELECT id, nik, nama_warga FROM data_warga 
            WHERE kk_id IS NULL OR kk_id = 0 
            OR id = :id_kepala_kk 
            ORDER BY nama_warga ASC
        ");
        $stmtWargaAvailable->execute([':id_kepala_kk' => $kk_detail['id_kepala']]);
        $warga_available = $stmtWargaAvailable->fetchAll(PDO::FETCH_ASSOC);

        // -------- 3. Ambil data anggota keluarga (warga yang kk_id = $kk_id) --------
        // Menggunakan ORDER BY nama_warga ASC agar kueri aman jika kolom status_hubungan_kk belum dibuat
        // Jika kolom status_hubungan_kk sudah dibuat dan ingin Kepala Keluarga muncul di atas:
        // Ganti ORDER BY dengan: ORDER BY FIELD(status_hubungan_kk, 'Kepala Keluarga', 'Istri', 'Anak', 'Cucu', 'Orang Tua', 'Anggota Lain'), nama_warga ASC
        $stmtAnggota = $pdo->prepare("
            SELECT * FROM data_warga 
            WHERE kk_id = :kk_id 
            ORDER BY nama_warga ASC
        "); 

        $stmtAnggota->execute([':kk_id' => $kk_id]);
        $anggota = $stmtAnggota->fetchAll(PDO::FETCH_ASSOC);
    }
    

    // -------- 4. Proses Hapus Anggota --------
    if (isset($_GET['action']) && $_GET['action'] === 'hapus' && isset($_GET['nik_anggota'])) {
        $nik_anggota = $_GET['nik_anggota'];

        // Cek apakah yang dihapus adalah Kepala Keluarga
        if ($kk_detail && $kk_detail['nik_kepala'] == $nik_anggota) {
            $error_message = "Tidak dapat menghapus Kepala Keluarga. Silakan hapus Kartu Keluarga secara keseluruhan, atau ubah Kepala Keluarga terlebih dahulu.";
        } else {
            // Update data_warga: SET kk_id = NULL
            $upd = $pdo->prepare("
                UPDATE data_warga SET kk_id = NULL, status_hubungan_kk = NULL WHERE nik = :nik_anggota AND kk_id = :kk_id
            ");

            if ($upd->execute([':nik_anggota' => $nik_anggota, ':kk_id' => $kk_id])) {
                $success_message = "Anggota keluarga (NIK: $nik_anggota) berhasil dikeluarkan dari Kartu Keluarga ini.";
                // Refresh data anggota
                header("Location: detail_kk.php?nomor_kk=$nomor_kk&status=removed");
                exit();
            } else {
                $error_message = "Gagal mengeluarkan anggota keluarga.";
            }
        }
    }


    // -------- 5. Proses Tambah Anggota --------
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_warga_baru = trim($_POST['id_warga'] ?? '');
        $hubungan = trim($_POST['hubungan'] ?? '');

        if ($id_warga_baru == '' || $hubungan == '' || $kk_id == 0) {
            $error_message = "Warga dan Hubungan wajib dipilih.";
        } else {
            // Update data_warga: SET kk_id = $kk_id
            $upd = $pdo->prepare("
                UPDATE data_warga 
                SET kk_id = :kk_id, status_hubungan_kk = :hubungan 
                WHERE id = :id_warga_baru
            ");

            if ($upd->execute([':kk_id' => $kk_id, ':hubungan' => $hubungan, ':id_warga_baru' => $id_warga_baru])) {
                // Redirect ke halaman detail KK setelah berhasil update
                header("Location: detail_kk.php?nomor_kk=$nomor_kk&status=added");
                exit();
            } else {
                $error_message = "Gagal menambahkan anggota keluarga.";
            }
        }
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
                <a class="nav-link" href="data_user.php">👤 User</a>
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

        <h3 class="mb-4">🧾 Detail & Anggota Kartu Keluarga</h3>
        
        <div class="d-flex mb-4 gap-2">
            <a href="data_kk.php" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Kembali ke Data KK
            </a>
            <?php if ($kk_detail): ?>
            <a href="ubah_kk.php?id=<?= $kk_detail['id'] ?>" class="btn btn-warning text-dark me-2">
                <i class="bi bi-pencil-square"></i> Ubah Detail KK
            </a>
            <?php endif; ?>
        </div>
        

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle"></i> **Error:** <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'added'): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle"></i> Anggota baru berhasil ditambahkan!
            </div>
        <?php elseif (isset($_GET['status']) && $_GET['status'] == 'removed'): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle"></i> Anggota berhasil dikeluarkan.
            </div>
        <?php endif; ?>


        <?php if ($kk_detail): ?>
            <div class="row mb-5">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">A. Data Kartu Keluarga</h5>
                        </div>
                        <div class="card-body">
                            <div class="detail-item"><strong>Nomor KK</strong> : <?= htmlspecialchars($kk_detail['nomor_kk']) ?></div>
                            <div class="detail-item"><strong>Kepala Keluarga</strong> : <?= htmlspecialchars($kk_detail['kepala_keluarga']) ?></div>
                            <div class="detail-item"><strong>NIK Kepala</strong> : <?= htmlspecialchars($kk_detail['nik_kepala']) ?></div>
                            <div class="detail-item"><strong>Alamat</strong> : <?= htmlspecialchars($kk_detail['alamat']) ?></div>
                            <div class="detail-item"><strong>RT/RW</strong> : <?= htmlspecialchars($kk_detail['rt']) ?>/<?= htmlspecialchars($kk_detail['rw']) ?></div>
                            <div class="detail-item"><strong>Desa/Kel.</strong> : <?= htmlspecialchars($kk_detail['desa_kelurahan']) ?></div>
                            <div class="detail-item"><strong>Kecamatan</strong> : <?= htmlspecialchars($kk_detail['kecamatan']) ?></div>
                            <div class="detail-item"><strong>Kab./Kota</strong> : <?= htmlspecialchars($kk_detail['kabupaten']) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-5">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">B. Tambah Anggota Keluarga</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label for="id_warga" class="form-label">Warga (Belum Punya KK) *</label>
                            <select name="id_warga" id="id_warga" class="form-select" required>
                                <option value="">-- Pilih Warga --</option>
                                <?php foreach ($warga_available as $w): ?>
                                    <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['nama_warga']) ?> (NIK: <?= htmlspecialchars($w['nik']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted">Hanya menampilkan warga yang belum memiliki KK atau warga yang NIK-nya sama dengan Kepala Keluarga (jika Kepala KK belum punya status hubungan).</div>
                        </div>
                        <div class="col-md-4">
                            <label for="hubungan" class="form-label">Hubungan dalam Keluarga *</label>
                            <select name="hubungan" id="hubungan" class="form-select" required>
                                <option value="">-- Pilih Hubungan --</option>
                                <option value="Istri">Istri</option>
                                <option value="Anak">Anak</option>
                                <option value="Cucu">Cucu</option>
                                <option value="Orang Tua">Orang Tua</option>
                                <option value="Anggota Lain">Anggota Lain</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle"></i> Tambahkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">C. Daftar Anggota Keluarga (Total: <?= count($anggota) ?>)</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>NIK</th>
                                <th>Nama Warga</th>
                                <th>Hubungan</th>
                                <th>Jenis Kelamin</th>
                                <th>Tgl. Lahir</th>
                                <th>Status Perkawinan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($anggota)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Belum ada anggota keluarga terdaftar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($anggota as $index => $a): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($a['nik']) ?></td>
                                        <td><?= htmlspecialchars($a['nama_warga']) ?></td>
                                        <td><?= htmlspecialchars($a['status_hubungan_kk'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($a['jenis_kelamin'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($a['tanggal_lahir'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($a['status_perkawinan'] ?? '-') ?></td>
                                        <td>
                                            <?php if (($a['status_hubungan_kk'] ?? '') !== 'Kepala Keluarga'): ?>
                                                <a href="?nomor_kk=<?= $nomor_kk ?>&action=hapus&nik_anggota=<?= $a['nik'] ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Yakin ingin mengeluarkan <?= htmlspecialchars($a['nama_warga']) ?> dari KK ini? Data warga tetap ada di Data Warga.');">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Kepala KK</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php endif; ?>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>