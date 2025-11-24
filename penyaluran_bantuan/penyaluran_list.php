<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../includes/db.php";

$sql = "SELECT pb.*, w.nama_warga, pr.nama_program
        FROM penyaluran_bantuan pb
        JOIN pengajuan_bantuan pg ON pb.id_pengajuan = pg.id_pengajuan
        JOIN data_warga w ON pg.id_warga = w.id
        JOIN program_bantuan pr ON pg.id_program = pr.id_program
        ORDER BY pb.tanggal_penyaluran DESC";

$stmt = $pdo->query($sql);
$penyaluran = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Penyaluran Bantuan</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Penyaluran & Monitoring Bantuan</h3>
        <a href="../dashboard_admin.php" class="btn btn-secondary btn-sm">⬅ Kembali ke Dashboard</a>
    </div>

    <a href="penyaluran_tambah.php" class="btn btn-primary mb-3">+ Catat Penyaluran</a>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Tanggal</th>
            <th>Warga</th>
            <th>Program</th>
            <th>Status</th>
            <th>Bukti</th>
            <th>Keterangan</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($penyaluran as $row): ?>
            <tr>
                <td><?= $row['id_penyaluran'] ?></td>
                <td><?= $row['tanggal_penyaluran'] ?></td>
                <td><?= htmlspecialchars($row['nama_warga']) ?></td>
                <td><?= htmlspecialchars($row['nama_program']) ?></td>
                <td>
                    <?php if ($row['status_penyaluran'] == 'diterima'): ?>
                        <span class="badge bg-success">Diterima</span>
                    <?php elseif ($row['status_penyaluran'] == 'tertunda'): ?>
                        <span class="badge bg-warning text-dark">Tertunda</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Ditolak</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($row['bukti_penyerahan']): ?>
                        <a href="../<?= $row['bukti_penyerahan'] ?>" target="_blank"
                           class="btn btn-sm btn-outline-secondary">Lihat</a>
                    <?php else: ?>
                        <span class="text-muted">Tidak ada</span>
                    <?php endif; ?>
                </td>
                <td><?= nl2br(htmlspecialchars($row['keterangan'])) ?></td>
                <td>
                    <a href="penyaluran_hapus.php?id=<?= $row['id_penyaluran'] ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Yakin hapus data penyaluran ini?');">
                        Hapus
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($penyaluran)): ?>
            <tr><td colspan="8" class="text-center">Belum ada data penyaluran.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
