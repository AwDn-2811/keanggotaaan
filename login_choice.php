<?php
session_start(); // Mulai session

// Jika sudah ada session role, langsung redirect ke login.php
if (isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Login - Sistem Pendataan Warga</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Pilih Peran Login</h1>
    </header>

    <section class="form-container">
        <p>Silakan pilih peran Anda untuk login:</p>
        <a href="login.php?role=admin">
            <button>Login sebagai Admin</button>
        </a>
        <a href="login.php?role=warga">
            <button>Login sebagai Warga</button>
        </a>
    </section>

</body>
</html>
