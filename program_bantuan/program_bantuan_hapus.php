<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../fungsi_bantuan/fungsi_program_bantuan.php";

if (!isset($_GET['id'])) {
    header("Location: program_bantuan_list.php");
    exit();
}

$id = (int) $_GET['id'];
hapusProgram($id);

header("Location: program_bantuan_list.php?msg=Program+berhasil+dihapus");
exit;
