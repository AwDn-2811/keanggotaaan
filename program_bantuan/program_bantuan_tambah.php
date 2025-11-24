<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Program Bantuan</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Tambah Program Bantuan</h3>
        <a href="program_bantuan_list.php" class="btn btn-secondary btn-sm">⬅ Kembali</a>
    </div>

    <form action="program_bantuan_tambah_proses.php" method="POST">
        <div class="mb-3">
            <label class="form-label">Nama Program</label>
            <input type="text" name="nama_program" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Jenis Bantuan</label>
            <input type="text" name="jenis_bantuan" class="form-control"
                   placeholder="Contoh: Bansos Pangan, Uang Tunai, Beasiswa, Bedah Rumah" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Persyaratan</label>
            <textarea name="persyaratan" class="form-control" rows="3"
                      placeholder="Tuliskan persyaratan untuk program ini"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Kuota Bantuan</label>
            <input type="number" name="kuota" class="form-control" min="0">
        </div>

        <div class="mb-3">
            <label class="form-label">Jadwal Mulai</label>
            <input type="date" name="jadwal_mulai" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Jadwal Selesai</label>
            <input type="date" name="jadwal_selesai" class="form-control">
        </div>

        <button class="btn btn-success">Simpan Program</button>
    </form>
</div>

</body>
</html>
