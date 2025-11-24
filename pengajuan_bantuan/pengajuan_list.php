<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../includes/db.php";

$sql = "SELECT p.*, w.nama_warga, pr.nama_program
        FROM pengajuan_bantuan p
        JOIN data_warga w ON p.id_warga = w.id
        JOIN program_bantuan pr ON p.id_program = pr.id_program
        ORDER BY p.tanggal_pengajuan DESC";

$stmt = $pdo->query($sql);
$pengajuan = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengajuan Bantuan</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Pengajuan Bantuan</h3>
        <a href="../dashboard_admin.php" class="btn btn-secondary btn-sm">⬅ Kembali ke Dashboard</a>
    </div>

    <a href="pengajuan_tambah.php" class="btn btn-primary mb-3">+ Tambah Pengajuan (Admin)</a>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Tanggal</th>
            <th>Warga</th>
            <th>Program</th>
            <th>Via</th>
            <th>Status</th>
            <th>Dokumen</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($pengajuan as $row): ?>
            <tr>
                <td><?= $row['id_pengajuan'] ?></td>
                <td><?= $row['tanggal_pengajuan'] ?></td>
                <td><?= htmlspecialchars($row['nama_warga']) ?></td>
                <td><?= htmlspecialchars($row['nama_program']) ?></td>
                <td><?= htmlspecialchars($row['pengajuan_via']) ?></td>
                <td>
                    <?php if ($row['status_pengajuan'] == 'menunggu_verifikasi'): ?>
                        <span class="badge bg-warning text-dark">Menunggu</span>
                    <?php elseif ($row['status_pengajuan'] == 'disetujui'): ?>
                        <span class="badge bg-success">Disetujui</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Ditolak</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($row['dokumen_path']): ?>
                        <a href="../<?= $row['dokumen_path'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                            Lihat
                        </a>
                    <?php else: ?>
                        <span class="text-muted">Tidak ada</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="pengajuan_verifikasi.php?id=<?= $row['id_pengajuan'] ?>"
                       class="btn btn-sm btn-primary">
                        Detail / Verifikasi
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($pengajuan)): ?>
            <tr><td colspan="8" class="text-center">Belum ada pengajuan.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
