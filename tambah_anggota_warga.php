<?php
session_start();

// Hanya admin yang boleh akses
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['nik'])) {
    die("NIK Kepala Keluarga tidak ditemukan.");
}

$nik_kk = $_GET['nik']; // NIK Kepala keluarga

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

$error_message = '';
$success_message = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Ambil data kepala keluarga
    $stmt = $pdo->prepare("SELECT * FROM data_warga WHERE nik = :nik");
    $stmt->execute([':nik' => $nik_kk]);
    $kepala = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kepala) {
        die("Data Kepala Keluarga tidak ditemukan.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nik = $_POST['nik'];
        $nama_warga = $_POST['nama_warga'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $pendidikan = $_POST['pendidikan'];
        $pekerjaan = $_POST['pekerjaan'];
        $status_perkawinan = $_POST['status_perkawinan'];
        $status = $_POST['status'];

        // Cek apakah NIK sudah dipakai
        $cekNik = $pdo->prepare("SELECT nik FROM data_warga WHERE nik = :nik");
        $cekNik->execute([':nik' => $nik]);

        if ($cekNik->rowCount() > 0) {
            $error_message = "NIK sudah terdaftar! Gunakan NIK lain.";
        } else {

            $insert = $pdo->prepare("
                INSERT INTO data_warga 
                (nik, nama_warga, jenis_kelamin, tanggal_lahir, pendidikan_terakhir, pekerjaan, status_perkawinan, status, nik_kepala_keluarga)
                VALUES 
                (:nik, :nama_warga, :jenis_kelamin, :tanggal_lahir, :pendidikan, :pekerjaan, :status_perkawinan, :status, :nik_kepala)
            ");

            $insert->execute([
                ':nik' => $nik,
                ':nama_warga' => $nama_warga,
                ':jenis_kelamin' => $jenis_kelamin,
                ':tanggal_lahir' => $tanggal_lahir,
                ':pendidikan' => $pendidikan,
                ':pekerjaan' => $pekerjaan,
                ':status_perkawinan' => $status_perkawinan,
                ':status' => $status,
                ':nik_kepala' => $nik_kk
            ]);

            $success_message = "Anggota berhasil ditambahkan ke KK!";
        }
    }

} catch (PDOException $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Anggota Warga</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-4">

    <div class="card shadow-sm">
        <div class="card-body">

            <h3>Tambah Anggota Keluarga</h3>
            <p class="text-muted">Menambahkan anggota baru untuk KK: <b><?= htmlspecialchars($kepala['nama_warga']) ?></b> (<?= $nik_kk ?>)</p>

            <a href="data_warga.php" class="btn btn-secondary btn-sm mb-3">Kembali</a>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= $success_message ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">NIK Anggota</label>
                    <input type="text" name="nik" class="form-control" required maxlength="16">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Anggota</label>
                    <input type="text" name="nama_warga" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control" required>
                        <option value="">-- PILIH --</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pendidikan Terakhir</label>
                    <input type="text" name="pendidikan" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Pekerjaan</label>
                    <input type="text" name="pekerjaan" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status Perkawinan</label>
                    <select name="status_perkawinan" class="form-control">
                        <option value="Belum Kawin">Belum Kawin</option>
                        <option value="Kawin">Kawin</option>
                        <option value="Cerai Hidup">Cerai Hidup</option>
                        <option value="Cerai Mati">Cerai Mati</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status Warga</label>
                    <select name="status" class="form-control">
                        <option value="Aktif">Aktif</option>
                        <option value="Pindah">Pindah</option>
                        <option value="Meninggal">Meninggal</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Anggota</button>

            </form>

        </div>
    </div>

</div>

</body>
</html>
