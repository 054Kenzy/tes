<?php
// ============================================
// LIST KATEGORI - ELECTHREE E-COMMERCE
// ============================================

require_once '../../config/koneksi.php';
require_once '../../config/session.php';

// Cek login admin
require_admin_login('../login.php');

$admin = get_admin_data();

// Get semua kategori
$query = "SELECT * FROM kategori ORDER BY id_kategori ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Electhree Admin</title>
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
        .btn-logout, .btn-dashboard {
            padding: 8px 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: background 0.3s;
        }
        .btn-logout:hover, .btn-dashboard:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .page-header h2 {
            color: #333;
        }
        .btn-add {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn-add:hover {
            transform: translateY(-2px);
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
        .actions {
            display: flex;
            gap: 10px;
        }
        .btn-edit, .btn-delete {
            padding: 6px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-edit {
            background: #ffc107;
            color: #000;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-edit:hover {
            background: #e0a800;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-state h3 {
            margin-bottom: 10px;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
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
            <h2>📦 Kelola Kategori</h2>
            <a href="tambah.php" class="btn-add">+ Tambah Kategori</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                <?php
                if ($_GET['success'] == 'tambah') echo '✅ Kategori berhasil ditambahkan!';
                elseif ($_GET['success'] == 'edit') echo '✅ Kategori berhasil diupdate!';
                elseif ($_GET['success'] == 'hapus') echo '✅ Kategori berhasil dihapus!';
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php
                if ($_GET['error'] == 'used') echo '❌ Kategori tidak bisa dihapus karena masih digunakan oleh produk!';
                elseif ($_GET['error'] == 'delete') echo '❌ Gagal menghapus kategori!';
                ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($kategori = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $kategori['id_kategori'] ?></td>
                            <td><strong><?= $kategori['nama_kategori'] ?></strong></td>
                            <td><?= $kategori['deskripsi'] ?></td>
                            <td class="actions">
                                <a href="edit.php?id=<?= $kategori['id_kategori'] ?>" class="btn-edit">Edit</a>
                                <a href="hapus.php?id=<?= $kategori['id_kategori'] ?>" 
                                   class="btn-delete"
                                   onclick="return confirm('Yakin ingin menghapus kategori ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h3>Belum ada kategori</h3>
                    <p>Klik tombol "Tambah Kategori" untuk menambahkan kategori pertama.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
