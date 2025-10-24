<?php
require_once 'db.php';
require_once 'helpers.php';
require_once 'auth.php';
require_login();
require_once 'header.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) { flash_set('error','ID tidak valid'); header('Location: index.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
$stmt->execute([':id'=>$id]);
$row = $stmt->fetch();
if (!$row) { flash_set('error','Data tidak ditemukan'); header('Location: index.php'); exit; }
?>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-info-circle"></i> Detail Produk #<?= e($row['id']) ?></h5>
        <div class="d-flex gap-2">
            <a href="edit.php?id=<?= e($row['id']) ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="index.php" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-span-1">
            <div style="background: #f8f9fa; padding: 25px; border-radius: 10px; margin-bottom: 20px;">
                <h3 style="color: #2c3e50; margin-bottom: 10px;"><?= e($row['name']) ?></h3>
                <div class="d-flex gap-2" style="margin-bottom: 15px;">
                    <span class="badge badge-primary"><?= strtoupper(e($row['jenis'])) ?></span>
                    <span class="badge badge-success"><?= e($row['merk']) ?></span>
                </div>
                <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                    <div>
                        <p style="color: #7f8c8d; margin: 0; font-size: 0.9rem;">Stok</p>
                        <p style="font-size: 1.5rem; font-weight: 600; margin: 5px 0;">
                            <?php if ($row['stok'] < 10): ?>
                            <span style="color: #e74c3c;"><?= e($row['stok']) ?> <small>unit</small></span>
                            <?php else: ?>
                            <span style="color: #27ae60;"><?= e($row['stok']) ?> <small>unit</small></span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <p style="color: #7f8c8d; margin: 0; font-size: 0.9rem;">Harga</p>
                        <p style="font-size: 1.5rem; font-weight: 600; color: #2c3e50; margin: 5px 0;">
                            Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                        </p>
                    </div>
                    <div>
                        <p style="color: #7f8c8d; margin: 0; font-size: 0.9rem;">Total Nilai</p>
                        <p style="font-size: 1.5rem; font-weight: 600; color: #f39c12; margin: 5px 0;">
                            Rp <?= number_format($row['harga'] * $row['stok'], 0, ',', '.') ?>
                        </p>
                    </div>
                </div>
            </div>

            <h5 style="margin-bottom: 15px; color: #2c3e50;"><i class="fas fa-microchip"></i> Spesifikasi</h5>
            <table class="data-table">
                <tbody>
                    <tr>
                        <th style="width: 200px;"><i class="fas fa-microchip"></i> Processor</th>
                        <td><?= e($row['processor']) ?: '<span style="color: #bdc3c7;">-</span>' ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-desktop"></i> VGA / GPU</th>
                        <td><?= e($row['vga']) ?: '<span style="color: #bdc3c7;">-</span>' ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-memory"></i> RAM</th>
                        <td><?= e($row['ram']) ?: '<span style="color: #bdc3c7;">-</span>' ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-hdd"></i> Storage</th>
                        <td><?= e($row['storage']) ?: '<span style="color: #bdc3c7;">-</span>' ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-span-1">
            <div
                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 10px; color: white; margin-bottom: 20px;">
                <h5 style="margin-bottom: 20px;"><i class="fas fa-chart-line"></i> Status Produk</h5>
                <div style="margin-bottom: 15px;">
                    <p style="margin: 0; opacity: 0.9;">Status Stok</p>
                    <?php if ($row['stok'] < 10): ?>
                    <h6 style="margin: 5px 0;"><i class="fas fa-exclamation-triangle"></i> Stok Rendah</h6>
                    <?php elseif ($row['stok'] < 20): ?>
                    <h6 style="margin: 5px 0;"><i class="fas fa-info-circle"></i> Stok Sedang</h6>
                    <?php else: ?>
                    <h6 style="margin: 5px 0;"><i class="fas fa-check-circle"></i> Stok Tersedia</h6>
                    <?php endif; ?>
                </div>
            </div>

            <div style="background: #fff; padding: 20px; border-radius: 10px; border: 2px solid #ecf0f1;">
                <h6 style="margin-bottom: 15px; color: #2c3e50;"><i class="far fa-clock"></i> Informasi Waktu</h6>
                <div style="margin-bottom: 12px;">
                    <p style="color: #7f8c8d; margin: 0; font-size: 0.85rem;">Ditambahkan</p>
                    <p style="margin: 5px 0; font-weight: 500;"><?= date('d F Y, H:i', strtotime($row['created_at'])) ?>
                    </p>
                </div>
                <div>
                    <p style="color: #7f8c8d; margin: 0; font-size: 0.85rem;">Terakhir Diubah</p>
                    <p style="margin: 5px 0; font-weight: 500;"><?= date('d F Y, H:i', strtotime($row['updated_at'])) ?>
                    </p>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <a href="edit.php?id=<?= e($row['id']) ?>" class="btn btn-warning"
                    style="width: 100%; margin-bottom: 10px;">
                    <i class="fas fa-edit"></i> Edit Produk
                </a>
                <a href="delete.php?id=<?= e($row['id']) ?>" class="btn btn-danger" style="width: 100%;">
                    <i class="fas fa-trash"></i> Hapus Produk
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
}

@media (max-width: 768px) {
    .row {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require 'footer.php'; ?>