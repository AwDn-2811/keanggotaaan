<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';
$error_message = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        /* ===========================
           Ambil input & sanitasi
        ============================ */
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        /* ===========================
           Cek email terdaftar
        ============================ */
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {

            /* ===========================
               Verifikasi password
            ============================ */
            if (password_verify($password, $user['password'])) {

                // Set session dasar
                $_SESSION['user_name'] = $user['nama_depan'];
                $_SESSION['role']      = $user['role'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['nik']       = $user['nik']; // sementara

                /* =============================================
                   Ambil id_warga dari data_warga berdasarkan NIK
                ============================================== */

                $sqlW = "SELECT id, nik FROM data_warga WHERE nik = :nik LIMIT 1";
                $stmtW = $pdo->prepare($sqlW);
                $stmtW->execute([':nik' => $user['nik']]);
                $wargaRow = $stmtW->fetch(PDO::FETCH_ASSOC);


                if ($wargaRow) {
                    $_SESSION['id_warga'] = $wargaRow['id'];
                    $_SESSION['nik']      = $wargaRow['nik']; // dijamin valid
                } else {

                    $_SESSION['id_warga'] = null;
                }

                /* ===========================
                   Redirect berdasarkan role
                ============================ */
                if ($user['role'] === 'admin') {
                    header("Location: dashboard_admin.php");
                } else {
                    header("Location: dashboard_warga.php");
                }
                exit;

            } else {
                $error_message = "Password salah!";
            }
        } else {
            $error_message = "Akun dengan email ini tidak ditemukan.";
        }
    }

} catch (PDOException $e) {
    $error_message = "Koneksi gagal: " . $e->getMessage();
}
?>


<!-- Desain Login HTML -->

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Pendataan Warga</title>

    <style>
    body {
        background-color: #f4f6f9;
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .card {
        width: 380px;
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .title {
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 20px;
        text-align: center;
        color: #007bff;
    }

    .form-group {
        margin-bottom: 15px;
        width: 100%;
    }

    label {
        font-weight: bold;
        display: block;
        margin-bottom: 5px;
    }

    input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
        box-sizing: border-box;
    }

    button {
        width: 100%;
        padding: 10px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 10px;
    }

    .link {
        text-align: center;
        margin-top: 10px;
    }

    .error {
        background: #ffdddd;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 15px;
        color: #a30000;
    }
</style>
</head>

<body>

<div class="card">
    <div class="title">Login Sistem Warga</div>

    <?php if (!empty($error_message)): ?>
        <div class="error"><?= $error_message ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <div class="link">
        Belum punya akun? <a href="register.php">Daftar sekarang</a>
    </div>
</div>
</body>
</html>

