<?php
// =====================================================
// XỬ LÝ PHIÊN ĐĂNG NHẬP
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function currentUser() {
    return [
        'id'        => $_SESSION['user_id'] ?? null,
        'username'  => $_SESSION['username'] ?? '',
        'full_name' => $_SESSION['full_name'] ?? '',
        'role'      => $_SESSION['role'] ?? 'staff',
    ];
}

function isAdmin() {
    return ($_SESSION['role'] ?? '') === 'admin';
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}
