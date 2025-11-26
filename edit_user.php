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

    // Ambil data user yang akan diedit
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User tidak ditemukan.");
    }

    // Jika form disubmit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama_depan = $_POST['nama_depan'];
        $nama_belakang = $_POST['nama_belakang'];
        $email = $_POST['email'];
        $role = $_POST['role'];

        $updateSql = "UPDATE users SET 
                        nama_depan = :nama_depan,
                        nama_belakang = :nama_belakang,
                        email = :email,
                        role = :role
                      WHERE id = :id";

        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            ':nama_depan' => $nama_depan,
            ':nama_belakang' => $nama_belakang,
            ':email' => $email,
            ':role' => $role,
            ':id' => $id
        ]);

        $success_message = "Data user berhasil diperbarui!";
        
        // Refresh data
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow-sm">
        <div class="card-body">

            <h3>Edit User</h3>
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
                    <input type="text" name="nama_depan" value="<?= htmlspecialchars($user['nama_depan']) ?>"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Belakang</label>
                    <input type="text" name="nama_belakang" value="<?= htmlspecialchars($user['nama_belakang']) ?>"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email User</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Role User</label>
                    <select name="role" class="form-control" required>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="warga" <?= $user['role'] === 'warga' ? 'selected' : '' ?>>Warga</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>

            </form>

        </div>
    </div>

</div>

</body>
</html>
