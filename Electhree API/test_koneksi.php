<?php
// Test koneksi database
require_once 'config/koneksi.php';

echo "<h2>Test Koneksi Database Electhree</h2>";

// Cek koneksi
if ($conn) {
    echo "<p style='color: green;'>✅ Koneksi database BERHASIL!</p>";
    
    // Test query: hitung jumlah produk
    $query = "SELECT COUNT(*) as total FROM produk";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    echo "<p>Total produk di database: <strong>" . $row['total'] . " produk</strong></p>";
    
    // Test query: tampilkan 5 produk pertama
    $query = "SELECT nama_produk, harga FROM produk LIMIT 5";
    $result = mysqli_query($conn, $query);
    
    echo "<h3>Sample 5 Produk:</h3>";
    echo "<ul>";
    while($produk = mysqli_fetch_assoc($result)) {
        echo "<li>" . $produk['nama_produk'] . " - Rp " . number_format($produk['harga'], 0, ',', '.') . "</li>";
    }
    echo "</ul>";
    
} else {
    echo "<p style='color: red;'>❌ Koneksi database GAGAL!</p>";
}

// Tutup koneksi
mysqli_close($conn);
?>
