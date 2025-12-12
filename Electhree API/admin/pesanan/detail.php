<?php
// ============================================
// DETAIL PESANAN - ELECTHREE E-COMMERCE
// ============================================


require_once '../../config/koneksi.php';
require_once '../../config/session.php';


// Cek login admin
require_admin_login('../login.php');


$admin = get_admin_data();


// Get ID pesanan
$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;


// Get data pesanan
$query_pesanan = "SELECT p.id_pesanan, p.id_user, p.tanggal_pesan, p.total_harga, p.status_pesanan, 
                  p.alamat_pengiriman, p.metode_pembayaran, p.ongkir,
                  u.nama as nama_user, u.email, u.no_hp
                  FROM pesanan p
                  LEFT JOIN `user` u ON p.id_user = u.id_user
                  WHERE p.id_pesanan = ?";
$stmt = mysqli_prepare($conn, $query_pesanan);
mysqli_stmt_bind_param($stmt, "i", $id_pesanan);
mysqli_stmt_execute($stmt);
$result_pesanan = mysqli_stmt_get_result($stmt);
$pesanan = mysqli_fetch_assoc($result_pesanan);


if (!$pesanan) {
    header("Location: index.php");
    exit;
}


// Get detail items
$query_items = "SELECT dp.id_detail, dp.id_produk, dp.jumlah, dp.harga_satuan, 
                p.nama_produk, p.gambar
                FROM detail_pesanan dp
                JOIN produk p ON dp.id_produk = p.id_produk
                WHERE dp.id_pesanan = ?";
$stmt_items = mysqli_prepare($conn, $query_items);
mysqli_stmt_bind_param($stmt_items, "i", $id_pesanan);
mysqli_stmt_execute($stmt_items);
$result_items = mysqli_stmt_get_result($stmt_items);
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?= $id_pesanan ?> - Electhree Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }
        .header h1 { font-size: 24px; }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .info-item label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .info-item .value {
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-diproses { background: #cfe2ff; color: #084298; }
        .status-dikirim { background: #d1ecf1; color: #0c5460; }
        .status-selesai { background: #d4edda; color: #155724; }
        .status-dibatalkan { background: #f8d7da; color: #721c24; }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
        .total-row {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .update-status {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .update-status h3 {
            margin-bottom: 15px;
        }
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .btn-update {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-invoice {
            display: inline-block;
            padding: 12px 30px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: background 0.3s;
        }
        .btn-invoice:hover {
            background: #218838;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚡ Electhree Admin</h1>
    </div>
    
    <div class="container">
        <a href="index.php" class="back-btn">← Kembali</a>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">✅ Status pesanan berhasil diupdate!</div>
        <?php endif; ?>
        
        <div class="card">
            <h2>📋 Detail Pesanan #<?= $id_pesanan ?></h2>
            
            <div class="info-grid">
                <div class="info-item">
                    <label>Tanggal Pesanan</label>
                    <div class="value"><?= date('d F Y, H:i', strtotime($pesanan['tanggal_pesan'])) ?></div>
                </div>
                
                <div class="info-item">
                    <label>Status</label>
                    <div class="value">
                        <span class="status-badge status-<?= $pesanan['status_pesanan'] ?>">
                            <?= ucfirst($pesanan['status_pesanan']) ?>
                        </span>
                    </div>
                </div>
                
                <div class="info-item">
                    <label>Nama Customer</label>
                    <div class="value"><?= $pesanan['nama_user'] ?></div>
                </div>
                
                <div class="info-item">
                    <label>Email</label>
                    <div class="value"><?= $pesanan['email'] ?></div>
                </div>
                
                <div class="info-item">
                    <label>No HP</label>
                    <div class="value"><?= $pesanan['no_hp'] ?: '-' ?></div>
                </div>
                
                <div class="info-item">
                    <label>Metode Pembayaran</label>
                    <div class="value"><?= isset($pesanan['metode_pembayaran']) ? ucwords($pesanan['metode_pembayaran']) : '-' ?></div>
                </div>
            </div>
            
            <div class="info-item" style="grid-column: 1 / -1;">
                <label>Alamat Pengiriman</label>
                <div class="value"><?= nl2br($pesanan['alamat_pengiriman']) ?></div>
            </div>
        </div>
        
        <div class="card">
            <h2>🛍️ Item Pesanan</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($result_items)): ?>
                    <tr>
                        <td>
                            <?php if ($item['gambar']): ?>
                                <?php 
                                if (strpos($item['gambar'], 'drive.google.com') !== false) {
                                    $img_src = $item['gambar'];
                                } else {
                                    $img_src = '../../uploads/produk/' . $item['gambar'];
                                }
                                ?>
                                <img src="<?= $img_src ?>" class="product-img" onerror="this.style.display='none'">
                            <?php endif; ?>
                        </td>
                        <td><?= $item['nama_produk'] ?></td>
                        <td>Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                        <td><?= $item['jumlah'] ?></td>
                        <td>Rp <?= number_format($item['harga_satuan'] * $item['jumlah'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <tr class="total-row">
                        <td colspan="4" style="text-align: right;">TOTAL:</td>
                        <td>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <?php if ($pesanan['status_pesanan'] !== 'selesai' && $pesanan['status_pesanan'] !== 'dibatalkan'): ?>
        <div class="card">
            <div class="update-status">
                <h3>🔄 Update Status Pesanan</h3>
                <form action="update_status.php" method="POST">
                    <input type="hidden" name="id_pesanan" value="<?= $id_pesanan ?>">
                    
                    <select name="status" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="pending" <?= $pesanan['status_pesanan'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="diproses" <?= $pesanan['status_pesanan'] === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                        <option value="dikirim" <?= $pesanan['status_pesanan'] === 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                        <option value="selesai" <?= $pesanan['status_pesanan'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="dibatalkan" <?= $pesanan['status_pesanan'] === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                    
                    <button type="submit" class="btn-update">Update Status</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- TOMBOL CETAK INVOICE -->
        <div class="card">
            <a href="../laporan/invoice.php?id=<?= $id_pesanan ?>" target="_blank" class="btn-invoice">
                📄 Cetak Invoice
            </a>
        </div>
        
    </div>
</body>
</html>
