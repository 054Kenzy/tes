<?php
// ============================================
// SESSION HELPER - ELECTHREE E-COMMERCE
// ============================================

// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function: Check apakah user sudah login
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function: Check apakah admin sudah login
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Function: Get user ID yang sedang login
function get_user_id() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Function: Get admin ID yang sedang login
function get_admin_id() {
    return isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
}

// Function: Get user data
function get_user_data() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'nama' => $_SESSION['user_nama'] ?? null,
        'email' => $_SESSION['user_email'] ?? null
    ];
}

// Function: Get admin data
function get_admin_data() {
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'nama' => $_SESSION['admin_nama'] ?? null,
        'email' => $_SESSION['admin_email'] ?? null,
        'role' => $_SESSION['admin_role'] ?? null
    ];
}

// Function: Set user session (saat login)
function set_user_session($user_data) {
    $_SESSION['user_id'] = $user_data['id_user'];
    $_SESSION['user_nama'] = $user_data['nama'];
    $_SESSION['user_email'] = $user_data['email'];
}

// Function: Set admin session (saat login)
function set_admin_session($admin_data) {
    $_SESSION['admin_id'] = $admin_data['id_admin'];
    $_SESSION['admin_nama'] = $admin_data['nama'];
    $_SESSION['admin_email'] = $admin_data['email'];
    $_SESSION['admin_role'] = $admin_data['role'];
}

// Function: Logout user
function logout_user() {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_nama']);
    unset($_SESSION['user_email']);
}

// Function: Logout admin
function logout_admin() {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_nama']);
    unset($_SESSION['admin_email']);
    unset($_SESSION['admin_role']);
}

// Function: Destroy semua session (logout total)
function destroy_session() {
    session_unset();
    session_destroy();
}

// Function: Require login (redirect jika belum login)
function require_login($redirect_to = '../index.php') {
    if (!is_logged_in()) {
        header("Location: $redirect_to");
        exit;
    }
}

// Function: Require admin login
function require_admin_login($redirect_to = 'login.php') {
    if (!is_admin_logged_in()) {
        header("Location: $redirect_to");
        exit;
    }
}
?>
