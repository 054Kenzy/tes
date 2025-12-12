<?php
// ============================================
// API LOGIN USER - ELECTHREE E-COMMERCE
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/koneksi.php';
require_once '../config/session.php';

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
$email = isset($input['email']) ? clean_input($input['email']) : '';
$password = isset($input['password']) ? $input['password'] : '';

// Cek apakah semua field terisi
if (empty($email) || empty($password)) {
    json_response('error', 'Email dan password wajib diisi!');
}

// Query cek user
$query = "SELECT * FROM `user` WHERE email = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    // Cek password (gunakan MD5 karena di database pakai MD5)
    if (md5($password) === $user['password']) {
        // Login berhasil
        set_user_session($user);
        
        // Data user yang login
        $user_data = [
            'id_user' => $user['id_user'],
            'nama' => $user['nama'],
            'email' => $user['email'],
            'no_hp' => $user['no_hp']
        ];
        
        json_response('success', 'Login berhasil!', $user_data);
    } else {
        json_response('error', 'Password salah!');
    }
} else {
    json_response('error', 'Email tidak ditemukan!');
}

mysqli_close($conn);
?>
