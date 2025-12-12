<?php
// ============================================
// UPDATE STATUS PESANAN - ELECTHREE E-COMMERCE
// ============================================

require_once '../../config/koneksi.php';
require_once '../../config/session.php';

// Cek login admin
require_admin_login('../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id_pesanan = (int)$_POST['id_pesanan'];
$status = clean_input($_POST['status']);

// Validasi status
$allowed_status = ['pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];
if (!in_array($status, $allowed_status)) {
    header("Location: detail.php?id=$id_pesanan&error=status");
    exit;
}

// Update status_pesanan (KOLOM YANG BENAR!)
$query = "UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "si", $status, $id_pesanan);

if (mysqli_stmt_execute($stmt)) {
    header("Location: detail.php?id=$id_pesanan&success=1");
} else {
    header("Location: detail.php?id=$id_pesanan&error=update");
}

mysqli_close($conn);
exit;
?>
