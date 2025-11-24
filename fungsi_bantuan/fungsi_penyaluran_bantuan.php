<?php
require_once __DIR__ . "/../includes/db.php";

/* ============================================
   UPLOAD BUKTI PENYERAHAN
============================================ */
function uploadBuktiPenyerahan($file) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;

    $allowed = ['pdf','jpg','jpeg','png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) return null;

    if (!is_dir("uploads/bukti")) {
        mkdir("uploads/bukti", 0777, true);
    }

    $newName = "bukti_" . time() . "_" . rand(1000, 9999) . "." . $ext;
    $path = "uploads/bukti/" . $newName;

    move_uploaded_file($file['tmp_name'], __DIR__ . "/../$path");

    return $path;
}

/* ============================================
   CATAT PENYALURAN
============================================ */
function catatPenyaluran($data, $file = null) {
    global $pdo;

    $bukti = uploadBuktiPenyerahan($file);

    $sql = "INSERT INTO penyaluran_bantuan
            (id_pengajuan, tanggal_penyaluran, status_penyaluran, bukti_penyerahan, keterangan)
            VALUES (:id_pengajuan, :tgl, :status, :bukti, :ket)";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':id_pengajuan' => $data['id_pengajuan'],
        ':tgl'          => $data['tanggal_penyaluran'],
        ':status'       => $data['status_penyaluran'],
        ':bukti'        => $bukti,
        ':ket'          => $data['keterangan']
    ]);
}

/* ============================================
   GET RIWAYAT PENYALURAN WARGA
============================================ */
function getRiwayatWarga($id_warga) {
    global $pdo;

    $sql = "SELECT pb.*, pr.nama_program
            FROM penyaluran_bantuan pb
            JOIN pengajuan_bantuan pg ON pb.id_pengajuan = pg.id_pengajuan
            JOIN program_bantuan pr ON pg.id_program = pr.id_program
            WHERE pg.id_warga = :id_warga
            ORDER BY pb.tanggal_penyaluran DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_warga' => $id_warga]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
