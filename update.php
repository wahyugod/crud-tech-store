<?php
require_once 'db.php';
require_once 'helpers.php';
require_once 'auth.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
$input = sanitize_input($_POST);
$id = intval($input['id'] ?? 0);
if (!$id) { flash_set('error','ID tidak valid'); header('Location: index.php'); exit; }
$errors = validate_product($input);
if (!empty($errors)) {
flash_set('errors', $errors);
flash_set('old', $input);
header('Location: edit.php?id=' . $id); exit;
}
try {
$sql = "UPDATE products SET name=:name, jenis=:jenis, merk=:merk, stok=:stok, harga=:harga, processor=:processor, vga=:vga, ram=:ram, storage=:storage WHERE id=:id";
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
':id' => $id,
]);
flash_set('success','Data berhasil diperbarui.');
header('Location: index.php'); exit;
} catch (Exception $e) {
error_log('Update error: ' . $e->getMessage());
flash_set('error','Terjadi kesalahan saat memperbarui data.');
flash_set('old', $input);
header('Location: edit.php?id=' . $id); exit;
}