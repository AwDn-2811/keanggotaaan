<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../includes/db.php";

if (!isset($_GET['id'])) {
    header("Location: penyaluran_list.php");
    exit();
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM penyaluran_bantuan WHERE id_penyaluran = :id");
$stmt->execute([':id' => $id]);

header("Location: penyaluran_list.php");
exit;
