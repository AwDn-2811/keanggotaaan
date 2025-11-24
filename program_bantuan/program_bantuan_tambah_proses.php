<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../fungsi_bantuan/fungsi_program_bantuan.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nama_program'   => $_POST['nama_program'],
        'jenis_bantuan'  => $_POST['jenis_bantuan'],
        'persyaratan'    => $_POST['persyaratan'],
        'kuota'          => $_POST['kuota'],
        'jadwal_mulai'   => $_POST['jadwal_mulai'],
        'jadwal_selesai' => $_POST['jadwal_selesai'],
    ];

    if (tambahProgram($data)) {
        header("Location: program_bantuan_list.php?msg=Program+berhasil+ditambahkan");
        exit;
    } else {
        echo "Gagal menyimpan program.";
    }
}
