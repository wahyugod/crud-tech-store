<?php
// helpers.php - fungsi utilitas
if (session_status() === PHP_SESSION_NONE) session_start();


function flash_set($key, $value) {
$_SESSION['flash'][$key] = $value;
}
function flash_get($key) {
if (!isset($_SESSION['flash'])) return null;
$val = $_SESSION['flash'][$key] ?? null;
unset($_SESSION['flash'][$key]);
return $val;
}


function e($str) {
return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


function sanitize_input($data) {
// Trim and remove excessive whitespace
foreach ($data as $k => $v) {
if (is_string($v)) $data[$k] = trim($v);
}
 // Normalize numeric fields (remove thousands separators or non-digits)
 if (isset($data['harga'])) {
 $data['harga'] = preg_replace('/[^0-9]/', '', (string)$data['harga']);
 }
 if (isset($data['stok'])) {
 $data['stok'] = preg_replace('/[^0-9]/', '', (string)$data['stok']);
 }
return $data;
}


function validate_product($data) {
$errors = [];
// required fields
if (empty($data['name'])) $errors['name'] = 'Nama produk wajib diisi.';
if (empty($data['jenis'])) $errors['jenis'] = 'Jenis wajib diisi.';
if (empty($data['merk'])) $errors['merk'] = 'Merk wajib diisi.';


// stok harus integer >= 0
if ($data['stok'] === '' || !is_numeric($data['stok']) || intval($data['stok']) < 0) {
$errors['stok'] = 'Stok harus bilangan bulat >= 0.';
}
// harga harus numeric >=0
if ($data['harga'] === '' || !is_numeric($data['harga']) || floatval($data['harga']) < 0) {
$errors['harga'] = 'Harga harus angka >= 0.';
}


// panjang field opsional cek
foreach (['processor','vga','ram','storage'] as $f) {
if (isset($data[$f]) && strlen($data[$f]) > 255) $errors[$f] = ucfirst($f) . ' terlalu panjang.';
}


return $errors;
}