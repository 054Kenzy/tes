<?php
// ============================================
// LAPORAN PENJUALAN - ELECTHREE E-COMMERCE
// ============================================


require_once '../../config/koneksi.php';
require_once '../../config/session.php';


// Cek login admin
require_admin_login('../login.php');


$admin = get_admin_data();


// Filter tanggal
$tanggal_mulai = isset($_GET['mulai']) ? $_GET['mulai'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d');


// Total penjualan
$query_total = "SELECT COUNT(*) as total_pesanan, SUM(total_harga) as total_penjualan
                FROM pesanan 
                WHERE DATE(tanggal_pesan) BETWEEN ? AND ?
                AND status_pesanan != 'dibatalkan'";
$stmt_total = mysqli_prepare($conn, $query_total);
mysqli_stmt_bind_param($stmt_total, "ss", $tanggal_mulai, $tanggal_akhir);
mysqli_stmt_execute($stmt_total);
$result_total = mysqli_stmt_get_result($stmt_total);
$total = mysqli_fetch_assoc($result_total);


// Produk terlaris
$query_terlaris = "SELECT p.nama_produk, SUM(dp.jumlah) as total_terjual, 
                   SUM(dp.jumlah * dp.harga_satuan) as total_pendapatan
                   FROM detail_pesanan dp
                   JOIN produk p ON dp.id_produk = p.id_produk
                   JOIN pesanan ps ON dp.id_pesanan = ps.id_pesanan
                   WHERE DATE(ps.tanggal_pesan) BETWEEN ? AND ?
                   AND ps.status_pesanan != 'dibatalkan'
                   GROUP BY dp.id_produk
                   ORDER BY total_terjual DESC
                   LIMIT 10";
$stmt_terlaris = mysqli_prepare($conn, $query_terlaris);
mysqli_stmt_bind_param($stmt_terlaris, "ss", $tanggal_mulai, $tanggal_akhir);
mysqli_stmt_execute($stmt_terlaris);
$result_terlaris = mysqli_stmt_get_result($stmt_terlaris);


// Penjualan per hari
$query_harian = "SELECT DATE(tanggal_pesan) as tanggal, 
                 COUNT(*) as total_pesanan, 
                 SUM(total_harga) as total_penjualan
                 FROM pesanan
                 WHERE DATE(tanggal_pesan) BETWEEN ? AND ?
                 AND status_pesanan != 'dibatalkan'
                 GROUP BY DATE(tanggal_pesan)
                 ORDER BY tanggal DESC";
$stmt_harian = mysqli_prepare($conn, $query_harian);
mysqli_stmt_bind_param($stmt_harian, "ss", $tanggal_mulai, $tanggal_akhir);
mysqli_stmt_execute($stmt_harian);
$result_harian = mysqli_stmt_get_result($stmt_harian);
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Electhree Admin</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 24px; }
        .header .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .btn-logout, .btn-dashboard {
            padding: 8px 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .page-header h2 { color: #333; }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: end;
        }
        .form-group {
            flex: 1;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
        }
        .btn-filter {
            padding: 10px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .card h3 {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
            color: #333;
        }
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
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚡ Electhree Admin</h1>
        <div class="user-info">
            <a href="../dashboard.php" class="btn-dashboard">← Dashboard</a>
            <span><?= $admin['nama'] ?></span>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h2>📊 Laporan Penjualan</h2>
        </div>
        
        <!-- Filter -->
        <div class="filter-section">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="mulai" value="<?= $tanggal_mulai ?>" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Akhir</label>
                    <input type="date" name="akhir" value="<?= $tanggal_akhir ?>" required>
                </div>
                <button type="submit" class="btn-filter">Filter</button>
            </form>
        </div>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Pesanan</h3>
                <div class="number"><?= $total['total_pesanan'] ?: 0 ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Penjualan</h3>
                <div class="number">Rp <?= number_format($total['total_penjualan'] ?: 0, 0, ',', '.') ?></div>
            </div>
            <div class="stat-card">
                <h3>Rata-rata per Pesanan</h3>
                <div class="number">
                    <?php 
                    $avg = $total['total_pesanan'] > 0 ? $total['total_penjualan'] / $total['total_pesanan'] : 0;
                    echo 'Rp ' . number_format($avg, 0, ',', '.');
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Produk Terlaris -->
        <div class="card">
            <h3>🏆 Top 10 Produk Terlaris</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th class="text-right">Terjual</th>
                        <th class="text-right">Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($produk = mysqli_fetch_assoc($result_terlaris)): 
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $produk['nama_produk'] ?></td>
                        <td class="text-right"><strong><?= $produk['total_terjual'] ?> unit</strong></td>
                        <td class="text-right">Rp <?= number_format($produk['total_pendapatan'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Penjualan Harian -->
        <div class="card">
            <h3>📅 Penjualan Per Hari</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th class="text-right">Total Pesanan</th>
                        <th class="text-right">Total Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($hari = mysqli_fetch_assoc($result_harian)): ?>
                    <tr>
                        <td><?= date('d F Y', strtotime($hari['tanggal'])) ?></td>
                        <td class="text-right"><?= $hari['total_pesanan'] ?> pesanan</td>
                        <td class="text-right"><strong>Rp <?= number_format($hari['total_penjualan'], 0, ',', '.') ?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
