<?php
// ============================================
// LOGIN ADMIN - ELECTHREE E-COMMERCE
// ============================================

require_once '../config/koneksi.php';
require_once '../config/session.php';

// Redirect jika sudah login
if (is_admin_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    
    // Validasi input
    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi!";
    } else {
        // Query cek admin
        $query = "SELECT * FROM `admin` WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($admin = mysqli_fetch_assoc($result)) {
            // Cek password (gunakan MD5 karena di database pakai MD5)
            if (md5($password) === $admin['password']) {
                // Login berhasil
                set_admin_session($admin);
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Email tidak ditemukan!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Electhree</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        .credentials {
            margin-top: 30px;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 5px;
            font-size: 12px;
        }
        .credentials h4 {
            margin-bottom: 10px;
            color: #666;
        }
        .credentials p {
            margin: 5px 0;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>🔐 Login Admin</h2>
        <p class="subtitle">Electhree E-Commerce</p>
        
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       placeholder="admin@electhree.com">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required 
                       placeholder="••••••••">
            </div>
            
            <button type="submit" class="btn-login">Login</button>
        </form>
        
        <div class="credentials">
            <h4>📌 Akun Default (Testing):</h4>
            <p><strong>Super Admin:</strong><br>
               Email: admin@electhree.com<br>
               Password: admin123</p>
            <p><strong>Staff:</strong><br>
               Email: staff@electhree.com<br>
               Password: staff123</p>
        </div>
    </div>
</body>
</html>
