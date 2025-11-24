<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../fungsi_bantuan/fungsi_program_bantuan.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id_program'];

    $data = [
        'nama_program'   => $_POST['nama_program'],
        'jenis_bantuan'  => $_POST['jenis_bantuan'],
        'persyaratan'    => $_POST['persyaratan'],
        'kuota'          => $_POST['kuota'],
        'jadwal_mulai'   => $_POST['jadwal_mulai'],
        'jadwal_selesai' => $_POST['jadwal_selesai'],
    ];

    if (updateProgram($id, $data)) {
        header("Location: program_bantuan_list.php?msg=Program+berhasil+diupdate");
        exit;
    } else {
        echo "Gagal mengupdate program.";
    }
}
