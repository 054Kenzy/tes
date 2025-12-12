<?php
// ============================================
// EDIT KATEGORI - ELECTHREE E-COMMERCE
// ============================================

require_once '../../config/koneksi.php';
require_once '../../config/session.php';

// Cek login admin
require_admin_login('../login.php');

$admin = get_admin_data();
$error = '';

// Ambil ID kategori dari URL
$id_kategori = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data kategori
$query = "SELECT * FROM kategori WHERE id_kategori = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_kategori);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$kategori = mysqli_fetch_assoc($result);

// Jika kategori tidak ditemukan
if (!$kategori) {
    header("Location: index.php");
    exit;
}

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = clean_input($_POST['nama_kategori']);
    $deskripsi = clean_input($_POST['deskripsi']);
    
    // Validasi
    if (empty($nama_kategori)) {
        $error = 'Nama kategori wajib diisi!';
    } else {
        // Update database
        $query = "UPDATE kategori SET nama_kategori = ?, deskripsi = ? WHERE id_kategori = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $nama_kategori, $deskripsi, $id_kategori);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php?success=edit");
            exit;
        } else {
            $error = 'Gagal mengupdate kategori: ' . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori - Electhree Admin</title>
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
        .container {
            max-width: 800px;
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        input:focus, textarea:focus {
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
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
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
    </style>
</head>
<body>
    <div class="header">
        <h1>⚡ Electhree Admin</h1>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>✏️ Edit Kategori</h2>
            
            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama_kategori">Nama Kategori *</label>
                    <input type="text" id="nama_kategori" name="nama_kategori" 
                           required placeholder="Contoh: Elektronik" 
                           value="<?= htmlspecialchars($kategori['nama_kategori']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" 
                              placeholder="Deskripsi singkat tentang kategori ini..."><?= htmlspecialchars($kategori['deskripsi']) ?></textarea>
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
