<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionUserId = $_SESSION['user_id'] ?? null;
$sessionRole = $_SESSION['role'] ?? null;

if (empty($sessionUserId)) {
    header('Location: /cloud_9_cafe/guest/auth/login.php');
    exit;
}

if ($sessionRole !== null && $sessionRole !== 'user') {
    if ($sessionRole === 'admin') {
        header('Location: /cloud_9_cafe/admin/index.php');
        exit;
    }

    header('Location: /cloud_9_cafe/guest/auth/login.php?error=session_expired');
    exit;
}
