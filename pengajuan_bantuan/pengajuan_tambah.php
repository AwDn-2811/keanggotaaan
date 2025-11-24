<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../includes/db.php";

// ambil warga
$warga = $pdo->query("SELECT id, nama_warga FROM data_warga ORDER BY nama_warga")->fetchAll();

// ambil program
$program = $pdo->query("SELECT id_program, nama_program FROM program_bantuan ORDER BY nama_program")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pengajuan Bantuan</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Tambah Pengajuan Bantuan (Admin)</h3>
        <a href="pengajuan_list.php" class="btn btn-secondary btn-sm">⬅ Kembali</a>
    </div>

    <form action="pengajuan_tambah_proses.php" method="POST" enctype="multipart/form-data">

        <div class="mb-3">
            <label class="form-label">Warga</label>
            <select name="id_warga" class="form-select" required>
                <option value="">-- Pilih Warga --</option>
                <?php foreach ($warga as $w): ?>
                    <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['nama_warga']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Program Bantuan</label>
            <select name="id_program" class="form-select" required>
                <option value="">-- Pilih Program --</option>
                <?php foreach ($program as $p): ?>
                    <option value="<?= $p['id_program'] ?>"><?= htmlspecialchars($p['nama_program']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Dokumen Pendukung (opsional)</label>
            <input type="file" name="dokumen" class="form-control"
                   accept=".pdf,.jpg,.jpeg,.png">
            <div class="form-text">Format: PDF/JPG/PNG.</div>
        </div>

        <input type="hidden" name="pengajuan_via" value="admin">

        <button class="btn btn-success">Simpan Pengajuan</button>
    </form>
</div>

</body>
</html>
