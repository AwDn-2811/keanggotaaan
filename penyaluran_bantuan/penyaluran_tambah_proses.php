<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../fungsi_bantuan/fungsi_penyaluran_bantuan.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id_pengajuan'       => $_POST['id_pengajuan'],
        'tanggal_penyaluran' => $_POST['tanggal_penyaluran'],
        'status_penyaluran'  => $_POST['status_penyaluran'],
        'keterangan'         => $_POST['keterangan'],
    ];

    $fileBukti = $_FILES['bukti_penyerahan'] ?? null;

    if (catatPenyaluran($data, $fileBukti)) {
        header("Location: penyaluran_list.php");
        exit;
    } else {
        echo "Gagal menyimpan penyaluran.";
    }
}
