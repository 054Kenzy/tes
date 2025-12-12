<?php
// ============================================
// LIST PRODUK - ELECTHREE E-COMMERCE
// ============================================

require_once '../../config/koneksi.php';
require_once '../../config/session.php';

// Cek login admin
require_admin_login('../login.php');

$admin = get_admin_data();

// ============================================
// MAPPING GAMBAR GOOGLE DRIVE (49 PRODUK AWAL)
// Produk baru akan pakai database (upload/link)
// ============================================
$gambar_gdrive = [
    // Kategori 1: Peralatan Rumah Tangga (8 produk)
    1 => 'https://drive.google.com/uc?export=view&id=FILE_ID_1',
    2 => 'https://drive.google.com/uc?export=view&id=FILE_ID_2',
    3 => 'https://drive.google.com/uc?export=view&id=FILE_ID_3',
    4 => 'https://drive.google.com/uc?export=view&id=FILE_ID_4',
    5 => 'https://drive.google.com/uc?export=view&id=FILE_ID_5',
    6 => 'https://drive.google.com/uc?export=view&id=FILE_ID_6',
    7 => 'https://drive.google.com/uc?export=view&id=FILE_ID_7',
    8 => 'https://drive.google.com/uc?export=view&id=FILE_ID_8',
    
    // Kategori 2: Peralatan Dapur (8 produk)
    9 => 'https://drive.google.com/uc?export=view&id=FILE_ID_9',
    10 => 'https://drive.google.com/uc?export=view&id=FILE_ID_10',
    11 => 'https://drive.google.com/uc?export=view&id=FILE_ID_11',
    12 => 'https://drive.google.com/uc?export=view&id=FILE_ID_12',
    13 => 'https://drive.google.com/uc?export=view&id=FILE_ID_13',
    14 => 'https://drive.google.com/uc?export=view&id=FILE_ID_14',
    15 => 'https://drive.google.com/uc?export=view&id=FILE_ID_15',
    16 => 'https://drive.google.com/uc?export=view&id=FILE_ID_16',
    
    // Kategori 3: Peralatan Kesehatan & Kecantikan (6 produk)
    17 => 'https://drive.google.com/uc?export=view&id=FILE_ID_17',
    18 => 'https://drive.google.com/uc?export=view&id=FILE_ID_18',
    19 => 'https://drive.google.com/uc?export=view&id=FILE_ID_19',
    20 => 'https://drive.google.com/uc?export=view&id=FILE_ID_20',
    21 => 'https://drive.google.com/uc?export=view&id=FILE_ID_21',
    22 => 'https://drive.google.com/uc?export=view&id=FILE_ID_22',
    
    // Kategori 4: Peralatan Olahraga (6 produk)
    23 => 'https://drive.google.com/uc?export=view&id=FILE_ID_23',
    24 => 'https://drive.google.com/uc?export=view&id=FILE_ID_24',
    25 => 'https://drive.google.com/uc?export=view&id=FILE_ID_25',
    26 => 'https://drive.google.com/uc?export=view&id=FILE_ID_26',
    27 => 'https://drive.google.com/uc?export=view&id=FILE_ID_27',
    28 => 'https://drive.google.com/uc?export=view&id=FILE_ID_28',
    
    // Kategori 5: Elektronik Hiburan (5 produk)
    29 => 'https://drive.google.com/uc?export=view&id=FILE_ID_29',
    30 => 'https://drive.google.com/uc?export=view&id=FILE_ID_30',
    31 => 'https://drive.google.com/uc?export=view&id=FILE_ID_31',
    32 => 'https://drive.google.com/uc?export=view&id=FILE_ID_32',
    33 => 'https://drive.google.com/uc?export=view&id=FILE_ID_33',
    
    // Kategori 6: Gadget & Aksesoris (7 produk)
    34 => 'https://drive.google.com/uc?export=view&id=FILE_ID_34',
    35 => 'https://drive.google.com/uc?export=view&id=FILE_ID_35',
    36 => 'https://drive.google.com/uc?export=view&id=FILE_ID_36',
    37 => 'https://drive.google.com/uc?export=view&id=FILE_ID_37',
    38 => 'https://drive.google.com/uc?export=view&id=FILE_ID_38',
    39 => 'https://drive.google.com/uc?export=view&id=FILE_ID_39',
    40 => 'https://drive.google.com/uc?export=view&id=FILE_ID_40',
    
    // Kategori 7: Smart Home / IoT (5 produk)
    41 => 'https://drive.google.com/uc?export=view&id=FILE_ID_41',
    42 => 'https://drive.google.com/uc?export=view&id=FILE_ID_42',
    43 => 'https://drive.google.com/uc?export=view&id=FILE_ID_43',
    44 => 'https://drive.google.com/uc?export=view&id=FILE_ID_44',
    45 => 'https://drive.google.com/uc?export=view&id=FILE_ID_45',
    
    // Kategori 8: Perkakas Elektrik (4 produk)
    46 => 'https://drive.google.com/uc?export=view&id=FILE_ID_46',
    47 => 'https://drive.google.com/uc?export=view&id=FILE_ID_47',
    48 => 'https://drive.google.com/uc?export=view&id=FILE_ID_48',
    49 => 'https://drive.google.com/uc?export=view&id=FILE_ID_49'
];

// Get semua produk dengan join kategori
$query = "SELECT p.*, k.nama_kategori 
          FROM produk p 
          LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
          ORDER BY p.id_produk ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Electhree Admin</title>
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
        .btn-add {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
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
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .product-name {
            font-weight: 600;
            color: #333;
        }
        .category-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 12px;
            font-size: 12px;
        }
        .price {
            color: #28a745;
            font-weight: 600;
        }
        .stock {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .stock.low {
            background: #fff3cd;
            color: #856404;
        }
        .stock.ok {
            background: #d4edda;
            color: #155724;
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
        .no-img {
            width:60px;
            height:60px;
            background:#ddd;
            border-radius:5px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:10px;
            color:#999;
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
            <h2>🛍️ Kelola Produk</h2>
            <a href="tambah.php" class="btn-add">+ Tambah Produk</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                <?php
                if ($_GET['success'] == 'tambah') echo '✅ Produk berhasil ditambahkan!';
                elseif ($_GET['success'] == 'edit') echo '✅ Produk berhasil diupdate!';
                elseif ($_GET['success'] == 'hapus') echo '✅ Produk berhasil dihapus!';
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php
                if ($_GET['error'] == 'delete') echo '❌ Gagal menghapus produk!';
                ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">Gambar</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th style="width: 200px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($produk = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <?php 
                            $id_produk = $produk['id_produk'];
                            
                            // Prioritas 1: Cek array Google Drive (hardcode untuk 49 produk awal)
                            if (isset($gambar_gdrive[$id_produk]) && !empty($gambar_gdrive[$id_produk])) {
                                $img_src = $gambar_gdrive[$id_produk];
                            }
                            // Prioritas 2: Cek gambar di database
                            elseif (!empty($produk['gambar'])) {
                                // Cek apakah URL Google Drive atau file lokal
                                if (strpos($produk['gambar'], 'drive.google.com') !== false) {
                                    $img_src = $produk['gambar']; // Google Drive URL
                                } else {
                                    $img_src = '../../uploads/produk/' . $produk['gambar']; // File lokal
                                }
                            }
                            // Prioritas 3: Tidak ada gambar
                            else {
                                $img_src = '';
                            }
                            ?>
                            
                            <?php if ($img_src): ?>
                                <img src="<?= $img_src ?>" 
                                     alt="<?= $produk['nama_produk'] ?>" 
                                     class="product-img"
                                     onerror="this.src='https://placehold.co/60x60/ddd/999?text=No+Img'">
                            <?php else: ?>
                                <div class="no-img">No Img</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="product-name"><?= $produk['nama_produk'] ?></div>
                        </td>
                        <td>
                            <span class="category-badge"><?= $produk['nama_kategori'] ?></span>
                        </td>
                        <td class="price">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></td>
                        <td>
                            <span class="stock <?= $produk['stok'] < 10 ? 'low' : 'ok' ?>">
                                <?= $produk['stok'] ?> unit
                            </span>
                        </td>
                        <td class="actions">
                            <a href="edit.php?id=<?= $produk['id_produk'] ?>" class="btn-edit">Edit</a>
                            <a href="hapus.php?id=<?= $produk['id_produk'] ?>" 
                               class="btn-delete"
                               onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
