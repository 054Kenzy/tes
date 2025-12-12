<?php
// ============================================
// TAMBAH PRODUK - ELECTHREE E-COMMERCE
// ============================================

require_once '../../config/koneksi.php';
require_once '../../config/session.php';

// Cek login admin
require_admin_login('../login.php');

$admin = get_admin_data();
$error = '';

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
    
    // Validasi
    if (empty($nama_produk) || empty($harga) || $id_kategori == 0) {
        $error = 'Nama produk, kategori, dan harga wajib diisi!';
    } else {
        $gambar = '';
        
        // Prioritas 1: Cek Google Drive URL dulu
        if (!empty($gambar_url)) {
            $gambar = $gambar_url;
        } 
        // Prioritas 2: Kalau tidak ada URL, cek file upload
        elseif (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['gambar']['tmp_name'];
            $file_name = $_FILES['gambar']['name'];
            $file_size = $_FILES['gambar']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Validasi file
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file_ext, $allowed_ext)) {
                $error = 'Format file harus JPG, JPEG, PNG, atau GIF!';
            } elseif ($file_size > $max_size) {
                $error = 'Ukuran file maksimal 5MB!';
            } else {
                // Generate nama file unik
                $gambar = time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = '../../uploads/produk/' . $gambar;
                
                if (!move_uploaded_file($file_tmp, $upload_path)) {
                    $error = 'Gagal upload gambar!';
                    $gambar = '';
                }
            }
        }
        
        // Insert ke database jika tidak ada error
        if (empty($error)) {
            $query = "INSERT INTO produk (id_kategori, nama_produk, harga, stok, deskripsi, spesifikasi, gambar) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "isdiiss", $id_kategori, $nama_produk, $harga, $stok, $deskripsi, $spesifikasi, $gambar);
            
            if (mysqli_stmt_execute($stmt)) {
                header("Location: index.php?success=tambah");
                exit;
            } else {
                $error = 'Gagal menambahkan produk: ' . mysqli_error($conn);
                // Hapus gambar jika insert gagal (hanya untuk upload lokal)
                if (!empty($gambar) && strpos($gambar, 'drive.google.com') === false && file_exists($upload_path)) {
                    unlink($upload_path);
                }
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
    <title>Tambah Produk - Electhree Admin</title>
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
        h2 {
            color: #333;
            margin-bottom: 30px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group.full {
            grid-column: 1 / -1;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        label span {
            color: #dc3545;
        }
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
            <h2>🛍️ Tambah Produk Baru</h2>
            
            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nama_produk">Nama Produk <span>*</span></label>
                        <input type="text" id="nama_produk" name="nama_produk" required 
                               placeholder="Contoh: Kulkas 2 Pintu LG 450L"
                               value="<?= isset($_POST['nama_produk']) ? htmlspecialchars($_POST['nama_produk']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="id_kategori">Kategori <span>*</span></label>
                        <select id="id_kategori" name="id_kategori" required>
                            <option value="0">-- Pilih Kategori --</option>
                            <?php while ($kategori = mysqli_fetch_assoc($result_kategori)): ?>
                                <option value="<?= $kategori['id_kategori'] ?>" 
                                        <?= (isset($_POST['id_kategori']) && $_POST['id_kategori'] == $kategori['id_kategori']) ? 'selected' : '' ?>>
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
                               placeholder="Contoh: 7200000"
                               value="<?= isset($_POST['harga']) ? $_POST['harga'] : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="stok">Stok</label>
                        <input type="number" id="stok" name="stok" min="0" 
                               placeholder="Contoh: 12"
                               value="<?= isset($_POST['stok']) ? $_POST['stok'] : '0' ?>">
                    </div>
                </div>
                
                <!-- GAMBAR: GOOGLE DRIVE URL -->
                <div class="form-group full">
                    <label for="gambar_url">🔗 Link Gambar Google Drive</label>
                    <input type="text" id="gambar_url" name="gambar_url" 
                           placeholder="https://drive.google.com/uc?export=view&id=FILE_ID"
                           value="<?= isset($_POST['gambar_url']) ? htmlspecialchars($_POST['gambar_url']) : '' ?>">
                    <div class="file-info">📌 Paste link Google Drive format: https://drive.google.com/uc?export=view&id=...</div>
                </div>
                
                <div class="separator">── ATAU ──</div>
                
                <!-- GAMBAR: UPLOAD FILE -->
                <div class="form-group full">
                    <label for="gambar">📁 Upload Gambar dari Komputer</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                    <div class="file-info">Format: JPG, JPEG, PNG, GIF | Max: 5MB</div>
                </div>
                
                <div class="form-group full">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" 
                              placeholder="Deskripsi singkat produk..."><?= isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : '' ?></textarea>
                </div>
                
                <div class="form-group full">
                    <label for="spesifikasi">Spesifikasi</label>
                    <textarea id="spesifikasi" name="spesifikasi" 
                              placeholder="Spesifikasi teknis produk..."><?= isset($_POST['spesifikasi']) ? htmlspecialchars($_POST['spesifikasi']) : '' ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
