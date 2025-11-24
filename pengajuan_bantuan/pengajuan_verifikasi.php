<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../includes/db.php";

if (!isset($_GET['id'])) {
    header("Location: pengajuan_list.php");
    exit();
}

$id = (int) $_GET['id'];

$sql = "SELECT p.*, w.nama_warga, w.nik, pr.nama_program
        FROM pengajuan_bantuan p
        JOIN data_warga w ON p.id_warga = w.id
        JOIN program_bantuan pr ON p.id_program = pr.id_program
        WHERE p.id_pengajuan = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$data = $stmt->fetch();

if (!$data) {
    echo "Pengajuan tidak ditemukan.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail & Verifikasi Pengajuan</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Detail Pengajuan Bantuan</h3>
        <a href="pengajuan_list.php" class="btn btn-secondary btn-sm">⬅ Kembali</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Data Pengajuan</h5>

            <p><strong>ID Pengajuan:</strong> <?= $data['id_pengajuan'] ?></p>
            <p><strong>Tanggal Pengajuan:</strong> <?= $data['tanggal_pengajuan'] ?></p>
            <p><strong>Warga:</strong> <?= htmlspecialchars($data['nama_warga']) ?> (NIK: <?= htmlspecialchars($data['nik']) ?>)</p>
            <p><strong>Program:</strong> <?= htmlspecialchars($data['nama_program']) ?></p>
            <p><strong>Pengajuan via:</strong> <?= htmlspecialchars($data['pengajuan_via']) ?></p>

            <p><strong>Status Saat Ini:</strong>
                <?php if ($data['status_pengajuan'] == 'menunggu_verifikasi'): ?>
                    <span class="badge bg-warning text-dark">Menunggu Verifikasi</span>
                <?php elseif ($data['status_pengajuan'] == 'disetujui'): ?>
                    <span class="badge bg-success">Disetujui</span>
                <?php else: ?>
                    <span class="badge bg-danger">Ditolak</span>
                <?php endif; ?>
            </p>

            <p><strong>Dokumen Pendukung:</strong>
                <?php if ($data['dokumen_path']): ?>
                    <a href="../<?= $data['dokumen_path'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                        Lihat Dokumen
                    </a>
                <?php else: ?>
                    <span class="text-muted">Tidak ada</span>
                <?php endif; ?>
            </p>

            <?php if (!empty($data['catatan_verifikasi'])): ?>
                <p><strong>Catatan Verifikator:</strong><br><?= nl2br(htmlspecialchars($data['catatan_verifikasi'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Form Verifikasi -->
    <div class="card mb-4">
        <div class="card-header">Verifikasi Pengajuan</div>
        <div class="card-body">
            <form action="pengajuan_verifikasi_proses.php" method="POST">
                <input type="hidden" name="id_pengajuan" value="<?= $data['id_pengajuan'] ?>">

                <div class="mb-3">
                    <label class="form-label">Status Verifikasi</label>
                    <select name="status_pengajuan" class="form-select" required>
                        <option value="menunggu_verifikasi" <?= $data['status_pengajuan']=='menunggu_verifikasi'?'selected':'' ?>>Menunggu Verifikasi</option>
                        <option value="disetujui" <?= $data['status_pengajuan']=='disetujui'?'selected':'' ?>>Disetujui</option>
                        <option value="ditolak" <?= $data['status_pengajuan']=='ditolak'?'selected':'' ?>>Ditolak</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan Verifikator (opsional)</label>
                    <textarea name="catatan_verifikasi" class="form-control" rows="3"><?= htmlspecialchars($data['catatan_verifikasi'] ?? '') ?></textarea>
                </div>

                <button class="btn btn-primary">Simpan Verifikasi</button>
            </form>
        </div>
    </div>

    <!-- Form Upload SK -->
    <div class="card mb-4">
        <div class="card-header">Surat Keputusan Penerima (SK)</div>
        <div class="card-body">
            <?php if (!empty($data['file_surat_keputusan'])): ?>
                <p><strong>No. SK:</strong> <?= htmlspecialchars($data['no_surat_keputusan']) ?></p>
                <a href="../<?= $data['file_surat_keputusan'] ?>" target="_blank"
                   class="btn btn-sm btn-outline-secondary">Lihat SK</a>
                <hr>
            <?php endif; ?>

            <form action="pengajuan_upload_sk_proses.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_pengajuan" value="<?= $data['id_pengajuan'] ?>">

                <div class="mb-3">
                    <label class="form-label">Nomor SK</label>
                    <input type="text" name="no_surat_keputusan"
                           value="<?= htmlspecialchars($data['no_surat_keputusan'] ?? '') ?>"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">File SK</label>
                    <input type="file" name="file_sk" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>

                <button class="btn btn-success">Upload / Update SK</button>
            </form>
        </div>
    </div>

</div>

</body>
</html>
