<?php
require_once __DIR__ . "/../includes/db.php";

/* ============================================
   UPLOAD FILE (DOKUMEN / SK)
============================================ */
function uploadFile($file, $folder) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;

    $allowed = ['pdf','jpg','jpeg','png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) return null;

    if (!is_dir("uploads/$folder")) {
        mkdir("uploads/$folder", 0777, true);
    }

    $newName = $folder . "_" . time() . "_" . rand(1000,9999) . "." . $ext;
    $path = "uploads/$folder/" . $newName;

    move_uploaded_file($file['tmp_name'], __DIR__ . "/../$path");

    return $path;
}

/* ============================================
   AJUKAN BANTUAN (WARGA / ADMIN)
============================================ */
function ajukanBantuan($data, $file = null) {
    global $pdo;

    $dokumenPath = uploadFile($file, "dokumen");

    $sql = "INSERT INTO pengajuan_bantuan
            (id_warga, id_program, pengajuan_via, dokumen_path)
            VALUES (:id_warga, :id_program, :via, :dok)";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':id_warga'   => $data['id_warga'],
        ':id_program' => $data['id_program'],
        ':via'        => $data['via'],
        ':dok'        => $dokumenPath
    ]);
}

/* ============================================
   VERIFIKASI PENGAJUAN
============================================ */
function verifikasiPengajuan($id, $status, $catatan, $verifikator_id = null) {
    global $pdo;

    $sql = "UPDATE pengajuan_bantuan SET
            status_pengajuan = :status,
            catatan_verifikasi = :catatan,
            verifikator_id = :verifikator
            WHERE id_pengajuan = :id";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':status'      => $status,
        ':catatan'     => $catatan,
        ':verifikator' => $verifikator_id,
        ':id'          => $id
    ]);
}

/* ============================================
   UPLOAD SK
============================================ */
function unggahSK($id, $no_sk, $file) {
    global $pdo;

    $skPath = uploadFile($file, "sk");
    if (!$skPath) return false;

    $sql = "UPDATE pengajuan_bantuan SET
            no_surat_keputusan = :no_sk,
            file_surat_keputusan = :file_sk
            WHERE id_pengajuan = :id";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':no_sk'   => $no_sk,
        ':file_sk' => $skPath,
        ':id'      => $id
    ]);
}
?>
