<?php
require_once 'db.php';
require_once 'helpers.php';
require_once 'auth.php';
require_login();
require_once 'header.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) { flash_set('error','ID tidak valid'); header('Location: index.php'); exit; }
// jika ada old (error) gunakan itu
$old = flash_get('old') ?? null;
$errors = flash_get('errors') ?? [];
if ($old === null) {
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
$stmt->execute([':id'=>$id]);
$old = $stmt->fetch();
if (!$old) { flash_set('error','Data tidak ditemukan'); header('Location: index.php'); exit; }
}
?>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-edit"></i> Edit Produk #<?= e($id) ?></h5>
        <a href="index.php" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <form action="update.php" method="post">
        <input type="hidden" name="id" value="<?= e($id) ?>">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Nama Produk <span style="color: red;">*</span></label>
                    <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'error' : '' ?>"
                        value="<?= e($old['name'] ?? '') ?>" required>
                    <?php if(isset($errors['name'])): ?>
                    <span class="error-message"><?= e($errors['name']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Jenis <span style="color: red;">*</span></label>
                    <select name="jenis" class="form-control <?= isset($errors['jenis']) ? 'error' : '' ?>" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="laptop" <?= (($old['jenis'] ?? '') === 'laptop') ? 'selected' : '' ?>>Laptop
                        </option>
                        <option value="hp" <?= (($old['jenis'] ?? '') === 'hp') ? 'selected' : '' ?>>HP / Smartphone
                        </option>
                        <option value="tablet" <?= (($old['jenis'] ?? '') === 'tablet') ? 'selected' : '' ?>>Tablet
                        </option>
                    </select>
                    <?php if(isset($errors['jenis'])): ?>
                    <span class="error-message"><?= e($errors['jenis']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Merk <span style="color: red;">*</span></label>
                    <input type="text" name="merk" class="form-control <?= isset($errors['merk']) ? 'error' : '' ?>"
                        value="<?= e($old['merk'] ?? '') ?>" required>
                    <?php if(isset($errors['merk'])): ?>
                    <span class="error-message"><?= e($errors['merk']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Stok <span style="color: red;">*</span></label>
                    <input type="text" name="stok" class="form-control <?= isset($errors['stok']) ? 'error' : '' ?>"
                        value="<?= e($old['stok'] ?? '0') ?>" inputmode="numeric" required>
                    <?php if(isset($errors['stok'])): ?>
                    <span class="error-message"><?= e($errors['stok']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Harga (Rp) <span style="color: red;">*</span></label>
                    <input type="text" name="harga" class="form-control <?= isset($errors['harga']) ? 'error' : '' ?>"
                        value="<?= e($old['harga'] ?? '0') ?>" inputmode="numeric" placeholder="Contoh: 15000000"
                        required>
                    <?php if(isset($errors['harga'])): ?>
                    <span class="error-message"><?= e($errors['harga']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Processor</label>
                    <input type="text" name="processor" class="form-control" value="<?= e($old['processor'] ?? '') ?>"
                        placeholder="Contoh: Intel Core i5">
                </div>

                <div class="form-group">
                    <label class="form-label">VGA / GPU</label>
                    <input type="text" name="vga" class="form-control" value="<?= e($old['vga'] ?? '') ?>"
                        placeholder="Contoh: NVIDIA GTX 1650">
                </div>

                <div class="form-group">
                    <label class="form-label">RAM</label>
                    <input type="text" name="ram" class="form-control" value="<?= e($old['ram'] ?? '') ?>"
                        placeholder="Contoh: 8GB DDR4">
                </div>

                <div class="form-group">
                    <label class="form-label">Storage</label>
                    <input type="text" name="storage" class="form-control" value="<?= e($old['storage'] ?? '') ?>"
                        placeholder="Contoh: 512GB SSD">
                </div>
            </div>
        </div>

        <div class="d-flex gap-2" style="margin-top: 30px;">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Update Data
            </button>
            <a href="detail.php?id=<?= e($id) ?>" class="btn btn-primary">
                <i class="fas fa-eye"></i> Lihat Detail
            </a>
            <a href="index.php" class="btn btn-outline-primary">
                <i class="fas fa-times"></i> Batal
            </a>
        </div>
    </form>
</div>

<style>
.row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
}

@media (max-width: 768px) {
    .row {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require 'footer.php'; ?>