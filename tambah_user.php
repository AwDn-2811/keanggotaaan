<?php
session_start();

// Cek akses admin
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

$error_message = '';
$success_message = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama_depan = trim($_POST['nama_depan']);
        $nama_belakang = trim($_POST['nama_belakang']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $password_plain = $_POST['password'];

        // Validasi sederhana
        if ($nama_depan === '' || $nama_belakang === '' || $email === '' || $password_plain === '') {
            $error_message = "Semua field harus diisi.";
        } else {

            // Cek apakah email sudah dipakai
            $checkSql = "SELECT id FROM users WHERE email = :email";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([':email' => $email]);

            if ($checkStmt->rowCount() > 0) {
                $error_message = "Email sudah terdaftar. Gunakan email lain.";
            } else {

                // Hash password
                $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

                // Insert data
                $insertSql = "INSERT INTO users 
                            (nama_depan, nama_belakang, email, password, role) 
                            VALUES 
                            (:nama_depan, :nama_belakang, :email, :password, :role)";

                $insertStmt = $pdo->prepare($insertSql);
                $insertStmt->execute([
                    ':nama_depan' => $nama_depan,
                    ':nama_belakang' => $nama_belakang,
                    ':email' => $email,
                    ':password' => $password_hash,
                    ':role' => $role
                ]);

                $success_message = "User berhasil ditambahkan!";
            }
        }
    }

} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah User</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow-sm">
        <div class="card-body">

            <h3>Tambah User Baru</h3>
            <a href="data_user.php" class="btn btn-secondary btn-sm mb-3">Kembali</a>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= $success_message ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Nama Depan</label>
                    <input type="text" name="nama_depan" class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Belakang</label>
                    <input type="text" name="nama_belakang" class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email User</label>
                    <input type="email" name="email" class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password User</label>
                    <input type="password" name="password" class="form-control"
                           minlength="6"
                           required>
                    <small class="text-muted">Minimal 6 karakter.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Role User</label>
                    <select name="role" class="form-control" required>
                        <option value="admin">Admin</option>
                        <option value="warga">Warga</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Simpan User</button>

            </form>

        </div>
    </div>

</div>

</body>
</html>
