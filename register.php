<?php
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
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $nama_depan      = $_POST['nama_depan'];
        $nama_belakang   = $_POST['nama_belakang'];
        $nik             = $_POST['nik'];
        $email           = $_POST['email'];

        $password        = $_POST['password'];
        $confirm_password= $_POST['confirm_password'];
        $invite_code     = trim($_POST['invite_code']);

        // Password cocok?
        if ($password !== $confirm_password) {
            $error_message = "Password dan konfirmasi tidak sama!";
        }

        // Email sudah dipakai?
        if (empty($error_message)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->rowCount() > 0) {
                $error_message = "Email sudah terdaftar!";
            }
        }

        // Tentukan role default
        $role = "warga";
        $inviteRow = null;

        // Jika ada kode admin → cek
        if (empty($error_message) && !empty($invite_code)) {
            $q = "SELECT * FROM admin_invites 
                  WHERE invite_code = :code LIMIT 1";
            $stmt = $pdo->prepare($q);
            $stmt->execute([':code' => $invite_code]);
            $inviteRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$inviteRow) {
                $error_message = "Kode Admin tidak valid!";
            } else {
                // Kode masih valid
                $role = "admin";
            }
        }

        // Jika role warga → cek NIK dari tabel data_warga
        if (empty($error_message) && $role == "warga") {
            $stmt = $pdo->prepare("SELECT nik FROM data_warga WHERE nik = :nik LIMIT 1");
            $stmt->execute([':nik' => $nik]);

            if (!$stmt->fetch()) {
                $error_message = "NIK tidak ditemukan pada data warga!";
            }
        }

        // Jika lolos semua → insert
        if (empty($error_message)) {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (nama_depan, nama_belakang, nik, email, password, role)
                VALUES (:nama_depan, :nama_belakang, :nik, :email, :password, :role)
            ");

            $stmt->execute([
                ':nama_depan'   => $nama_depan,
                ':nama_belakang'=> $nama_belakang,
                ':nik'          => $role == 'admin' ? null : $nik,
                ':email'        => $email,
                ':password'     => $hashed_password,
                ':role'         => $role
            ]);

            $error_message = "Pendaftaran berhasil! Silakan <a href='login.php'>login</a> sebagai <strong>$role</strong>.";
        }
    }

} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Sistem Warga</title>

    <style>
    body {
        background-color: #f4f6f9;
        font-family: Arial;
        display: flex;
        justify-content: center;
        padding: 30px 0;
    }

    .card {
        width: 450px;
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
        text-align: left;
    }

    label {
        font-weight: bold;
        display: block;
        margin-bottom: 5px;
    }

    input {
        width: 100%;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 15px;
        box-sizing: border-box;
    }

    button {
        width: 100%;
        padding: 12px;
        background: #053173ff;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 10px;
    }

    .link {
        text-align: center;
        margin-top: 15px;
    }
</style>
</head>

<body>

<div class="card">

    <div class="title">Daftar Akun Warga</div>

    <?php if (!empty($error_message)): ?>
        <div class="<?= strpos($error_message,'berhasil')!==false ? 'success' : 'error' ?>">
            <?= $error_message ?>
        </div>
    <?php endif; ?>

    
    <form action="register.php" method="POST">

        <label>Nama Depan</label>
        <input type="text" name="nama_depan" required>

        <label>Nama Belakang</label>
        <input type="text" name="nama_belakang" required>

        <label>NIK</label>
        <input type="text" name="nik" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Konfirmasi Password</label>
        <input type="password" name="confirm_password" required>

        <label>Kode Admin (opsional)</label>
        <input type="text" name="invite_code">

        <button type="submit">Daftar</button>
    </form>

    <div class="link">
        Sudah punya akun? <a href="login.php">Login sekarang</a>
    </div>

</div>

</body>
</html>
