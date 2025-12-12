<?php
// ============================================
// LOGOUT ADMIN - ELECTHREE E-COMMERCE
// ============================================

require_once '../config/session.php';

// Logout admin
logout_admin();

// Redirect ke halaman login
header("Location: login.php");
exit;
?>
