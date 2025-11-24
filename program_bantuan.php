<?php
// fungsi_program_bantuan.php
require_once 'db.php';

// 1. Tambah Program Bantuan
function tambahProgramBantuan($data)
{
    global $pdo;

    $sql = "INSERT INTO program_bantuan 
            (nama_program, jenis_bantuan, persyaratan, kuota, jadwal_mulai, jadwal_selesai)
            VALUES (:nama_program, :jenis_bantuan, :persyaratan, :kuota, :jadwal_mulai, :jadwal_selesai)";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':nama_program'   => $data['nama_program'],
        ':jenis_bantuan'  => $data['jenis_bantuan'],
        ':persyaratan'    => $data['persyaratan'],
        ':kuota'          => $data['kuota'],
        ':jadwal_mulai'   => $data['jadwal_mulai'],   // format: YYYY-MM-DD
        ':jadwal_selesai' => $data['jadwal_selesai']  // format: YYYY-MM-DD
    ]);
}

// 2. Update Program Bantuan
function updateProgramBantuan($id_program, $data)
{
    global $pdo;

    $sql = "UPDATE program_bantuan SET 
                nama_program   = :nama_program,
                jenis_bantuan  = :jenis_bantuan,
                persyaratan    = :persyaratan,
                kuota          = :kuota,
                jadwal_mulai   = :jadwal_mulai,
                jadwal_selesai = :jadwal_selesai
            WHERE id_program = :id_program";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':id_program'     => $id_program,
        ':nama_program'   => $data['nama_program'],
        ':jenis_bantuan'  => $data['jenis_bantuan'],
        ':persyaratan'    => $data['persyaratan'],
        ':kuota'          => $data['kuota'],
        ':jadwal_mulai'   => $data['jadwal_mulai'],
        ':jadwal_selesai' => $data['jadwal_selesai']
    ]);
}

// 3. Hapus Program Bantuan
function hapusProgramBantuan($id_program)
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM program_bantuan WHERE id_program = :id_program");
    return $stmt->execute([':id_program' => $id_program]);
}

// 4. Ambil Semua Program Bantuan
function getSemuaProgramBantuan()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM program_bantuan ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 5. Ambil Satu Program
function getProgramBantuanById($id_program)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM program_bantuan WHERE id_program = :id_program");
    $stmt->execute([':id_program' => $id_program]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
