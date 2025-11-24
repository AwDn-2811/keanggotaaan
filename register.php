<?php
session_start(); // Memulai session untuk menyimpan data login

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';
$error_message = ''; // Menyimpan pesan error

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Mengambil data dari form
        $nama_depan      = $_POST['nama_depan'];
        $nama_belakang   = $_POST['nama_belakang'];
        $nik             = $_POST['nik'];
        $email           = $_POST['email'];
        $nik             = $_POST['nik'];              // ← baru
        $password        = $_POST['password'];
        $confirm_password= $_POST['confirm_password'];
        $invite_code     = $_POST['invite_code'];      // Kode undangan admin

        // Mengecek apakah password dan konfirmasi password cocok
        if ($password !== $confirm_password) {
            $error_message = 'Password dan konfirmasi password tidak cocok!';
        } else {
            // Mengecek apakah email sudah terdaftar
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $error_message = 'Akun dengan email ini sudah terdaftar!';
            } else {
                // Default role warga
                $role = 'warga';
                $inv  = null;

                // Validasi kode undangan admin jika ada
                if (!empty($invite_code)) {
                    $q = "SELECT id, invite_code, email, expires_at, used 
                          FROM admin_invites
                          WHERE invite_code = :code AND used = 0 LIMIT 1";
                    $check = $pdo->prepare($q);
                    $check->execute([':code' => $invite_code]);
                    $inv = $check->fetch(PDO::FETCH_ASSOC);

                    // Cek apakah kode undangan valid
                    if ($inv) {
                        // Cek kadaluwarsa
                        $valid_code = true;
                        if (!empty($inv['expires_at'])) {
                            $valid_code = (strtotime($inv['expires_at']) > time());
                        }

                        if ($valid_code) {
                            $role = 'admin';
                        } else {
                            $error_message = 'Kode undangan admin tidak valid atau sudah kedaluwarsa!';
                        }
                    } else {
                        $error_message = 'Kode undangan admin tidak valid!';
                    }
                }

                // Cek NIK di data_warga (kalau belum ada error)
                if (empty($error_message)) {
                    $sqlNik = "SELECT * FROM data_warga WHERE nik = :nik LIMIT 1";
                    $stmtNik = $pdo->prepare($sqlNik);
                    $stmtNik->execute([':nik' => $nik]);
                    $dataWarga = $stmtNik->fetch(PDO::FETCH_ASSOC);

                    if (!$dataWarga) {
                        $error_message = 'NIK tidak ditemukan dalam data warga! Silakan hubungi admin RT/RW.';
                    }
                }

                // Jika tidak ada error, simpan data pengguna
                if (empty($error_message)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "INSERT INTO users (nama_depan, nama_belakang, email, nik, password, role) 
                            VALUES (:nama_depan, :nama_belakang, :email, :nik, :password, :role)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':nama_depan'   => $nama_depan,
                        ':nama_belakang'=> $nama_belakang,
                        ':email'        => $email,
                        ':nik'          => $nik,             // ← baru
                        ':password'     => $hashed_password,
                        ':role'         => $role
                    ]);

                    // Jika role admin, tandai kode undangan sebagai sudah digunakan
                    if ($role == 'admin' && $inv) {
                        $upd = $pdo->prepare("UPDATE admin_invites SET used = 1, used_at = NOW() WHERE id = :id");
                        $upd->execute([':id' => $inv['id']]);
                    }

                    // Pesan sukses
                    $error_message = 'Pendaftaran berhasil! Silakan <a href="login.php">login</a> sebagai ' . $role . '.';
                }
            }
        }
    }
} catch (PDOException $e) {
    $error_message = "Koneksi gagal: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Pendataan Warga</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <h1>Daftar Akun Baru</h1>
</header>

<section class="form-container">
    <?php if ($error_message): ?>
        <div class="notification error">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <!-- Form Pendaftaran -->
    <form action="register.php" method="POST">
        <label for="nama_depan">Nama Depan:</label>
        <input type="text" id="nama_depan" name="nama_depan" required>

        <label for="nama_belakang">Nama Belakang:</label>
        <input type="text" id="nama_belakang" name="nama_belakang" required>

        <label for="nik">NIK:</label>
        <input type="text" id="nik" name="nik" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="nik">NIK:</label>
        <input type="text" id="nik" name="nik" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Konfirmasi Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <label for="nik">NIK:</label>
        <input type="text" id="nik" name="nik" required>


        <!-- Kode undangan admin (opsional) -->
        <label for="invite_code">Kode Admin (opsional):</label>
        <input type="text" id="invite_code" name="invite_code">

        <button type="submit" name="register">Daftar</button>
    </form>

    <hr>
    <div class="auth-switch">
        Sudah punya akun? <a href="login.php">Login sekarang</a>
    </div>
</section>
</body>
</html>
