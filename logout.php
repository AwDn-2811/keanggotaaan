<?php
session_start(); // Mulai session untuk pengelolaan session

// Jika pengguna mengonfirmasi logout, hapus session dan logout
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    session_unset(); // Menghapus semua session
    session_destroy(); // Menghancurkan session
    header("Location: login.php"); // Redirect ke halaman login
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Logout - Sistem Pendataan Warga</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Konfirmasi Logout</h1>
    </header>

    <section class="logout-confirmation">
        <p>Apakah Anda yakin ingin keluar?</p>
        <a href="logout.php?confirm=yes">
            <button>Ya, Keluar</button>
        </a>
        <a href="dashboard.php">
            <button class="cancel-btn">Batal</button>
        </a>
    </section>
</body>
</html>
