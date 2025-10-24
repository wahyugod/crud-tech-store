<?php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'crud_produk_laptop';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';


$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
$options = [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
PDO::ATTR_EMULATE_PREPARES => false,
];
try {
$pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
// Jangan tampilkan stack trace ke user
error_log('DB connection error: ' . $e->getMessage());
// Pesan generik
die('Koneksi database gagal. Silakan periksa konfigurasi.');
}