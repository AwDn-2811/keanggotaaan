<?php
session_start();

// Cek hak akses: hanya admin yang boleh akses
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

// Cek apakah nomor_kk diberikan melalui parameter URL
if (!isset($_GET['nomor_kk']) || empty($_GET['nomor_kk'])) {
    header("Location: data_kk.php?status=error&message=Nomor KK tidak valid.");
    exit();
}

$nomor_kk = $_GET['nomor_kk'];

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Ambil KK ID terlebih dahulu
    $stmtKK = $pdo->prepare("SELECT id FROM data_kk WHERE nomor_kk = :nomor_kk");
    $stmtKK->execute([':nomor_kk' => $nomor_kk]);
    $kk_detail = $stmtKK->fetch(PDO::FETCH_ASSOC);

    if (!$kk_detail) {
        // Jika KK tidak ditemukan
        header("Location: data_kk.php?status=error&message=Kartu Keluarga tidak ditemukan.");
        exit();
    }
    
    $kk_id = $kk_detail['id'];

    // Mulai transaksi untuk memastikan semua operasi sukses atau tidak sama sekali
    $pdo->beginTransaction();

    // 1. UPDATE data_warga: Putus ikatan KK untuk semua anggota
    // SET kk_id dan status_hubungan_kk menjadi NULL
    $stmtUpdateWarga = $pdo->prepare("
        UPDATE data_warga 
        SET kk_id = NULL, status_hubungan_kk = NULL 
        WHERE kk_id = :kk_id
    ");
    $stmtUpdateWarga->execute([':kk_id' => $kk_id]);

    // 2. DELETE data_kk: Hapus baris KK itu sendiri
    $stmtDeleteKK = $pdo->prepare("DELETE FROM data_kk WHERE id = :kk_id");
    $stmtDeleteKK->execute([':kk_id' => $kk_id]);

    // Commit transaksi jika semua query berhasil
    $pdo->commit();

    // Redirect ke halaman data_kk dengan pesan sukses
    $success_message = urlencode("Kartu Keluarga dengan Nomor **$nomor_kk** berhasil dihapus. Semua anggota telah diputus ikatannya.");
    header("Location: data_kk.php?status=success&message=$success_message");
    exit();

} catch (PDOException $e) {
    // Rollback jika ada error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Redirect ke halaman data_kk dengan pesan error
    $error_message = urlencode("Gagal menghapus Kartu Keluarga: " . $e->getMessage());
    header("Location: data_kk.php?status=error&message=$error_message");
    exit();
}

// Redirect default jika terjadi error tak terduga
header("Location: data_kk.php?status=error&message=Terjadi kesalahan tak terduga.");
exit();
?>