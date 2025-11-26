<?php
session_start();




if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'warga') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../fungsi_bantuan/fungsi_pengajuan_bantuan.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil id_warga dari SESSION, bukan dari input form
    // supaya tidak bisa dimanipulasi
    $data = [
        'id_warga'   => $_SESSION['id_warga'],
        'id_program' => $_POST['id_program'],
        'via'        => 'warga',
    ];

    // Ambil file dokumen
    $file = $_FILES['dokumen'] ?? null;

    // Proses penyimpanan
    if (ajukanBantuan($data, $file)) {
        header("Location: ../riwayat_pengajuan.php?status=sukses");
        exit;
    } else {
        echo "Gagal menyimpan pengajuan.";
    }
}
