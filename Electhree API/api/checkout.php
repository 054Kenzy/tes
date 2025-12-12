<?php
// ============================================
// API CHECKOUT - ELECTHREE E-COMMERCE
// ============================================


// CORS Headers
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');


// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


require_once '../config/koneksi.php';
require_once '../config/session.php';


// Hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response('error', 'Method harus POST!');
}


// Cek login user
if (!is_logged_in()) {
    json_response('error', 'User belum login!');
}


$id_user = get_user_id();


// Get input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}


$alamat_pengiriman = isset($input['alamat_pengiriman']) ? clean_input($input['alamat_pengiriman']) : '';
$metode_pembayaran = isset($input['metode_pembayaran']) ? clean_input($input['metode_pembayaran']) : '';


// Validasi
if (empty($alamat_pengiriman)) {
    json_response('error', 'Alamat pengiriman harus diisi!');
}


if (empty($metode_pembayaran)) {
    json_response('error', 'Metode pembayaran harus dipilih!');
}


// Ambil data keranjang
$query_keranjang = "SELECT k.id_keranjang, k.id_produk, k.jumlah, p.nama_produk, p.harga, p.stok 
                    FROM keranjang k
                    JOIN produk p ON k.id_produk = p.id_produk
                    WHERE k.id_user = ?";
$stmt = mysqli_prepare($conn, $query_keranjang);
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$result_keranjang = mysqli_stmt_get_result($stmt);


if (mysqli_num_rows($result_keranjang) == 0) {
    json_response('error', 'Keranjang kosong!');
}


// Validasi stok dan hitung total
$items = [];
$total_harga = 0;


while ($item = mysqli_fetch_assoc($result_keranjang)) {
    // Cek stok
    if ($item['stok'] < $item['jumlah']) {
        json_response('error', 'Stok produk "' . $item['nama_produk'] . '" tidak mencukupi! Tersedia: ' . $item['stok']);
    }
    
    $subtotal = $item['harga'] * $item['jumlah'];
    $total_harga += $subtotal;
    
    $items[] = $item;
}


// Mulai transaksi database
mysqli_begin_transaction($conn);


try {
    // 1. Insert pesanan
    $status = 'pending';
    $query_pesanan = "INSERT INTO pesanan (id_user, total_harga, status_pesanan, alamat_pengiriman, metode_pembayaran, ongkir) 
                      VALUES (?, ?, ?, ?, ?, 0)";
    $stmt_pesanan = mysqli_prepare($conn, $query_pesanan);
    mysqli_stmt_bind_param($stmt_pesanan, "idsss", $id_user, $total_harga, $status, $alamat_pengiriman, $metode_pembayaran);
    
    if (!mysqli_stmt_execute($stmt_pesanan)) {
        throw new Exception('Gagal membuat pesanan!');
    }
    
    $id_pesanan = mysqli_insert_id($conn);
    
    // 2. Insert detail pesanan & kurangi stok
    foreach ($items as $item) {
        // Insert detail pesanan
        $query_detail = "INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, harga_satuan) 
                        VALUES (?, ?, ?, ?)";
        $stmt_detail = mysqli_prepare($conn, $query_detail);
        mysqli_stmt_bind_param($stmt_detail, "iiid", $id_pesanan, $item['id_produk'], $item['jumlah'], $item['harga']);
        
        if (!mysqli_stmt_execute($stmt_detail)) {
            throw new Exception('Gagal menyimpan detail pesanan!');
        }
        
        // Kurangi stok produk
        $query_stok = "UPDATE produk SET stok = stok - ? WHERE id_produk = ?";
        $stmt_stok = mysqli_prepare($conn, $query_stok);
        mysqli_stmt_bind_param($stmt_stok, "ii", $item['jumlah'], $item['id_produk']);
        
        if (!mysqli_stmt_execute($stmt_stok)) {
            throw new Exception('Gagal update stok produk!');
        }
    }
    
    // 3. Hapus keranjang
    $query_hapus = "DELETE FROM keranjang WHERE id_user = ?";
    $stmt_hapus = mysqli_prepare($conn, $query_hapus);
    mysqli_stmt_bind_param($stmt_hapus, "i", $id_user);
    
    if (!mysqli_stmt_execute($stmt_hapus)) {
        throw new Exception('Gagal menghapus keranjang!');
    }
    
    // Commit transaksi
    mysqli_commit($conn);
    
    json_response('success', 'Checkout berhasil! Pesanan Anda sedang diproses.', [
        'id_pesanan' => $id_pesanan,
        'total_harga' => (float)$total_harga,
        'total_item' => count($items),
        'status' => $status
    ]);
    
} catch (Exception $e) {
    // Rollback jika ada error
    mysqli_rollback($conn);
    json_response('error', 'Checkout gagal: ' . $e->getMessage());
}


mysqli_close($conn);
?>
