<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'warga') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/includes/db.php";

$id_warga = $_SESSION['id_warga'];
$today = date('Y-m-d');

$sql = "SELECT * FROM program_bantuan ORDER BY jadwal_mulai DESC";
$program = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Program Bantuan</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light p-4">

<div class="container">
    <h3 class="mb-3">Daftar Program Bantuan</h3>

    <a href="dashboard_warga.php" class="btn btn-secondary btn-sm mb-3">⬅ Kembali</a>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Program</th>
                <th>Jenis</th>
                <th>Kuota</th>
                <th>Jadwal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>

        <?php foreach($program as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['nama_program']) ?></td>
                <td><?= htmlspecialchars($row['jenis_bantuan']) ?></td>
                <td><?= $row['kuota'] ?></td>
                <td><?= $row['jadwal_mulai'] ?> s/d <?= $row['jadwal_selesai'] ?></td>
                <td>
                    <?php if ($row['jadwal_selesai'] < $today): ?>
                        <span class="badge bg-secondary">Ditutup</span>

                    <?php elseif ($row['jadwal_mulai'] > $today): ?>
                        <span class="badge bg-warning text-dark">Belum Dibuka</span>

                    <?php else: ?>
                        <form action="pengajuan_bantuan/pengajuan_tambah_proses_warga.php"
                              method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id_warga" value="<?= $user_id ?>">
                            <input type="hidden" name="id_program" value="<?= $row['id_program'] ?>">
                            <input type="hidden" name="pengajuan_via" value="warga">

                            <input type="file" name="dokumen" required>
                            <button type="submit" class="btn btn-primary btn-sm mt-1">
                                Ajukan
                            </button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (empty($program)): ?>
            <tr><td colspan="5" class="text-center">Belum ada program.</td></tr>
        <?php endif; ?>

        </tbody>
    </table>
</div>

</body>
</html>
