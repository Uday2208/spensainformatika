<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'webpresensi');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error
    error_log($e->getMessage(), 3, __DIR__ . '/app/logs/error.log');
    die("Koneksi database gagal. Silakan hubungi administrator.");
}
