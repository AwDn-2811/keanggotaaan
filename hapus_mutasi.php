<?php
session_start();

// Cek apakah user sudah login dan peran admin
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';
$error = '';

if (isset($_GET['id'])) {
    $id_mutasi = $_GET['id']; // Ambil id yang ingin dihapus

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        // Hapus data mutasi berdasarkan id
        $sql = "DELETE FROM data_mutasi WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id_mutasi]);

        // Redirect setelah hapus berhasil
        header("Location: data_mutasi.php?status=deleted");
        exit();

    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
} else {
    header("Location: data_mutasi.php"); // Kalau id tidak ada, redirect ke halaman data_mutasi.php
    exit();
}
?>
