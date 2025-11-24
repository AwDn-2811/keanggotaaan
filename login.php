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
                // Login berhasil, simpan nama depan, role, dan NIK ke dalam session
                $_SESSION['user_name'] = $user['nama_depan']; // Simpan nama depan di session
                $_SESSION['role']      = $user['role'];       // Menyimpan role di session
                $_SESSION['nik']       = $user['nik'];        // ← PENTING untuk dashboard_warga
                $_SESSION['email']     = $user['email'];      // ← baru, buat backup kalau ada file lain yang butuh


                // Redirect berdasarkan role pengguna
                if ($user['role'] == 'admin') {
                    header("Location: dashboard_admin.php"); // Dashboard untuk admin
                } else {
                    header("Location: dashboard_warga.php"); // Dashboard untuk warga
                }
                exit; // Pastikan tidak ada kode lain yang dijalankan setelah redirect
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
