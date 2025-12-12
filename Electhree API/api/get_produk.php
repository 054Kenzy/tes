<?php
// ============================================
// API GET PRODUK - ELECTHREE E-COMMERCE
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../config/koneksi.php';

// Get parameters
$id_kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Build query
$where = [];
$params = [];
$types = '';

if ($id_kategori > 0) {
    $where[] = "p.id_kategori = ?";
    $params[] = $id_kategori;
    $types .= 'i';
}

if (!empty($search)) {
    $where[] = "(p.nama_produk LIKE ? OR p.deskripsi LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Query produk
$query = "SELECT p.*, k.nama_kategori 
          FROM produk p 
          LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
          $where_clause
          ORDER BY p.id_produk DESC 
          LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$produk_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Format gambar URL (support Google Drive & lokal)
    $gambar_url = '';
    if (!empty($row['gambar'])) {
        if (strpos($row['gambar'], 'drive.google.com') !== false) {
            $gambar_url = $row['gambar'];
        } else {
            $gambar_url = 'http://' . $_SERVER['HTTP_HOST'] . '/Electhree%20API/uploads/produk/' . $row['gambar'];
        }
    }
    
    $produk_list[] = [
        'id_produk' => (int)$row['id_produk'],
        'id_kategori' => (int)$row['id_kategori'],
        'nama_produk' => $row['nama_produk'],
        'nama_kategori' => $row['nama_kategori'],
        'harga' => (float)$row['harga'],
        'stok' => (int)$row['stok'],
        'deskripsi' => $row['deskripsi'],
        'spesifikasi' => $row['spesifikasi'],
        'gambar' => $gambar_url
    ];
}

// Count total
$query_count = "SELECT COUNT(*) as total FROM produk p $where_clause";
if (!empty($where)) {
    $stmt_count = mysqli_prepare($conn, $query_count);
    $count_types = substr($types, 0, -2); // Remove limit & offset types
    $count_params = array_slice($params, 0, -2); // Remove limit & offset params
    if (!empty($count_params)) {
        mysqli_stmt_bind_param($stmt_count, $count_types, ...$count_params);
    }
    mysqli_stmt_execute($stmt_count);
    $result_count = mysqli_stmt_get_result($stmt_count);
} else {
    $result_count = mysqli_query($conn, $query_count);
}
$total = mysqli_fetch_assoc($result_count)['total'];

json_response('success', 'Data produk berhasil diambil', [
    'produk' => $produk_list,
    'total' => (int)$total,
    'limit' => $limit,
    'offset' => $offset
]);

mysqli_close($conn);
?>
