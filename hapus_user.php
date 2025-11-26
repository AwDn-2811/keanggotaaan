<?php
session_start();

// Cek hanya admin
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

if (!isset($_GET['id'])) {
    header("Location: data_user.php");
    exit();
}

$id = (int) $_GET['id'];

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Cek apakah user ada
    $checkSql = "SELECT * FROM users WHERE id = :id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':id' => $id]);

    if ($checkStmt->rowCount() === 0) {
        die("User tidak ditemukan.");
    }

    // Hapus user
    $deleteSql = "DELETE FROM users WHERE id = :id";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->execute([':id' => $id]);

    header("Location: data_user.php?msg=deleted");
    exit();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
