<?php
// ============================================
// API KERANJANG - ELECTHREE E-COMMERCE
// ============================================

// CORS Headers - HARUS DI AWAL SEBELUM SESSION!
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/koneksi.php';
require_once '../config/session.php';

// Get method dan input
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_REQUEST;
}

// Cek login user
if (!is_logged_in()) {
    json_response('error', 'User belum login! Silakan login terlebih dahulu.');
}

$id_user = get_user_id();

// ============================================
// GET - Lihat Keranjang
// ============================================
if ($method === 'GET') {
    $query = "SELECT k.*, p.nama_produk, p.harga, p.stok, p.gambar, p.id_kategori
              FROM keranjang k
              JOIN produk p ON k.id_produk = p.id_produk
              WHERE k.id_user = ?
              ORDER BY k.id_keranjang DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $keranjang = [];
    $total_harga = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Format gambar URL
        $gambar_url = '';
        if (!empty($row['gambar'])) {
            if (strpos($row['gambar'], 'drive.google.com') !== false) {
                $gambar_url = $row['gambar'];
            } else {
                $gambar_url = 'http://' . $_SERVER['HTTP_HOST'] . '/Electhree%20API/uploads/produk/' . $row['gambar'];
            }
        }
        
        $subtotal = $row['harga'] * $row['jumlah'];
        $total_harga += $subtotal;
        
        $keranjang[] = [
            'id_keranjang' => (int)$row['id_keranjang'],
            'id_produk' => (int)$row['id_produk'],
            'nama_produk' => $row['nama_produk'],
            'harga' => (float)$row['harga'],
            'jumlah' => (int)$row['jumlah'],
            'subtotal' => (float)$subtotal,
            'stok_tersedia' => (int)$row['stok'],
            'gambar' => $gambar_url
        ];
    }
    
    json_response('success', 'Data keranjang berhasil diambil', [
        'keranjang' => $keranjang,
        'total_item' => count($keranjang),
        'total_harga' => (float)$total_harga
    ]);
}

// ============================================
// POST - Tambah ke Keranjang
// ============================================
elseif ($method === 'POST') {
    $id_produk = isset($input['id_produk']) ? (int)$input['id_produk'] : 0;
    $jumlah = isset($input['jumlah']) ? (int)$input['jumlah'] : 1;
    
    if ($id_produk == 0) {
        json_response('error', 'ID Produk harus diisi!');
    }
    
    if ($jumlah < 1) {
        json_response('error', 'Jumlah minimal 1!');
    }
    
    // Cek stok produk
    $query_produk = "SELECT stok, nama_produk FROM produk WHERE id_produk = ?";
    $stmt = mysqli_prepare($conn, $query_produk);
    mysqli_stmt_bind_param($stmt, "i", $id_produk);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $produk = mysqli_fetch_assoc($result);
    
    if (!$produk) {
        json_response('error', 'Produk tidak ditemukan!');
    }
    
    if ($produk['stok'] < $jumlah) {
        json_response('error', 'Stok tidak mencukupi! Stok tersedia: ' . $produk['stok']);
    }
    
    // Cek apakah produk sudah ada di keranjang
    $query_check = "SELECT id_keranjang, jumlah FROM keranjang WHERE id_user = ? AND id_produk = ?";
    $stmt_check = mysqli_prepare($conn, $query_check);
    mysqli_stmt_bind_param($stmt_check, "ii", $id_user, $id_produk);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $existing = mysqli_fetch_assoc($result_check);
    
    if ($existing) {
        // Update jumlah jika sudah ada
        $jumlah_baru = $existing['jumlah'] + $jumlah;
        
        if ($produk['stok'] < $jumlah_baru) {
            json_response('error', 'Stok tidak mencukupi! Stok tersedia: ' . $produk['stok'] . ', di keranjang: ' . $existing['jumlah']);
        }
        
        $query_update = "UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ?";
        $stmt_update = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt_update, "ii", $jumlah_baru, $existing['id_keranjang']);
        
        if (mysqli_stmt_execute($stmt_update)) {
            json_response('success', 'Jumlah produk di keranjang berhasil diupdate!');
        } else {
            json_response('error', 'Gagal update keranjang!');
        }
    } else {
        // Insert baru
        $query_insert = "INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES (?, ?, ?)";
        $stmt_insert = mysqli_prepare($conn, $query_insert);
        mysqli_stmt_bind_param($stmt_insert, "iii", $id_user, $id_produk, $jumlah);
        
        if (mysqli_stmt_execute($stmt_insert)) {
            json_response('success', 'Produk berhasil ditambahkan ke keranjang!');
        } else {
            json_response('error', 'Gagal menambahkan ke keranjang!');
        }
    }
}

// ============================================
// PUT - Update Jumlah
// ============================================
elseif ($method === 'PUT') {
    $id_keranjang = isset($input['id_keranjang']) ? (int)$input['id_keranjang'] : 0;
    $jumlah = isset($input['jumlah']) ? (int)$input['jumlah'] : 0;
    
    if ($id_keranjang == 0 || $jumlah < 1) {
        json_response('error', 'ID Keranjang dan jumlah harus valid!');
    }
    
    // Cek kepemilikan keranjang
    $query_check = "SELECT k.*, p.stok FROM keranjang k 
                    JOIN produk p ON k.id_produk = p.id_produk 
                    WHERE k.id_keranjang = ? AND k.id_user = ?";
    $stmt = mysqli_prepare($conn, $query_check);
    mysqli_stmt_bind_param($stmt, "ii", $id_keranjang, $id_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $keranjang = mysqli_fetch_assoc($result);
    
    if (!$keranjang) {
        json_response('error', 'Keranjang tidak ditemukan!');
    }
    
    if ($keranjang['stok'] < $jumlah) {
        json_response('error', 'Stok tidak mencukupi! Stok tersedia: ' . $keranjang['stok']);
    }
    
    // Update
    $query_update = "UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ?";
    $stmt_update = mysqli_prepare($conn, $query_update);
    mysqli_stmt_bind_param($stmt_update, "ii", $jumlah, $id_keranjang);
    
    if (mysqli_stmt_execute($stmt_update)) {
        json_response('success', 'Jumlah produk berhasil diupdate!');
    } else {
        json_response('error', 'Gagal update jumlah!');
    }
}

// ============================================
// DELETE - Hapus dari Keranjang
// ============================================
elseif ($method === 'DELETE') {
    $id_keranjang = isset($input['id_keranjang']) ? (int)$input['id_keranjang'] : 0;
    
    if ($id_keranjang == 0) {
        json_response('error', 'ID Keranjang harus diisi!');
    }
    
    // Hapus (dengan validasi user)
    $query = "DELETE FROM keranjang WHERE id_keranjang = ? AND id_user = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $id_keranjang, $id_user);
    
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_affected_rows($conn) > 0) {
            json_response('success', 'Produk berhasil dihapus dari keranjang!');
        } else {
            json_response('error', 'Keranjang tidak ditemukan!');
        }
    } else {
        json_response('error', 'Gagal menghapus dari keranjang!');
    }
}

else {
    json_response('error', 'Method tidak didukung!');
}

mysqli_close($conn);
?>
