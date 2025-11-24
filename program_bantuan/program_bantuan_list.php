<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../fungsi_bantuan/fungsi_program_bantuan.php";
$program = getProgram();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Program Bantuan</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Daftar Program Bantuan</h3>
        <a href="../dashboard_admin.php" class="btn btn-secondary btn-sm">⬅ Kembali ke Dashboard</a>
    </div>

    <a href="program_bantuan_tambah.php" class="btn btn-primary mb-3">+ Tambah Program</a>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Nama Program</th>
            <th>Jenis Bantuan</th>
            <th>Kuota</th>
            <th>Jadwal</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($program as $row): ?>
            <tr>
                <td><?= $row['id_program'] ?></td>
                <td><?= htmlspecialchars($row['nama_program']) ?></td>
                <td><?= htmlspecialchars($row['jenis_bantuan']) ?></td>
                <td><?= $row['kuota'] ?></td>
                <td><?= $row['jadwal_mulai'] ?> s/d <?= $row['jadwal_selesai'] ?></td>
                <td>
                    <a href="program_bantuan_edit.php?id=<?= $row['id_program'] ?>"
                       class="btn btn-warning btn-sm">Edit</a>
                    <a href="program_bantuan_hapus.php?id=<?= $row['id_program'] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Yakin hapus program ini?');">
                        Hapus
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($program)): ?>
            <tr><td colspan="6" class="text-center">Belum ada program bantuan.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
