<?php
// ============================================
// DASHBOARD ADMIN - ELECTHREE E-COMMERCE
// ============================================


require_once '../config/koneksi.php';
require_once '../config/session.php';


// Cek login
require_admin_login();


$admin = get_admin_data();
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Electhree</title>
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
        .header h1 {
            font-size: 24px;
        }
        .header .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .btn-logout {
            padding: 8px 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: background 0.3s;
        }
        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .welcome-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .welcome-box h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .welcome-box p {
            color: #666;
        }
        .stats {
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
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .menu-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .menu-card .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .menu-card h3 {
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚡ Electhree Admin</h1>
        <div class="user-info">
            <span><?= $admin['nama'] ?> (<?= ucfirst($admin['role']) ?>)</span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-box">
            <h2>Selamat Datang, <?= $admin['nama'] ?>! 👋</h2>
            <p>Dashboard admin Electhree E-Commerce</p>
        </div>
        
        <?php
        // Get statistics
        $query_produk = "SELECT COUNT(*) as total FROM produk";
        $result_produk = mysqli_query($conn, $query_produk);
        $total_produk = mysqli_fetch_assoc($result_produk)['total'];
        
        $query_kategori = "SELECT COUNT(*) as total FROM kategori";
        $result_kategori = mysqli_query($conn, $query_kategori);
        $total_kategori = mysqli_fetch_assoc($result_kategori)['total'];
        
        $query_user = "SELECT COUNT(*) as total FROM `user`";
        $result_user = mysqli_query($conn, $query_user);
        $total_user = mysqli_fetch_assoc($result_user)['total'];
        
        $query_pesanan = "SELECT COUNT(*) as total FROM pesanan";
        $result_pesanan = mysqli_query($conn, $query_pesanan);
        $total_pesanan = mysqli_fetch_assoc($result_pesanan)['total'];
        ?>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Produk</h3>
                <div class="number"><?= $total_produk ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Kategori</h3>
                <div class="number"><?= $total_kategori ?></div>
            </div>
            <div class="stat-card">
                <h3>Total User</h3>
                <div class="number"><?= $total_user ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Pesanan</h3>
                <div class="number"><?= $total_pesanan ?></div>
            </div>
        </div>
        
        <h2 style="margin-bottom: 20px; color: #333;">Menu Admin</h2>
        <div class="menu-grid">
            <a href="kategori/" class="menu-card">
                <div class="icon">📦</div>
                <h3>Kategori</h3>
            </a>
            <a href="produk/" class="menu-card">
                <div class="icon">🛍️</div>
                <h3>Produk</h3>
            </a>
            <a href="pesanan/" class="menu-card">
                <div class="icon">📋</div>
                <h3>Pesanan</h3>
            </a>
            <a href="laporan/" class="menu-card">
                <div class="icon">📊</div>
                <h3>Laporan</h3>
            </a>
        </div>
    </div>
</body>
</html>
