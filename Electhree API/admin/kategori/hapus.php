<?php
// ============================================
// HAPUS KATEGORI - ELECTHREE E-COMMERCE
// ============================================

require_once '../../config/koneksi.php';
require_once '../../config/session.php';

// Cek login admin
require_admin_login('../login.php');

// Ambil ID kategori dari URL
$id_kategori = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_kategori > 0) {
    // Cek apakah kategori masih dipakai produk
    $query_cek = "SELECT COUNT(*) as total FROM produk WHERE id_kategori = ?";
    $stmt_cek = mysqli_prepare($conn, $query_cek);
    mysqli_stmt_bind_param($stmt_cek, "i", $id_kategori);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);
    $row = mysqli_fetch_assoc($result_cek);
    
    if ($row['total'] > 0) {
        // Kategori masih dipakai produk, tidak bisa dihapus
        header("Location: index.php?error=used");
        exit;
    }
    
    // Hapus kategori
    $query = "DELETE FROM kategori WHERE id_kategori = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_kategori);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?success=hapus");
    } else {
        header("Location: index.php?error=delete");
    }
} else {
    header("Location: index.php");
}

mysqli_close($conn);
exit;
?>
