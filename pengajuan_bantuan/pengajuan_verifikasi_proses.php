<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../fungsi_bantuan/fungsi_pengajuan_bantuan.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pengajuan     = (int) $_POST['id_pengajuan'];
    $status_pengajuan = $_POST['status_pengajuan'];
    $catatan          = $_POST['catatan_verifikasi'] ?? '';

    // kalau ada id user admin di session, pakai; kalau tidak, null
    $verifikator_id = $_SESSION['user_id'] ?? null;

    if (verifikasiPengajuan($id_pengajuan, $status_pengajuan, $catatan, $verifikator_id)) {
        header("Location: pengajuan_verifikasi.php?id={$id_pengajuan}");
        exit;
    } else {
        echo "Gagal menyimpan verifikasi.";
    }
}
