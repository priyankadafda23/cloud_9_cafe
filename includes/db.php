<?php

$envPath = __DIR__ . '/../.env';
$env = [];

if (is_file($envPath)) {
    $parsed = parse_ini_file($envPath);
    if (is_array($parsed)) {
        $env = $parsed;
    }
}

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$db = $env['DB_NAME'] ?? 'cloud_9_cafe';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed.');
}
