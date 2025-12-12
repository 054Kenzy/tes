<?php
// ============================================
// API INVOICE USER - ELECTHREE E-COMMERCE
// ============================================


require_once '../config/koneksi.php';
require_once '../config/session.php';


// Cek login user
if (!is_logged_in()) {
    die("<h1>Akses Ditolak</h1><p>Anda harus login terlebih dahulu untuk melihat invoice.</p>");
}


$id_user = get_user_id();
$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;


// Get data pesanan (pastikan milik user yang login)
$query_pesanan = "SELECT p.id_pesanan, p.tanggal_pesan, p.total_harga, p.status_pesanan,
                  p.alamat_pengiriman, p.metode_pembayaran, p.ongkir,
                  u.nama as nama_user, u.email, u.no_hp
                  FROM pesanan p
                  LEFT JOIN `user` u ON p.id_user = u.id_user
                  WHERE p.id_pesanan = ? AND p.id_user = ?";
$stmt = mysqli_prepare($conn, $query_pesanan);
mysqli_stmt_bind_param($stmt, "ii", $id_pesanan, $id_user);
mysqli_stmt_execute($stmt);
$result_pesanan = mysqli_stmt_get_result($stmt);
$pesanan = mysqli_fetch_assoc($result_pesanan);


if (!$pesanan) {
    die("<h1>Invoice Tidak Ditemukan</h1><p>Pesanan tidak ditemukan atau bukan milik Anda.</p>");
}


// Get detail items
$query_items = "SELECT dp.id_detail, dp.jumlah, dp.harga_satuan, p.nama_produk
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
    <title>Invoice #<?= $id_pesanan ?> - Electhree</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background: white;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 40px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }
        .company {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }
        .invoice-title {
            font-size: 24px;
            color: #333;
        }
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        .info-box h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .info-box p {
            margin: 5px 0;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #333;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .text-right { text-align: right; }
        .total-section {
            margin-top: 20px;
            text-align: right;
        }
        .total-row {
            display: flex;
            justify-content: flex-end;
            gap: 40px;
            margin: 10px 0;
        }
        .total-row.grand {
            font-size: 20px;
            font-weight: bold;
            padding-top: 10px;
            border-top: 2px solid #333;
        }
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-diproses { background: #cfe2ff; color: #084298; }
        .status-dikirim { background: #d1ecf1; color: #0c5460; }
        .status-selesai { background: #d4edda; color: #155724; }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
        .btn-print {
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="btn-print no-print">🖨️ Cetak Invoice</button>
    
    <div class="invoice-container">
        <div class="header">
            <div>
                <div class="company">⚡ ELECTHREE</div>
                <p>E-Commerce Elektronik Terpercaya</p>
                <p>Email: info@electhree.com</p>
                <p>Telp: (021) 1234-5678</p>
            </div>
            <div style="text-align: right;">
                <div class="invoice-title">INVOICE</div>
                <p><strong>#<?= str_pad($id_pesanan, 6, '0', STR_PAD_LEFT) ?></strong></p>
                <p><?= date('d F Y', strtotime($pesanan['tanggal_pesan'])) ?></p>
                <span class="status-badge status-<?= $pesanan['status_pesanan'] ?>">
                    <?= strtoupper($pesanan['status_pesanan']) ?>
                </span>
            </div>
        </div>
        
        <div class="info-section">
            <div class="info-box">
                <h3>Pembeli</h3>
                <p><strong><?= $pesanan['nama_user'] ?></strong></p>
                <p><?= $pesanan['email'] ?></p>
                <p><?= $pesanan['no_hp'] ?></p>
            </div>
            
            <div class="info-box">
                <h3>Alamat Pengiriman</h3>
                <p><?= nl2br($pesanan['alamat_pengiriman']) ?></p>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($item = mysqli_fetch_assoc($result_items)): 
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $item['nama_produk'] ?></td>
                    <td>Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                    <td><?= $item['jumlah'] ?></td>
                    <td class="text-right">Rp <?= number_format($item['harga_satuan'] * $item['jumlah'], 0, ',', '.') ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></span>
            </div>
            <div class="total-row">
                <span>Ongkir:</span>
                <span>Rp <?= number_format($pesanan['ongkir'], 0, ',', '.') ?></span>
            </div>
            <div class="total-row grand">
                <span>TOTAL:</span>
                <span>Rp <?= number_format($pesanan['total_harga'] + $pesanan['ongkir'], 0, ',', '.') ?></span>
            </div>
        </div>
        
        <div class="info-box" style="margin-top: 40px;">
            <h3>Metode Pembayaran</h3>
            <p><?= ucwords($pesanan['metode_pembayaran']) ?></p>
        </div>
        
        <div class="footer">
            <p>Terima kasih atas kepercayaan Anda berbelanja di Electhree!</p>
            <p>Invoice ini dicetak secara otomatis dan sah tanpa tanda tangan.</p>
        </div>
    </div>
</body>
</html>
