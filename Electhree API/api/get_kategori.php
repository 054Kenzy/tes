<?php
// ============================================
// API GET KATEGORI - ELECTHREE E-COMMERCE
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../config/koneksi.php';

// Query semua kategori
$query = "SELECT k.*, COUNT(p.id_produk) as jumlah_produk 
          FROM kategori k 
          LEFT JOIN produk p ON k.id_kategori = p.id_kategori 
          GROUP BY k.id_kategori 
          ORDER BY k.nama_kategori ASC";
$result = mysqli_query($conn, $query);

$kategori_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $kategori_list[] = [
        'id_kategori' => (int)$row['id_kategori'],
        'nama_kategori' => $row['nama_kategori'],
        'deskripsi' => $row['deskripsi'],
        'jumlah_produk' => (int)$row['jumlah_produk']
    ];
}

json_response('success', 'Data kategori berhasil diambil', [
    'kategori' => $kategori_list,
    'total' => count($kategori_list)
]);

mysqli_close($conn);
?>
