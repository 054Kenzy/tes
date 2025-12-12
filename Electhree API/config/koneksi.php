<?php
// ============================================
// FILE KONEKSI DATABASE - ELECTHREE E-COMMERCE
// ============================================

// Konfigurasi Database
$db_host = "localhost";      // Host database (default: localhost)
$db_user = "root";           // Username MySQL (default XAMPP: root)
$db_pass = "";               // Password MySQL (default XAMPP: kosong)
$db_name = "electhree";      // Nama database

// Buat koneksi ke database
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if (!$conn) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Koneksi database gagal: ' . mysqli_connect_error()
    ]));
}

// Set charset UTF-8 (untuk karakter Indonesia)
mysqli_set_charset($conn, "utf8");

// Timezone Indonesia (WIB)
date_default_timezone_set('Asia/Jakarta');

// Function untuk membersihkan input (mencegah SQL Injection)
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Function untuk response JSON
function json_response($status, $message, $data = null) {
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

?>
