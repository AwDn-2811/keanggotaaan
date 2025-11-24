<?php
session_start();

// Hanya untuk warga yang sudah login
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'warga') {
    header("Location: login.php");
    exit();
}

$nik        = $_SESSION['nik'] ?? null;
$user_name  = $_SESSION['user_name'] ?? '';

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

$error_message   = '';
$success_message = '';
$warga           = null;
$riwayat_mutasi  = [];

if (!$nik) {
    $error_message = 'NIK tidak ditemukan di session. Silakan login ulang atau hubungi admin.';
} else {
    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Ambil data warga (untuk alamat_asal)
        $sqlW = "SELECT * FROM data_warga WHERE nik = :nik LIMIT 1";
        $stmtW = $pdo->prepare($sqlW);
        $stmtW->execute([':nik' => $nik]);
        $warga = $stmtW->fetch(PDO::FETCH_ASSOC);

        if (!$warga) {
            $error_message = "Data warga dengan NIK {$nik} tidak ditemukan di data_warga.";
        } else {
            // Jika form dikirim
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $jenis_mutasi    = trim($_POST['jenis_mutasi'] ?? '');
                $alamat_asal     = trim($_POST['alamat_asal'] ?? '');
                $alamat_tujuan   = trim($_POST['alamat_tujuan'] ?? '');
                $tanggal_mutasi  = trim($_POST['tanggal_mutasi'] ?? '');
                $keterangan      = trim($_POST['keterangan'] ?? '');

                // Validasi sederhana
                if ($jenis_mutasi === '' || $alamat_tujuan === '' || $tanggal_mutasi === '') {
                    $error_message = 'Jenis mutasi, alamat tujuan, dan tanggal mutasi wajib diisi.';
                } else {
                    // Insert ke data_mutasi dengan status Pending
                    $sqlInsert = "INSERT INTO data_mutasi 
                        (nik_warga, jenis_mutasi, alamat_asal, alamat_tujuan, tanggal_mutasi, keterangan, status)
                        VALUES 
                        (:nik_warga, :jenis_mutasi, :alamat_asal, :alamat_tujuan, :tanggal_mutasi, :keterangan, :status)";

                    $stmtI = $pdo->prepare($sqlInsert);
                    $stmtI->execute([
                        ':nik_warga'      => $nik,
                        ':jenis_mutasi'   => $jenis_mutasi,
                        ':alamat_asal'    => $alamat_asal,
                        ':alamat_tujuan'  => $alamat_tujuan,
                        ':tanggal_mutasi' => $tanggal_mutasi,
                        ':keterangan'     => $keterangan,
                        ':status'         => 'Pending'
                    ]);

                    $success_message = 'Pengajuan mutasi berhasil dikirim. Menunggu persetujuan admin.';
                }
            }

            // Ambil riwayat mutasi milik warga ini
            $sqlM = "SELECT * FROM data_mutasi 
                     WHERE nik_warga = :nik 
                     ORDER BY tanggal_mutasi DESC, id DESC";
            $stmtM = $pdo->prepare($sqlM);
            $stmtM->execute([':nik' => $nik]);
            $riwayat_mutasi = $stmtM->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch (PDOException $e) {
        $error_message = 'Error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengajuan Mutasi - Sistem Pendataan Warga</title>

    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        body { background-color: #f5f6fa; }
        .layout-wrapper { min-height: 100vh; }
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

    <!-- SIDEBAR -->
    <aside class="sidebar d-flex flex-column">
        <div class="brand">
            <h4>Sistem Warga</h4>
            <small class="text-muted">Dashboard Warga</small>
        </div>

        <div class="flex-grow-1">
            <div class="nav-section-title">Menu Utama</div>
            <nav class="nav flex-column">
                <a class="nav-link" href="dashboard_warga.php">
                    🏠 Dasbor
                </a>
                <a class="nav-link" href="kk_saya.php">
                    📄 Kartu Keluarga Saya
                </a>
                <a class="nav-link active" href="pengajuan_mutasi.php">
                    🔁 Pengajuan Mutasi
                </a>
                <a class="nav-link" href="pengaturan_akun.php">
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
                <span class="text-muted">NIK: <?= htmlspecialchars($nik ?? '-') ?></span>
            </small>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <div class="mb-4">
            <h3 class="mb-0">Pengajuan Mutasi Online</h3>
            <small class="text-muted">
                Isi formulir di bawah untuk mengajukan mutasi alamat / status. Pengajuan akan diproses oleh admin RT/RW.
            </small>
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

        <?php if ($warga): ?>

            <!-- FORM PENGAJUAN -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    Form Pengajuan Mutasi
                </div>
                <div class="card-body">
                    <form method="POST" action="pengajuan_mutasi.php">
                        <div class="mb-3">
                            <label class="form-label">Jenis Mutasi</label>
                            <select name="jenis_mutasi" class="form-select" required>
                                <option value="">-- Pilih Jenis Mutasi --</option>
                                <option value="Pindah Alamat">Pindah Alamat</option>
                                <option value="Pindah Masuk">Pindah Masuk</option>
                                <option value="Pindah Keluar">Pindah Keluar</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Asal</label>
                            <input type="text" class="form-control" name="alamat_asal"
                                   value="<?= htmlspecialchars($warga['alamat']) ?>" readonly>
                            <div class="form-text">
                                Alamat asal diambil dari data warga yang sudah terdaftar.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Tujuan</label>
                            <textarea name="alamat_tujuan" class="form-control" rows="2" required
                                      placeholder="Masukkan alamat tujuan lengkap"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Mutasi (rencana)</label>
                            <input type="date" name="tanggal_mutasi" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan (opsional)</label>
                            <textarea name="keterangan" class="form-control" rows="2"
                                      placeholder="Misal: alasan pindah, catatan tambahan, dsb."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Kirim Pengajuan
                        </button>
                    </form>
                </div>
            </div>

            <!-- RIWAYAT PENGAJUAN MUTASI -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    Riwayat Mutasi & Status Pengajuan
                </div>
                <div class="card-body">
                    <?php if (empty($riwayat_mutasi)): ?>
                        <p class="text-muted mb-0">Belum ada pengajuan mutasi yang tercatat.</p>
                    <?php else: ?>
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Jenis Mutasi</th>
                                    <th>Alamat Asal</th>
                                    <th>Alamat Tujuan</th>
                                    <th>Tanggal Mutasi</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($riwayat_mutasi as $m): ?>
                                <tr>
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
