<?php
// ============================================
// API PESANAN - ELECTHREE E-COMMERCE
// ============================================


// CORS Headers
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');


// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


require_once '../config/koneksi.php';
require_once '../config/session.php';


// Cek login user
if (!is_logged_in()) {
    json_response('error', 'User belum login!');
}


$id_user = get_user_id();


// Get parameter
$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;


// ============================================
// GET Detail Pesanan (by ID)
// ============================================
if ($id_pesanan > 0) {
    // Get pesanan
    $query_pesanan = "SELECT id_pesanan, id_user, alamat_pengiriman, metode_pembayaran, tanggal_pesan, total_harga, ongkir, status_pesanan 
                      FROM pesanan 
                      WHERE id_pesanan = ? AND id_user = ?";
    $stmt = mysqli_prepare($conn, $query_pesanan);
    mysqli_stmt_bind_param($stmt, "ii", $id_pesanan, $id_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $pesanan = mysqli_fetch_assoc($result);
    
    if (!$pesanan) {
        json_response('error', 'Pesanan tidak ditemukan!');
    }
    
    // Get detail pesanan
    $query_detail = "SELECT dp.id_detail, dp.id_produk, dp.jumlah, dp.harga_satuan, p.nama_produk, p.gambar 
                    FROM detail_pesanan dp
                    JOIN produk p ON dp.id_produk = p.id_produk
                    WHERE dp.id_pesanan = ?";
    $stmt_detail = mysqli_prepare($conn, $query_detail);
    mysqli_stmt_bind_param($stmt_detail, "i", $id_pesanan);
    mysqli_stmt_execute($stmt_detail);
    $result_detail = mysqli_stmt_get_result($stmt_detail);
    
    $items = [];
    while ($item = mysqli_fetch_assoc($result_detail)) {
        // Format gambar URL
        $gambar_url = '';
        if (!empty($item['gambar'])) {
            if (strpos($item['gambar'], 'drive.google.com') !== false) {
                $gambar_url = $item['gambar'];
            } else {
                $gambar_url = 'http://' . $_SERVER['HTTP_HOST'] . '/Electhree%20API/uploads/produk/' . $item['gambar'];
            }
        }
        
        $items[] = [
            'id_produk' => (int)$item['id_produk'],
            'nama_produk' => $item['nama_produk'],
            'harga' => (float)$item['harga_satuan'],
            'jumlah' => (int)$item['jumlah'],
            'subtotal' => (float)($item['harga_satuan'] * $item['jumlah']),
            'gambar' => $gambar_url
        ];
    }
    
    json_response('success', 'Detail pesanan berhasil diambil', [
        'pesanan' => [
            'id_pesanan' => (int)$pesanan['id_pesanan'],
            'tanggal_pesanan' => $pesanan['tanggal_pesan'],
            'total_harga' => (float)$pesanan['total_harga'],
            'status' => $pesanan['status_pesanan'],
            'alamat_pengiriman' => $pesanan['alamat_pengiriman'],
            'metode_pembayaran' => $pesanan['metode_pembayaran'],
            'items' => $items,
            'total_item' => count($items),
            'invoice_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/Electhree%20API/api/invoice.php?id=' . $pesanan['id_pesanan']
        ]
    ]);
}


// ============================================
// GET List Semua Pesanan User
// ============================================
else {
    $query = "SELECT p.id_pesanan, p.tanggal_pesan, p.total_harga, p.status_pesanan, p.metode_pembayaran, COUNT(dp.id_detail) as total_item
              FROM pesanan p
              LEFT JOIN detail_pesanan dp ON p.id_pesanan = dp.id_pesanan
              WHERE p.id_user = ?
              GROUP BY p.id_pesanan
              ORDER BY p.tanggal_pesan DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $pesanan_list = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $pesanan_list[] = [
            'id_pesanan' => (int)$row['id_pesanan'],
            'tanggal_pesanan' => $row['tanggal_pesan'],
            'total_harga' => (float)$row['total_harga'],
            'status' => $row['status_pesanan'],
            'total_item' => (int)$row['total_item'],
            'metode_pembayaran' => $row['metode_pembayaran']
        ];
    }
    
    json_response('success', 'Data pesanan berhasil diambil', [
        'pesanan' => $pesanan_list,
        'total' => count($pesanan_list)
    ]);
}


mysqli_close($conn);
?>
