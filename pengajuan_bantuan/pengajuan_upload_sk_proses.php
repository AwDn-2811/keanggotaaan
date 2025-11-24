<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../fungsi_bantuan/fungsi_pengajuan_bantuan.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pengajuan = (int) $_POST['id_pengajuan'];
    $no_surat     = $_POST['no_surat_keputusan'];
    $file         = $_FILES['file_sk'] ?? null;

    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        if (unggahSK($id_pengajuan, $no_surat, $file)) {
            header("Location: pengajuan_verifikasi.php?id={$id_pengajuan}");
            exit;
        } else {
            echo "Gagal mengupload SK.";
        }
    } else {
        echo "File SK tidak valid.";
    }
}
