<?php
session_start();

// Hanya boleh diakses warga yang sudah login
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'warga') {
    header("Location: login.php");
    exit();
}

$nik        = $_SESSION['nik'] ?? null;
$user_name  = $_SESSION['user_name'] ?? '';

$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

$error_message   = '';
$success_message = '';
$user            = null;

if (!$nik) {
    $error_message = 'NIK tidak ditemukan di session. Silakan login ulang atau hubungi admin.';
} else {
    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Jika form disubmit
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_depan      = trim($_POST['nama_depan'] ?? '');
            $nama_belakang   = trim($_POST['nama_belakang'] ?? '');
            $email_baru      = trim($_POST['email'] ?? '');
            $new_password    = $_POST['new_password'] ?? '';
            $confirm_password= $_POST['confirm_password'] ?? '';

            // Validasi dasar
            if ($nama_depan === '' || $nama_belakang === '' || $email_baru === '') {
                $error_message = 'Nama depan, nama belakang, dan email tidak boleh kosong.';
            } elseif (!filter_var($email_baru, FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Format email tidak valid.';
            } elseif ($new_password !== '' && $new_password !== $confirm_password) {
                $error_message = 'Password baru dan konfirmasi password tidak sama.';
            } else {
                // Cek apakah email sudah dipakai user lain
                $cek = $pdo->prepare(
                    "SELECT id FROM users 
                     WHERE email = :email AND nik <> :nik LIMIT 1"
                );
                $cek->execute([
                    ':email' => $email_baru,
                    ':nik'   => $nik
                ]);
                if ($cek->fetch()) {
                    $error_message = 'Email tersebut sudah digunakan oleh pengguna lain.';
                } else {
                    // Siapkan query update
                    if ($new_password !== '') {
                        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                        $sqlUpdate = "UPDATE users 
                                      SET nama_depan = :nama_depan,
                                          nama_belakang = :nama_belakang,
                                          email = :email,
                                          password = :password
                                      WHERE nik = :nik";
                        $stmt = $pdo->prepare($sqlUpdate);
                        $stmt->execute([
                            ':nama_depan'   => $nama_depan,
                            ':nama_belakang'=> $nama_belakang,
                            ':email'        => $email_baru,
                            ':password'     => $hashed,
                            ':nik'          => $nik
                        ]);
                    } else {
                        $sqlUpdate = "UPDATE users 
                                      SET nama_depan = :nama_depan,
                                          nama_belakang = :nama_belakang,
                                          email = :email
                                      WHERE nik = :nik";
                        $stmt = $pdo->prepare($sqlUpdate);
                        $stmt->execute([
                            ':nama_depan'   => $nama_depan,
                            ':nama_belakang'=> $nama_belakang,
                            ':email'        => $email_baru,
                            ':nik'          => $nik
                        ]);
                    }

                    // Update juga session supaya sinkron
                    $_SESSION['user_name'] = $nama_depan;
                    $_SESSION['email']     = $email_baru;

                    $success_message = 'Perubahan akun berhasil disimpan.';
                }
            }
        }

        // Ambil data user terbaru berdasarkan NIK
        $qUser = $pdo->prepare("SELECT * FROM users WHERE nik = :nik LIMIT 1");
        $qUser->execute([':nik' => $nik]);
        $user = $qUser->fetch(PDO::FETCH_ASSOC);

        if (!$user && $error_message === '') {
            $error_message = 'Data akun pengguna tidak ditemukan di tabel users.';
        }

    } catch (PDOException $e) {
        $error_message = 'Error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Akun - Sistem Pendataan Warga</title>

    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        body { background-color: #f5f6fa; }
        .layout-wrapper { min-height: 100vh; }
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #1f2937;
            color: #e5e7eb;
        }
        .sidebar .brand {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar .brand h4 {
            font-size: 1.1rem;
            margin: 0;
        }
        .sidebar .nav-link {
            color: #e5e7eb;
            padding: .6rem 1.25rem;
            font-size: .95rem;
            border-radius: 0;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.06);
        }
        .sidebar .nav-link.active {
            background: #111827;
            font-weight: 600;
        }
        .sidebar .nav-section-title {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: .75rem 1.25rem .25rem;
            opacity: .6;
        }
        .main-content {
            flex: 1;
            padding: 1.5rem 1.5rem 2rem;
        }
    </style>
</head>
<body>

<div class="d-flex layout-wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar d-flex flex-column">
        <div class="brand">
            <h4>Sistem Warga</h4>
            <small class="text-muted">Dashboard Warga</small>
        </div>

        <div class="flex-grow-1">
            <div class="nav-section-title">Menu Utama</div>
<nav class="nav flex-column">
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard_warga.php' ? 'active' : '' ?>" href="dashboard_warga.php">
        🏠 Dasbor
    </a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'kk_saya.php' ? 'active' : '' ?>" href="kk_saya.php">
        📄 Kartu Keluarga Saya
    </a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'pengajuan_mutasi.php' ? 'active' : '' ?>" href="pengajuan_mutasi.php">
        🔁 Pengajuan Mutasi
    </a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'pengaturan_akun.php' ? 'active' : '' ?>" href="pengaturan_akun.php">
        ⚙️ Pengaturan Akun
    </a>
</nav>


            <div class="nav-section-title">Lainnya</div>
            <nav class="nav flex-column">
                <a class="nav-link text-danger" href="logout.php">
                    🚪 Logout
                </a>
            </nav>
        </div>

        <div class="p-3 border-top border-secondary">
            <small class="d-block">
                Login sebagai:<br>
                <strong><?= htmlspecialchars($user_name) ?></strong><br>
                <span class="text-muted">NIK: <?= htmlspecialchars($nik ?? '-') ?></span>
            </small>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <div class="mb-4">
            <h3 class="mb-0">Pengaturan Akun</h3>
            <small class="text-muted">Ubah nama, email, dan password akun kamu</small>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?= $success_message ?>
            </div>
        <?php endif; ?>

        <?php if ($user): ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="pengaturan_akun.php">
                        <div class="mb-3">
                            <label for="nama_depan" class="form-label">Nama Depan</label>
                            <input type="text" class="form-control" id="nama_depan"
                                   name="nama_depan"
                                   value="<?= htmlspecialchars($user['nama_depan']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="nama_belakang" class="form-label">Nama Belakang</label>
                            <input type="text" class="form-control" id="nama_belakang"
                                   name="nama_belakang"
                                   value="<?= htmlspecialchars($user['nama_belakang']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email"
                                   name="email"
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <hr>

                        <div class="mb-2">
                            <label class="form-label">Password Baru (opsional)</label>
                            <input type="password" class="form-control" id="new_password"
                                   name="new_password" placeholder="Kosongkan jika tidak ingin mengubah password">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="confirm_password"
                                   name="confirm_password">
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </main>
</div>

</body>
</html>
