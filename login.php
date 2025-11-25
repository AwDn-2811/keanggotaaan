<?php
// Tampilkan semua error (buat debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Memulai session untuk menyimpan data login

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';
$error_message = ''; // Menyimpan pesan error

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Mengambil data dari form
        $email    = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Mengecek apakah email ada di database
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Mengambil data user
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Memeriksa apakah password cocok
            if (password_verify($password, $user['password'])) {

                // Simpan data dasar user
                $_SESSION['user_name'] = $user['nama_depan'];
                $_SESSION['role']      = $user['role'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['nik']       = $user['nik'];  // Mungkin masih NULL, tapi tidak apa ~ diperbaiki di bawah

                /* ========================================================
                   AMBIL id_warga & nik DARI TABEL data_warga
                   BERDASARKAN NIK USER
                ======================================================== */

                $sqlW = "SELECT id, nik FROM data_warga WHERE nik = :nik LIMIT 1";
                $stmtW = $pdo->prepare($sqlW);
                $stmtW->execute([':nik' => $user['nik']]);
                $wargaRow = $stmtW->fetch(PDO::FETCH_ASSOC);

                // Jika warga ditemukan di data_warga
                if ($wargaRow) {
                    $_SESSION['id_warga'] = $wargaRow['id'];
                    $_SESSION['nik']      = $wargaRow['nik'];  // overwrite biar pasti benar
                } else {
                    // Kalau tidak ditemukan → id_warga NULL → akan memunculkan error FK
                    $_SESSION['id_warga'] = null;
                }

                // Redirect berdasarkan role pengguna
                if ($user['role'] == 'admin') {
                    header("Location: dashboard_admin.php");
                } else {
                    header("Location: dashboard_warga.php");
                }
                exit;

            } else {
                $error_message = 'Password salah!';
            }
        } else {
            $error_message = 'Akun dengan email ini belum terdaftar!';
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
    <title>Login - Sistem Pendataan Warga</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Login</h1>
    </header>

    <section class="form-container">
        <?php if (!empty($error_message)): ?>
            <div class="notification error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" name="login">Login</button>
        </form>

        <hr>

        <p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
    </section>
</body>
</html>
