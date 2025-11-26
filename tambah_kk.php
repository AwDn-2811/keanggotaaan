<?php
session_start();

// Hanya admin yang boleh akses
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['user_name'];

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

$error = '';
$success = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // Ambil calon kepala keluarga dari data_warga
    $warga_stmt = $pdo->query("SELECT id, nik, nama_warga FROM data_warga ORDER BY nama_warga ASC");
    $warga_list = $warga_stmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ambil input
        $nomor_kk       = trim($_POST['nomor_kk'] ?? '');
        $id_kepala      = (int)($_POST['id_kepala'] ?? 0);
        $alamat         = trim($_POST['alamat'] ?? '');
        $desa_kelurahan = trim($_POST['desa_kelurahan'] ?? '');
        $kecamatan      = trim($_POST['kecamatan'] ?? '');
        $kabupaten      = trim($_POST['kabupaten'] ?? '');
        $provinsi       = trim($_POST['provinsi'] ?? '');
        $negara         = trim($_POST['negara'] ?? 'Indonesia');
        $rt             = trim($_POST['rt'] ?? '');
        $rw             = trim($_POST['rw'] ?? '');
        $kode_pos       = trim($_POST['kode_pos'] ?? '');

        // Validasi dasar
        if (
            $nomor_kk === '' || $id_kepala === 0 || $alamat === '' ||
            $desa_kelurahan === '' || $kecamatan === '' || $kabupaten === '' ||
            $provinsi === '' || $negara === '' || $rt === '' || $rw === ''
        ) {
            $error = "Semua field wajib diisi.";
        } elseif (!preg_match('/^[0-9]{16}$/', $nomor_kk)) {
            $error = "Nomor KK harus 16 digit angka.";
        } else {
            // Cek KK unik
            $cek = $pdo->prepare("SELECT COUNT(*) FROM data_kk WHERE nomor_kk = :kk");
            $cek->execute([':kk' => $nomor_kk]);
            if ((int)$cek->fetchColumn() > 0) {
                $error = "Nomor KK sudah terdaftar.";
            } else {
                // Ambil data kepala keluarga
                $kep = $pdo->prepare("SELECT id, nik, nama_warga, kk_id FROM data_warga WHERE id = :id LIMIT 1");
                $kep->execute([':id' => $id_kepala]);
                $kepala = $kep->fetch();

                if (!$kepala) {
                    $error = "ID Kepala Keluarga tidak valid.";
                } elseif (!empty($kepala['kk_id'])) {
                    // Jika kepala sudah punya KK, tolak (biar konsisten)
                    $error = "Warga ini sudah terdaftar di KK lain (ID KK: " . (int)$kepala['kk_id'] . ").";
                } else {
                    // Insert + tautkan kepala → gunakan transaksi
                    $pdo->beginTransaction();

                    try {
                        // Insert KK
                        $ins = $pdo->prepare("
                            INSERT INTO data_kk
                            (nomor_kk, id_kepala, kepala_keluarga, nik_kepala,
                             alamat, desa_kelurahan, kecamatan, kabupaten,
                             provinsi, negara, rt, rw, kode_pos)
                            VALUES
                            (:nomor_kk, :id_kepala, :kepala_keluarga, :nik_kepala,
                             :alamat, :desa_kelurahan, :kecamatan, :kabupaten,
                             :provinsi, :negara, :rt, :rw, :kode_pos)
                        ");

                        $ins->execute([ 
                            ':nomor_kk'        => $nomor_kk,
                            ':id_kepala'       => $id_kepala,
                            ':kepala_keluarga' => $kepala['nama_warga'],
                            ':nik_kepala'      => $kepala['nik'],
                            ':alamat'          => $alamat,
                            ':desa_kelurahan'  => $desa_kelurahan,
                            ':kecamatan'       => $kecamatan,
                            ':kabupaten'       => $kabupaten,
                            ':provinsi'        => $provinsi,
                            ':negara'          => $negara,
                            ':rt'              => $rt,
                            ':rw'              => $rw,
                            ':kode_pos'        => ($kode_pos !== '' ? $kode_pos : null),
                        ]);

                        $kkIdBaru = (int)$pdo->lastInsertId();

                        // Tautkan kepala keluarga jadi anggota KK (isi kk_id di data_warga)
                        $upd = $pdo->prepare("UPDATE data_warga SET kk_id = :kk_id WHERE id = :id");
                        $upd->execute([ 
                            ':kk_id' => $kkIdBaru,
                            ':id'    => $id_kepala
                        ]);

                        $pdo->commit();

                        // Redirect setelah data berhasil disimpan
                        header("Location: data_kk.php?status=added");
                        exit();
                    } catch (Throwable $txe) {
                        $pdo->rollBack();
                        $error = "Gagal menyimpan KK: " . $txe->getMessage();
                    }
                }
            }
        }
    }

} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

$page_title = "Tambah Kartu Keluarga";
$active_link = "data_kk.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* Gaya Konsisten Sidebar */
        body { background-color: #f5f6fa; }
        .layout-wrapper { min-height: 100vh; }

        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #111827;
            color: #e5e7eb;
            flex-shrink: 0; /* Penting agar tidak menyusut */
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
                <a class="nav-link text-danger" href="logout.php">
                    🚪 Logout
                </a>
            </nav>

        </div>

        <div class="p-3 border-top border-secondary">
            <small>Login sebagai:<br><b><?= htmlspecialchars($user_name) ?></b></small>
        </div>
    </aside>

    <main class="main-content">

        <h3 class="mb-4"><i class="bi bi-person-badge"></i> <?= $page_title ?></h3>


        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle"></i> **Error:** <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle"></i> **Sukses:** <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Form Input Kartu Keluarga Baru</h5>
            </div>
            <div class="card-body">
                <form method="POST" autocomplete="off" class="row g-3">
                    
                    <h4 class="mt-4 col-12 text-primary">A. Data Kartu & Kepala Keluarga</h4>

                    <div class="col-md-6">
                        <label for="nomor_kk" class="form-label">Nomor Kartu Keluarga</label>
                        <input type="text" name="nomor_kk" id="nomor_kk" class="form-control" maxlength="16" pattern="\d{16}" required 
                               value="<?= htmlspecialchars($_POST['nomor_kk'] ?? '') ?>">
                        <div class="form-text text-muted">Masukkan 16 digit angka (tanpa spasi/tanda baca).</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="id_kepala" class="form-label">Kepala Keluarga (pilih dari Data Warga)</label>
                        <select name="id_kepala" id="id_kepala" class="form-select" required>
                            <option value="">- Pilih Kepala Keluarga -</option>
                            <?php 
                            $selected_id = (int)($_POST['id_kepala'] ?? 0);
                            foreach ($warga_list as $w): ?>
                                <option value="<?= (int)$w['id'] ?>"
                                    <?= $selected_id === (int)$w['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($w['nama_warga']) ?> (NIK: <?= htmlspecialchars($w['nik']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <h4 class="mt-5 col-12 text-primary">B. Data Alamat KK</h4>

                    <div class="col-12">
                        <label for="alamat" class="form-label">Alamat Lengkap</label>
                        <textarea name="alamat" id="alamat" class="form-control" rows="2" required><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-4">
                        <label for="desa_kelurahan" class="form-label">Desa/Kelurahan</label>
                        <input type="text" name="desa_kelurahan" id="desa_kelurahan" class="form-control" required
                               value="<?= htmlspecialchars($_POST['desa_kelurahan'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="kecamatan" class="form-label">Kecamatan</label>
                        <input type="text" name="kecamatan" id="kecamatan" class="form-control" required
                               value="<?= htmlspecialchars($_POST['kecamatan'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="kabupaten" class="form-label">Kabupaten/Kota</label>
                        <input type="text" name="kabupaten" id="kabupaten" class="form-control" required
                               value="<?= htmlspecialchars($_POST['kabupaten'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="provinsi" class="form-label">Provinsi</label>
                        <input type="text" name="provinsi" id="provinsi" class="form-control" required
                               value="<?= htmlspecialchars($_POST['provinsi'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="negara" class="form-label">Negara</label>
                        <input type="text" name="negara" id="negara" class="form-control" value="<?= htmlspecialchars($_POST['negara'] ?? 'Indonesia') ?>" required>
                    </div>

                    <div class="col-md-2">
                        <label for="rt" class="form-label">RT</label>
                        <input type="text" name="rt" id="rt" class="form-control" required
                               value="<?= htmlspecialchars($_POST['rt'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="rw" class="form-label">RW</label>
                        <input type="text" name="rw" id="rw" class="form-control" required
                               value="<?= htmlspecialchars($_POST['rw'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="kode_pos" class="form-label">Kode Pos (Opsional)</label>
                        <input type="text" name="kode_pos" id="kode_pos" class="form-control"
                               value="<?= htmlspecialchars($_POST['kode_pos'] ?? '') ?>">
                    </div>
                    
                    <div class="col-12 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="bi bi-save"></i> Simpan Kartu Keluarga
                        </button>
                        <a href="data_kk.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>