<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'warga') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/includes/db.php";

$user_id = $_SESSION['nik'];

$sql = "SELECT pg.*, pr.nama_program
        FROM pengajuan_bantuan pg
        JOIN program_bantuan pr ON pg.id_program = pr.id_program
        WHERE pg.id_warga = :id 
        ORDER BY pg.tanggal_pengajuan DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $user_id]);
$data = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Pengajuan</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light p-4">

<div class="container">

    <h3 class="mb-3">Riwayat Pengajuan Bantuan</h3>
    <a href="dashboard_warga.php" class="btn btn-secondary btn-sm mb-3">⬅ Kembali</a>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Tanggal</th>
                <th>Program</th>
                <th>Status</th>
                <th>SK</th>
            </tr>
        </thead>
        <tbody>

        <?php foreach ($data as $row): ?>
            <tr>
                <td><?= $row['tanggal_pengajuan'] ?></td>
                <td><?= htmlspecialchars($row['nama_program']) ?></td>
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
                    <?php if (!empty($row['file_surat_keputusan'])): ?>
                       <a href="<?= $row['file_surat_keputusan'] ?>" target="_blank"
                          class="btn btn-sm btn-outline-secondary">Lihat SK</a>
                    <?php else: ?>
                        <span class="text-muted">Belum ada</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (empty($data)): ?>
            <tr><td colspan="4" class="text-center">Belum ada pengajuan.</td></tr>
        <?php endif; ?>

        </tbody>
    </table>

</div>
</body>
</html>
