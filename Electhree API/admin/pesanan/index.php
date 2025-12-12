<?php
// ============================================
// LIST PESANAN - ELECTHREE E-COMMERCE
// ============================================


require_once '../../config/koneksi.php';
require_once '../../config/session.php';


// Cek login admin
require_admin_login('../login.php');


$admin = get_admin_data();


// Filter status
$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : '';


// Build query
$where = "";
if (!empty($filter_status)) {
    $where = "WHERE p.status_pesanan = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
}


// Get semua pesanan
$query = "SELECT p.id_pesanan, p.id_user, p.tanggal_pesan, p.total_harga, p.status_pesanan, 
          u.nama as nama_user, u.email, 
          COUNT(dp.id_detail) as total_item
          FROM pesanan p
          LEFT JOIN `user` u ON p.id_user = u.id_user
          LEFT JOIN detail_pesanan dp ON p.id_pesanan = dp.id_pesanan
          $where
          GROUP BY p.id_pesanan
          ORDER BY p.id_pesanan DESC";
$result = mysqli_query($conn, $query);


// Count by status
$query_stats = "SELECT status_pesanan, COUNT(*) as total FROM pesanan GROUP BY status_pesanan";
$result_stats = mysqli_query($conn, $query_stats);
$stats = [];
while ($row = mysqli_fetch_assoc($result_stats)) {
    $stats[$row['status_pesanan']] = $row['total'];
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Electhree Admin</title>
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
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .stat-card.active {
            border: 2px solid #667eea;
        }
        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-diproses {
            background: #cfe2ff;
            color: #084298;
        }
        .status-dikirim {
            background: #d1ecf1;
            color: #0c5460;
        }
        .status-selesai {
            background: #d4edda;
            color: #155724;
        }
        .status-dibatalkan {
            background: #f8d7da;
            color: #721c24;
        }
        .btn-detail {
            padding: 6px 16px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
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
        <div class="user-info">
            <a href="../dashboard.php" class="btn-dashboard">← Dashboard</a>
            <span><?= $admin['nama'] ?></span>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h2>📋 Kelola Pesanan</h2>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                ✅ Status pesanan berhasil diupdate!
            </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="stats">
            <a href="index.php" class="stat-card <?= empty($filter_status) ? 'active' : '' ?>">
                <h3>Semua Pesanan</h3>
                <div class="number"><?= mysqli_num_rows($result) ?></div>
            </a>
            
            <a href="index.php?status=pending" class="stat-card <?= $filter_status === 'pending' ? 'active' : '' ?>">
                <h3>Pending</h3>
                <div class="number"><?= isset($stats['pending']) ? $stats['pending'] : 0 ?></div>
            </a>
            
            <a href="index.php?status=diproses" class="stat-card <?= $filter_status === 'diproses' ? 'active' : '' ?>">
                <h3>Diproses</h3>
                <div class="number"><?= isset($stats['diproses']) ? $stats['diproses'] : 0 ?></div>
            </a>
            
            <a href="index.php?status=dikirim" class="stat-card <?= $filter_status === 'dikirim' ? 'active' : '' ?>">
                <h3>Dikirim</h3>
                <div class="number"><?= isset($stats['dikirim']) ? $stats['dikirim'] : 0 ?></div>
            </a>
            
            <a href="index.php?status=selesai" class="stat-card <?= $filter_status === 'selesai' ? 'active' : '' ?>">
                <h3>Selesai</h3>
                <div class="number"><?= isset($stats['selesai']) ? $stats['selesai'] : 0 ?></div>
            </a>
        </div>
        
        <div class="card">
            <?php if (mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Total Item</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    mysqli_data_seek($result, 0); // Reset pointer
                    while ($pesanan = mysqli_fetch_assoc($result)): 
                    ?>
                    <tr>
                        <td>#<?= $pesanan['id_pesanan'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($pesanan['tanggal_pesan'])) ?></td>
                        <td>
                            <strong><?= $pesanan['nama_user'] ?></strong><br>
                            <small><?= $pesanan['email'] ?></small>
                        </td>
                        <td><?= $pesanan['total_item'] ?> item</td>
                        <td><strong>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></strong></td>
                        <td>
                            <span class="status-badge status-<?= $pesanan['status_pesanan'] ?>">
                                <?= ucfirst($pesanan['status_pesanan']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="detail.php?id=<?= $pesanan['id_pesanan'] ?>" class="btn-detail">Detail</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="text-align: center; padding: 40px; color: #999;">
                    Belum ada pesanan<?= !empty($filter_status) ? ' dengan status "' . $filter_status . '"' : '' ?>.
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
