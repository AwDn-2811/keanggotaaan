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

    // Jika ID warga ada dalam URL, ambil data untuk ditampilkan di form edit
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Query untuk mengambil data warga berdasarkan ID
        $sql = "SELECT * FROM data_warga WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $warga = $stmt->fetch(PDO::FETCH_ASSOC);

        // Jika tidak ditemukan data warga dengan ID tersebut
        if (!$warga) {
            header("Location: data_warga.php");
            exit();
        }
    }

    // Proses untuk mengupdate data warga
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Ambil data dari form
        $nik = $_POST['nik'];
        $nama_warga = $_POST['nama_warga'];
        $tempat_lahir = $_POST['tempat_lahir'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $alamat_ktp = $_POST['alamat_ktp'];
        $alamat = $_POST['alamat'];
        $desa_kelurahan = $_POST['desa_kelurahan'];
        $kecamatan = $_POST['kecamatan'];
        $kabupaten = $_POST['kabupaten'];
        $provinsi = $_POST['provinsi'];
        $negara = $_POST['negara'];
        $rt = $_POST['rt'];
        $rw = $_POST['rw'];
        $agama = $_POST['agama'];
        $pendidikan_terakhir = $_POST['pendidikan_terakhir'];
        $pekerjaan = $_POST['pekerjaan'];
        $status_perkawinan = $_POST['status_perkawinan'];
        $status_tinggal = $_POST['status_tinggal'];

        // Query untuk mengupdate data warga
        $sql = "UPDATE data_warga SET nik = :nik, nama_warga = :nama_warga, tempat_lahir = :tempat_lahir, tanggal_lahir = :tanggal_lahir, jenis_kelamin = :jenis_kelamin, alamat_ktp = :alamat_ktp, alamat = :alamat, desa_kelurahan = :desa_kelurahan, kecamatan = :kecamatan, kabupaten = :kabupaten, provinsi = :provinsi, negara = :negara, rt = :rt, rw = :rw, agama = :agama, pendidikan_terakhir = :pendidikan_terakhir, pekerjaan = :pekerjaan, status_perkawinan = :status_perkawinan, status_tinggal = :status_tinggal WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nik' => $nik,
            ':nama_warga' => $nama_warga,
            ':tempat_lahir' => $tempat_lahir,
            ':tanggal_lahir' => $tanggal_lahir,
            ':jenis_kelamin' => $jenis_kelamin,
            ':alamat_ktp' => $alamat_ktp,
            ':alamat' => $alamat,
            ':desa_kelurahan' => $desa_kelurahan,
            ':kecamatan' => $kecamatan,
            ':kabupaten' => $kabupaten,
            ':provinsi' => $provinsi,
            ':negara' => $negara,
            ':rt' => $rt,
            ':rw' => $rw,
            ':agama' => $agama,
            ':pendidikan_terakhir' => $pendidikan_terakhir,
            ':pekerjaan' => $pekerjaan,
            ':status_perkawinan' => $status_perkawinan,
            ':status_tinggal' => $status_tinggal,
            ':id' => $id // Pastikan ID warga yang ingin diubah dikirimkan
        ]);

        // Redirect kembali ke halaman data_warga setelah update berhasil
        header("Location: data_warga.php?status=updated");
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Data Warga - Sistem Pendataan Warga</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Sistem Pendataan Warga</a>
            <div class="navbar-nav ml-auto">
                <span class="nav-item nav-link text-white">Hai, <?php echo $user_name; ?></span>
                <a class="nav-item nav-link text-white" href="dashboard_admin.php">Dasbor</a>
                <a class="nav-item nav-link text-white" href="logout.php">Keluar</a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <nav>
            <ul>
                <li><a href="data_warga.php">Data Warga</a></li>
                <li><a href="data_kk.php">Data Kartu Keluarga</a></li>
                <li><a href="data_mutasi.php">Data Mutasi</a></li>
                <li><a href="user.php">User</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main content -->
    <div class="main-content">
        <header>
            <h1>Ubah Data Warga</h1>
        </header>

        <!-- Form Edit Data Warga -->
        <div class="content">
            <form action="ubah_warga.php?id=<?php echo $warga['id']; ?>" method="POST">
                <h4>A. Data Pribadi</h4>
                <div class="form-group">
                    <label for="nik">NIK</label>
                    <input type="text" class="form-control" name="nik" value="<?php echo $warga['nik']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="nama_warga">Nama Warga</label>
                    <input type="text" class="form-control" name="nama_warga" value="<?php echo $warga['nama_warga']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="tempat_lahir">Tempat Lahir</label>
                    <input type="text" class="form-control" name="tempat_lahir" value="<?php echo $warga['tempat_lahir']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="tanggal_lahir">Tanggal Lahir</label>
                    <input type="date" class="form-control" name="tanggal_lahir" value="<?php echo $warga['tanggal_lahir']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="jenis_kelamin">Jenis Kelamin</label>
                    <select class="form-control" name="jenis_kelamin" required>
                        <option value="Laki-laki" <?php echo $warga['jenis_kelamin'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="Perempuan" <?php echo $warga['jenis_kelamin'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                    </select>
                </div>

                <h4>B. Data Alamat</h4>
                <div class="form-group">
                    <label for="alamat_ktp">Alamat KTP</label>
                    <textarea class="form-control" name="alamat_ktp" required><?php echo $warga['alamat_ktp']; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea class="form-control" name="alamat" required><?php echo $warga['alamat']; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="desa_kelurahan">Desa/Kelurahan</label>
                    <input type="text" class="form-control" name="desa_kelurahan" value="<?php echo $warga['desa_kelurahan']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="kecamatan">Kecamatan</label>
                    <input type="text" class="form-control" name="kecamatan" value="<?php echo $warga['kecamatan']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="kabupaten">Kabupaten/Kota</label>
                    <input type="text" class="form-control" name="kabupaten" value="<?php echo $warga['kabupaten']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="provinsi">Provinsi</label>
                    <input type="text" class="form-control" name="provinsi" value="<?php echo $warga['provinsi']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="negara">Negara</label>
                    <input type="text" class="form-control" name="negara" value="<?php echo $warga['negara']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="rt">RT</label>
                    <input type="text" class="form-control" name="rt" value="<?php echo $warga['rt']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="rw">RW</label>
                    <input type="text" class="form-control" name="rw" value="<?php echo $warga['rw']; ?>" required>
                </div>

                <h4>C. Data Lain-lain</h4>
                <div class="form-group">
                    <label for="agama">Agama</label>
                    <select class="form-control" name="agama" required>
                        <option value="Islam" <?php echo $warga['agama'] == 'Islam' ? 'selected' : ''; ?>>Islam</option>
                        <option value="Kristen" <?php echo $warga['agama'] == 'Kristen' ? 'selected' : ''; ?>>Kristen</option>
                        <option value="Hindu" <?php echo $warga['agama'] == 'Hindu' ? 'selected' : ''; ?>>Hindu</option>
                        <option value="Buddha" <?php echo $warga['agama'] == 'Buddha' ? 'selected' : ''; ?>>Buddha</option>
                        <option value="Konghucu" <?php echo $warga['agama'] == 'Konghucu' ? 'selected' : ''; ?>>Konghucu</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pendidikan_terakhir">Pendidikan Terakhir</label>
                    <select class="form-control" name="pendidikan_terakhir" required>
                        <option value="SD" <?php echo $warga['pendidikan_terakhir'] == 'SD' ? 'selected' : ''; ?>>SD</option>
                        <option value="SMP" <?php echo $warga['pendidikan_terakhir'] == 'SMP' ? 'selected' : ''; ?>>SMP</option>
                        <option value="SMA" <?php echo $warga['pendidikan_terakhir'] == 'SMA' ? 'selected' : ''; ?>>SMA</option>
                        <option value="D3" <?php echo $warga['pendidikan_terakhir'] == 'D3' ? 'selected' : ''; ?>>D3</option>
                        <option value="S1" <?php echo $warga['pendidikan_terakhir'] == 'S1' ? 'selected' : ''; ?>>S1</option>
                        <option value="S2" <?php echo $warga['pendidikan_terakhir'] == 'S2' ? 'selected' : ''; ?>>S2</option>
                        <option value="S3" <?php echo $warga['pendidikan_terakhir'] == 'S3' ? 'selected' : ''; ?>>S3</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pekerjaan">Pekerjaan</label>
                    <input type="text" class="form-control" name="pekerjaan" value="<?php echo $warga['pekerjaan']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="status_perkawinan">Status Perkawinan</label>
                    <select class="form-control" name="status_perkawinan" required>
                        <option value="Belum Menikah" <?php echo $warga['status_perkawinan'] == 'Belum Menikah' ? 'selected' : ''; ?>>Belum Menikah</option>
                        <option value="Menikah" <?php echo $warga['status_perkawinan'] == 'Menikah' ? 'selected' : ''; ?>>Menikah</option>
                        <option value="Duda/Janda" <?php echo $warga['status_perkawinan'] == 'Duda/Janda' ? 'selected' : ''; ?>>Duda/Janda</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status_tinggal">Status Tinggal</label>
                    <select class="form-control" name="status_tinggal" required>
                        <option value="Tinggal Bersama Keluarga" <?php echo $warga['status_tinggal'] == 'Tinggal Bersama Keluarga' ? 'selected' : ''; ?>>Tinggal Bersama Keluarga</option>
                        <option value="Tinggal Mandiri" <?php echo $warga['status_tinggal'] == 'Tinggal Mandiri' ? 'selected' : ''; ?>>Tinggal Mandiri</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
