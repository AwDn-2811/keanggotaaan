<?php
session_start();
var_dump($_SESSION);
exit;



if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'warga') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../fungsi_bantuan/fungsi_pengajuan_bantuan.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id_warga'   => $_POST['id_warga'],
        'id_program' => $_POST['id_program'],
        'via'        => 'warga',
    ];

    $file = $_FILES['dokumen'] ?? null;

    if (ajukanBantuan($data, $file)) {
        header("Location: ../riwayat_pengajuan.php");
        exit;
    } else {
        echo "Gagal menyimpan pengajuan.";
    }
}
