<?php
// ============================================
// API REGISTER USER - ELECTHREE E-COMMERCE
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/koneksi.php';

// Hanya terima POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response('error', 'Method not allowed. Use POST request.');
}

// Ambil data dari request
$input = json_decode(file_get_contents('php://input'), true);

// Jika data dari form biasa (bukan JSON)
if (!$input) {
    $input = $_POST;
}

// Validasi input
$nama = isset($input['nama']) ? clean_input($input['nama']) : '';
$email = isset($input['email']) ? clean_input($input['email']) : '';
$password = isset($input['password']) ? $input['password'] : '';
$no_hp = isset($input['no_hp']) ? clean_input($input['no_hp']) : '';

// Cek apakah semua field terisi
if (empty($nama) || empty($email) || empty($password)) {
    json_response('error', 'Nama, email, dan password wajib diisi!');
}

// Validasi format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response('error', 'Format email tidak valid!');
}

// Cek apakah email sudah terdaftar
$query = "SELECT id_user FROM `user` WHERE email = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    json_response('error', 'Email sudah terdaftar!');
}

// Hash password dengan MD5 (sesuai database)
$password_hash = md5($password);

// Insert user baru
$query = "INSERT INTO `user` (nama, email, password, no_hp) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $password_hash, $no_hp);

if (mysqli_stmt_execute($stmt)) {
    $user_id = mysqli_insert_id($conn);
    
    // Data user yang baru dibuat
    $user_data = [
        'id_user' => $user_id,
        'nama' => $nama,
        'email' => $email,
        'no_hp' => $no_hp
    ];
    
    json_response('success', 'Registrasi berhasil!', $user_data);
} else {
    json_response('error', 'Registrasi gagal! ' . mysqli_error($conn));
}

mysqli_close($conn);
?>
