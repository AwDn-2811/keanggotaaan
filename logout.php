<?php
session_start(); // Mulai session untuk pengelolaan session

// Ambil role user dari session
$role = $_SESSION['role'] ?? null;

// Tentukan alamat batal berdasarkan role
if ($role === 'admin') {
    $cancel_url = "dashboard_admin.php";
} elseif ($role === 'warga') {
    $cancel_url = "dashboard_warga.php";
} else {
    // Jika tidak ada role (misalnya session sudah expired)
    $cancel_url = "login.php";
}

// Jika pengguna mengonfirmasi logout
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    session_unset();        // Hapus semua session
    session_destroy();      // Hancurkan session
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Logout - Sistem Pendataan Warga</title>

    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            padding-top: 120px;
        }

        .card {
            width: 450px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        h1 {
            color: #007bff;
            margin-bottom: 20px;
        }

        button {
            padding: 10px 20px;
            border: none;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border-radius: 6px;
            margin: 5px;
            font-size: 15px;
        }

        .btn-yes {
            background: #28a745;
        }

        .btn-yes:hover {
            background: #1e7e34;
        }

        .btn-cancel {
            background: #dc3545;
        }

        .btn-cancel:hover {
            background: #b52a37;
        }

        a {
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="card">
    <h1>Konfirmasi Logout</h1>
    <p>Apakah Anda yakin ingin keluar?</p>

    <a href="logout.php?confirm=yes">
        <button class="btn-yes">Ya, Keluar</button>
    </a>

    <a href="<?= $cancel_url ?>">
        <button class="btn-cancel">Batal</button>
    </a>
</div>

</body>
</html>
