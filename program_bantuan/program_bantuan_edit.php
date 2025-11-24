<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../fungsi_bantuan/fungsi_program_bantuan.php";

if (!isset($_GET['id'])) {
    header("Location: program_bantuan_list.php");
    exit();
}

$id   = (int) $_GET['id'];
$data = getProgramById($id);

if (!$data) {
    echo "Program tidak ditemukan.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Program Bantuan</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Edit Program Bantuan</h3>
        <a href="program_bantuan_list.php" class="btn btn-secondary btn-sm">⬅ Kembali</a>
    </div>

    <form action="program_bantuan_edit_proses.php" method="POST">
        <input type="hidden" name="id_program" value="<?= $data['id_program'] ?>">

        <div class="mb-3">
            <label class="form-label">Nama Program</label>
            <input type="text" name="nama_program" class="form-control"
                   value="<?= htmlspecialchars($data['nama_program']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Jenis Bantuan</label>
            <input type="text" name="jenis_bantuan" class="form-control"
                   value="<?= htmlspecialchars($data['jenis_bantuan']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Persyaratan</label>
            <textarea name="persyaratan" class="form-control" rows="3"><?= htmlspecialchars($data['persyaratan']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Kuota Bantuan</label>
            <input type="number" name="kuota" class="form-control" min="0"
                   value="<?= $data['kuota'] ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Jadwal Mulai</label>
            <input type="date" name="jadwal_mulai" class="form-control"
                   value="<?= $data['jadwal_mulai'] ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Jadwal Selesai</label>
            <input type="date" name="jadwal_selesai" class="form-control"
                   value="<?= $data['jadwal_selesai'] ?>">
        </div>

        <button class="btn btn-success">Update Program</button>
    </form>
</div>

</body>
</html>
