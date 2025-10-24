<?php
require_once 'db.php';
require_once 'helpers.php';
require_once 'auth.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: index.php'); exit;
}
$input = sanitize_input($_POST);
$errors = validate_product($input);
if (!empty($errors)) {
flash_set('errors', $errors);
flash_set('old', $input);
header('Location: create.php'); exit;
}
try {
$sql = "INSERT INTO products (name, jenis, merk, stok, harga, processor, vga, ram, storage) VALUES (:name, :jenis, :merk, :stok, :harga, :processor, :vga, :ram, :storage)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
':name' => $input['name'],
':jenis' => $input['jenis'],
':merk' => $input['merk'],
':stok' => intval($input['stok']),
':harga' => intval($input['harga']),
':processor' => $input['processor'] ?? null,
':vga' => $input['vga'] ?? null,
':ram' => $input['ram'] ?? null,
':storage' => $input['storage'] ?? null,
]);
flash_set('success', 'Data berhasil ditambahkan.');
header('Location: index.php'); exit;
} catch (Exception $e) {
error_log('Insert error: ' . $e->getMessage());
flash_set('error', 'Terjadi kesalahan saat menyimpan data.');
flash_set('old', $input);
header('Location: create.php'); exit;
}