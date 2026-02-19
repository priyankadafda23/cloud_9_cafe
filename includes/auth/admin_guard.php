<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionUserId = $_SESSION['user_id'] ?? null;
$sessionAdminId = $_SESSION['admin_id'] ?? null;
$sessionRole = $_SESSION['role'] ?? null;

$hasAdminSession = !empty($sessionAdminId) || (!empty($sessionUserId) && $sessionRole === 'admin');

if (!$hasAdminSession) {
    header('Location: /cloud_9_cafe/admin/auth/login.php');
    exit;
}

if ($sessionRole !== null && $sessionRole !== 'admin') {
    header('Location: /cloud_9_cafe/user/index.php');
    exit;
}
