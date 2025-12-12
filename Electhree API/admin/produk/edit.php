<?php
// ============================================
// EDIT PRODUK - ELECTHREE E-COMMERCE
// ============================================

require_once '../../config/koneksi.php';
require_once '../../config/session.php';

// Cek login admin
require_admin_login('../login.php');

$admin = get_admin_data();
$error = '';

// Ambil ID produk
$id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get data produk
$query = "SELECT * FROM produk WHERE id_produk = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_produk);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);

if (!$produk) {
    header("Location: index.php");
    exit;
}

// Get semua kategori untuk dropdown
$query_kategori = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($conn, $query_kategori);

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kategori = (int)$_POST['id_kategori'];
    $nama_produk = clean_input($_POST['nama_produk']);
    $harga = (float)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $deskripsi = clean_input($_POST['deskripsi']);
    $spesifikasi = clean_input($_POST['spesifikasi']);
    $gambar_url = isset($_POST['gambar_url']) ? clean_input($_POST['gambar_url']) : '';
    $gambar_lama = $produk['gambar'];
    $gambar_baru = $gambar_lama;
    
    // Validasi
    if (empty($nama_produk) || empty($harga) || $id_kategori == 0) {
        $error = 'Nama produk, kategori, dan harga wajib diisi!';
    } else {
        // Prioritas 1: Cek Google Drive URL dulu
        if (!empty($gambar_url)) {
            $gambar_baru = $gambar_url;
            
            // Hapus gambar lama jika bukan URL Google Drive
            if (!empty($gambar_lama) && strpos($gambar_lama, 'drive.google.com') === false) {
                $file_path = '../../uploads/produk/' . $gambar_lama;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        } 
        // Prioritas 2: Upload gambar baru (jika ada)
        elseif (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['gambar']['tmp_name'];
            $file_name = $_FILES['gambar']['name'];
            $file_size = $_FILES['gambar']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file_ext, $allowed_ext)) {
                $error = 'Format file harus JPG, JPEG, PNG, atau GIF!';
            } elseif ($file_size > $max_size) {
                $error = 'Ukuran file maksimal 5MB!';
            } else {
                $gambar_baru = time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = '../../uploads/produk/' . $gambar_baru;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Hapus gambar lama (jika bukan URL Google Drive)
                    if (!empty($gambar_lama) && strpos($gambar_lama, 'drive.google.com') === false) {
                        $file_path_old = '../../uploads/produk/' . $gambar_lama;
                        if (file_exists($file_path_old)) {
                            unlink($file_path_old);
                        }
                    }
                } else {
                    $error = 'Gagal upload gambar!';
                    $gambar_baru = $gambar_lama;
                }
            }
        }
        
        // Update database jika tidak ada error
        if (empty($error)) {
            $query = "UPDATE produk SET 
                      id_kategori = ?, nama_produk = ?, harga = ?, stok = ?, 
                      deskripsi = ?, spesifikasi = ?, gambar = ? 
                      WHERE id_produk = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "isdiissi", $id_kategori, $nama_produk, $harga, $stok, 
                                   $deskripsi, $spesifikasi, $gambar_baru, $id_produk);
            
            if (mysqli_stmt_execute($stmt)) {
                header("Location: index.php?success=edit");
                exit;
            } else {
                $error = 'Gagal mengupdate produk: ' . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Electhree Admin</title>
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
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 { color: #333; margin-bottom: 30px; }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group.full { grid-column: 1 / -1; }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        label span { color: #dc3545; }
        input[type="text"],
        input[type="number"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        .current-image {
            margin: 10px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .current-image img {
            max-width: 200px;
            height: auto;
            border-radius: 5px;
            margin-top: 10px;
        }
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .file-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .separator {
            text-align: center;
            margin: 20px 0;
            color: #999;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚡ Electhree Admin</h1>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>✏️ Edit Produk</h2>
            
            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nama_produk">Nama Produk <span>*</span></label>
                        <input type="text" id="nama_produk" name="nama_produk" required 
                               value="<?= htmlspecialchars($produk['nama_produk']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="id_kategori">Kategori <span>*</span></label>
                        <select id="id_kategori" name="id_kategori" required>
                            <?php while ($kategori = mysqli_fetch_assoc($result_kategori)): ?>
                                <option value="<?= $kategori['id_kategori'] ?>" 
                                        <?= $produk['id_kategori'] == $kategori['id_kategori'] ? 'selected' : '' ?>>
                                    <?= $kategori['nama_kategori'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="harga">Harga (Rp) <span>*</span></label>
                        <input type="number" id="harga" name="harga" required min="0" step="0.01"
                               value="<?= $produk['harga'] ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="stok">Stok</label>
                        <input type="number" id="stok" name="stok" min="0" 
                               value="<?= $produk['stok'] ?>">
                    </div>
                </div>
                
                <!-- GAMBAR SAAT INI -->
                <?php if (!empty($produk['gambar'])): ?>
                    <div class="current-image">
                        <strong>📷 Gambar Saat Ini:</strong>
                        <?php 
                        // Cek apakah Google Drive URL atau file lokal
                        if (strpos($produk['gambar'], 'drive.google.com') !== false): 
                        ?>
                            <img src="<?= $produk['gambar'] ?>" alt="Current" onerror="this.src='https://placehold.co/200x200?text=Error'">
                            <div class="file-info">🔗 Google Drive Link</div>
                        <?php else: ?>
                            <img src="../../uploads/produk/<?= $produk['gambar'] ?>" alt="Current" onerror="this.src='https://placehold.co/200x200?text=Error'">
                            <div class="file-info">📁 File Lokal: <?= $produk['gambar'] ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- GAMBAR: GOOGLE DRIVE URL -->
                <div class="form-group full">
                    <label for="gambar_url">🔗 Ganti dengan Link Google Drive</label>
                    <input type="text" id="gambar_url" name="gambar_url" 
                           placeholder="https://drive.google.com/uc?export=view&id=FILE_ID">
                    <div class="file-info">📌 Paste link baru jika ingin mengganti gambar dengan Google Drive</div>
                </div>
                
                <div class="separator">── ATAU ──</div>
                
                <!-- GAMBAR: UPLOAD FILE -->
                <div class="form-group full">
                    <label for="gambar">📁 Upload Gambar Baru dari Komputer</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                    <div class="file-info">Kosongkan jika tidak ingin mengganti gambar | Max: 5MB</div>
                </div>
                
                <div class="form-group full">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
                </div>
                
                <div class="form-group full">
                    <label for="spesifikasi">Spesifikasi</label>
                    <textarea id="spesifikasi" name="spesifikasi"><?= htmlspecialchars($produk['spesifikasi']) ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
