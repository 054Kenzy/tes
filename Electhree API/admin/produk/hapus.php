<?php
// ============================================
// HAPUS PRODUK - ELECTHREE E-COMMERCE
// ============================================

require_once '../../config/koneksi.php';
require_once '../../config/session.php';

// Cek login admin
require_admin_login('../login.php');

// Ambil ID produk
$id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_produk > 0) {
    // Get data produk (untuk hapus gambar)
    $query = "SELECT gambar FROM produk WHERE id_produk = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_produk);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $produk = mysqli_fetch_assoc($result);
    
    if ($produk) {
        // Hapus produk dari database
        $query_delete = "DELETE FROM produk WHERE id_produk = ?";
        $stmt_delete = mysqli_prepare($conn, $query_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $id_produk);
        
        if (mysqli_stmt_execute($stmt_delete)) {
            // Hapus file gambar jika ada
            if (!empty($produk['gambar'])) {
                $file_path = '../../uploads/produk/' . $produk['gambar'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            header("Location: index.php?success=hapus");
        } else {
            header("Location: index.php?error=delete");
        }
    } else {
        header("Location: index.php");
    }
} else {
    header("Location: index.php");
}

mysqli_close($conn);
exit;
?>
