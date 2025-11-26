<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'warga') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/fungsi_bantuan/fungsi_penyaluran_bantuan.php";

$id_warga = $_SESSION['id_warga'];
$riwayat = getRiwayatWarga($id_warga);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Penyaluran</title>
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4 bg-light">

<div class="container">

    <h3 class="mb-3">Riwayat Penyaluran Bantuan</h3>
    <a href="dashboard_warga.php" class="btn btn-secondary btn-sm mb-3">⬅ Kembali</a>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Tgl Penyaluran</th>
                <th>Program</th>
                <th>Status</th>
                <th>Bukti</th>
            </tr>
        </thead>
        <tbody>

        <?php foreach ($riwayat as $row): ?>
            <tr>
                <td><?= $row['tanggal_penyaluran'] ?></td>
                <td><?= htmlspecialchars($row['nama_program']) ?></td>
                <td><?= $row['status_penyaluran'] ?></td>
                <td>
                    <?php if (!empty($row['bukti_penyerahan'])): ?>
                       <a href="<?= $row['bukti_penyerahan'] ?>" target="_blank"
                          class="btn btn-sm btn-outline-secondary">Lihat Bukti</a>
                    <?php else: ?>
                        <span class="text-muted">Tidak ada</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (empty($riwayat)): ?>
            <tr><td colspan="4" class="text-center">Belum ada penyaluran.</td></tr>
        <?php endif; ?>

        </tbody>
    </table>

</div>

</body>
</html>
