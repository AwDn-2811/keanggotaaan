<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../includes/db.php";

// ambil pengajuan yang disetujui
$sql = "SELECT p.id_pengajuan, w.nama_warga, pr.nama_program
        FROM pengajuan_bantuan p
        JOIN data_warga w ON p.id_warga = w.id
        JOIN program_bantuan pr ON p.id_program = pr.id_program
        WHERE p.status_pengajuan = 'disetujui'
        ORDER BY p.tanggal_pengajuan DESC";

$stmt = $pdo->query($sql);
$pengajuan = $stmt->fetchAll();

// tanggal default hari ini
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Catat Penyaluran Bantuan</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Catat Penyaluran Bantuan</h3>
        <a href="penyaluran_list.php" class="btn btn-secondary btn-sm">⬅ Kembali</a>
    </div>

    <form action="penyaluran_tambah_proses.php" method="POST" enctype="multipart/form-data">

        <div class="mb-3">
            <label class="form-label">Pengajuan (Warga - Program)</label>
            <select name="id_pengajuan" class="form-select" required>
                <option value="">-- Pilih Pengajuan Disetujui --</option>
                <?php foreach ($pengajuan as $p): ?>
                    <option value="<?= $p['id_pengajuan'] ?>">
                        <?= htmlspecialchars($p['nama_warga']) ?> - <?= htmlspecialchars($p['nama_program']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Hanya pengajuan yang sudah disetujui.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Tanggal Penyaluran</label>
            <input type="date" name="tanggal_penyaluran" class="form-control" value="<?= $today ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Status Penyaluran</label>
            <select name="status_penyaluran" class="form-select" required>
                <option value="diterima">Diterima</option>
                <option value="tertunda">Tertunda</option>
                <option value="ditolak">Ditolak</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Bukti Penyerahan (opsional)</label>
            <input type="file" name="bukti_penyerahan" class="form-control"
                   accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3"
                      placeholder="Contoh: Disalurkan di balai desa, dll."></textarea>
        </div>

        <button class="btn btn-success">Simpan Penyaluran</button>
    </form>
</div>

</body>
</html>
