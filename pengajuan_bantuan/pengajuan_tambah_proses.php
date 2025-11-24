<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../fungsi_bantuan/fungsi_pengajuan_bantuan.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id_warga'   => $_POST['id_warga'],
        'id_program' => $_POST['id_program'],
        'via'        => $_POST['pengajuan_via'] ?? 'admin',
    ];

    $file = $_FILES['dokumen'] ?? null;

    if (ajukanBantuan($data, $file)) {
        header("Location: pengajuan_list.php");
        exit;
    } else {
        echo "Gagal menyimpan pengajuan.";
    }
}
