<?php
require_once 'db.php';
require_once 'helpers.php';
require_once 'auth.php';
require_login();
// delete via GET (dengan konfirmasi JS di UI). Jika ingin aman, ubah ke POST.
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) { flash_set('error','ID tidak valid'); header('Location: index.php'); exit; }
try {
$stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
$stmt->execute([':id'=>$id]);
flash_set('success','Data berhasil dihapus.');
header('Location: index.php'); exit;
} catch (Exception $e) {
error_log('Delete error: ' . $e->getMessage());
flash_set('error','Terjadi kesalahan saat menghapus data.');
header('Location: index.php'); exit;
}