<?php
require_once __DIR__ . "/../includes/db.php";

/* ============================================
   TAMBAH PROGRAM BANTUAN
============================================ */
function tambahProgram($data) {
    global $pdo;

    $sql = "INSERT INTO program_bantuan
            (nama_program, jenis_bantuan, persyaratan, kuota, jadwal_mulai, jadwal_selesai)
            VALUES (:nama_program, :jenis_bantuan, :persyaratan, :kuota, :mulai, :selesai)";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':nama_program'   => $data['nama_program'],
        ':jenis_bantuan'  => $data['jenis_bantuan'],
        ':persyaratan'    => $data['persyaratan'],
        ':kuota'          => $data['kuota'],
        ':mulai'          => $data['jadwal_mulai'],
        ':selesai'        => $data['jadwal_selesai']
    ]);
}

/* ============================================
   UPDATE PROGRAM
============================================ */
function updateProgram($id, $data) {
    global $pdo;

    $sql = "UPDATE program_bantuan SET
            nama_program   = :nama_program,
            jenis_bantuan  = :jenis_bantuan,
            persyaratan    = :persyaratan,
            kuota          = :kuota,
            jadwal_mulai   = :mulai,
            jadwal_selesai = :selesai
            WHERE id_program = :id";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':nama_program'  => $data['nama_program'],
        ':jenis_bantuan' => $data['jenis_bantuan'],
        ':persyaratan'   => $data['persyaratan'],
        ':kuota'         => $data['kuota'],
        ':mulai'         => $data['jadwal_mulai'],
        ':selesai'       => $data['jadwal_selesai'],
        ':id'            => $id
    ]);
}

/* ============================================
   HAPUS PROGRAM
============================================ */
function hapusProgram($id) {
    global $pdo;

    $stmt = $pdo->prepare("DELETE FROM program_bantuan WHERE id_program = ?");
    return $stmt->execute([$id]);
}

/* ============================================
   AMBIL SEMUA PROGRAM
============================================ */
function getProgram() {
    global $pdo;

    $stmt = $pdo->query("SELECT * FROM program_bantuan ORDER BY id_program DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ============================================
   AMBIL PROGRAM BY ID
============================================ */
function getProgramById($id) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM program_bantuan WHERE id_program = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
