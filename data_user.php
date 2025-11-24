<?php
session_start();

// Memeriksa apakah pengguna sudah login, jika tidak, arahkan ke halaman login
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['user_name']; // Ambil nama pengguna dari session

// Koneksi ke database
$host = 'localhost';
$dbname = 'keanggotaan_warga';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ambil data user
    $sql = "SELECT * FROM users"; // Pastikan ada tabel 'users' untuk data user
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data_user = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User - Sistem Pendataan Warga</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Memuat Font Awesome untuk ikon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Sistem Pendataan Warga</a>
            <div class="navbar-nav ml-auto">
                <span class="nav-item nav-link text-white">Hai, <?php echo $user_name; ?></span>
                <a class="nav-item nav-link text-white" href="logout.php">Keluar</a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <nav>
            <ul>
                <li><a href="dashboard_admin.php">Dasbor</a></li>
                <li><a href="data_warga.php">Data Warga</a></li>
                <li><a href="data_kk.php">Data Kartu Keluarga</a></li>
                <li><a href="data_mutasi.php">Data Mutasi</a></li>
                <li><a href="data_user.php">User</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main content -->
    <div class="main-content">
        <header>
            <h1>Data User</h1>
        </header>

        <!-- Tombol Fitur -->
        <div class="content mb-3">
            <a href="tambah_user.php" class="btn btn-success">Tambah</a>
            <a href="#" class="btn btn-info">Lihat Data</a>
            <a href="#" class="btn btn-warning">Refresh</a>
            <a href="#" class="btn btn-primary">Cetak</a>
        </div>

        <!-- Tabel Data User -->
        <div class="content">
            <table id="dataTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Depan</th>
                        <th>Nama Belakang</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($data_user): ?>
                        <?php foreach ($data_user as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['nama_depan']; ?></td>
                                <td><?php echo $user['nama_belakang']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['role']; ?></td>
                                <td>
                                    <!-- Dropdown Aksi -->
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item" href="edit_user.php?id=<?php echo $user['id']; ?>"><i class="fas fa-edit"></i> Ubah</a>
                                            <a class="dropdown-item" href="hapus_user.php?id=<?php echo $user['id']; ?>"><i class="fas fa-trash-alt"></i> Hapus</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data user.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Statistik Data User -->
        <div class="content mb-3">
            <div class="alert alert-info">
                <h4>Statistik Data User</h4>
                <p><strong>Total User: </strong> <?php echo count($data_user); ?> orang</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            // Mengaktifkan DataTables dengan fitur pencarian dan pagination
            $('#dataTable').DataTable({
                "pagingType": "full_numbers", // Full pagination (previous/next/first/last)
                "pageLength": 10, // Default untuk menampilkan 10 data per halaman
                "searching": true, // Aktifkan pencarian
                "lengthMenu": [10, 25, 50, 100], // Pilihan jumlah data per halaman
                "language": {
                    "search": "Cari:" // Label pencarian
                }
            });
        });
    </script>
</body>
</html>
