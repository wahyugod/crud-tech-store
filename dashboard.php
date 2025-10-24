<?php
require_once 'db.php';
require_once 'helpers.php';
require_once 'auth.php';
require_login();
require_once 'header.php';

// ==============================
// DASHBOARD DATA QUERIES
// ==============================

// Key statistics
$stats = [];
$stats['total_products'] = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$stats['total_stock']    = (int)($pdo->query("SELECT COALESCE(SUM(stok),0) FROM products")->fetchColumn() ?? 0);
$stats['total_value']    = (int)($pdo->query("SELECT COALESCE(SUM(harga*stok),0) FROM products")->fetchColumn() ?? 0);
$stats['low_stock']      = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE stok < 10")->fetchColumn();
$stats['avg_price']      = (int)($pdo->query("SELECT COALESCE(AVG(harga),0) FROM products")->fetchColumn() ?? 0);
$stats['avg_stock']      = (int)($pdo->query("SELECT COALESCE(AVG(stok),0) FROM products")->fetchColumn() ?? 0);

// Distribution by type
$productsByType = $pdo->query("SELECT jenis, COUNT(*) total FROM products GROUP BY jenis")->fetchAll(PDO::FETCH_ASSOC);
$stockByType    = $pdo->query("SELECT jenis, COALESCE(SUM(stok),0) total FROM products GROUP BY jenis")->fetchAll(PDO::FETCH_ASSOC);
$valueByType    = $pdo->query("SELECT jenis, COALESCE(SUM(harga*stok),0) total FROM products GROUP BY jenis")->fetchAll(PDO::FETCH_ASSOC);

// Price range buckets
$priceBuckets = [
    'Murah (<5 jt)'   => (int)$pdo->query("SELECT COUNT(*) FROM products WHERE harga < 5000000")->fetchColumn(),
    'Menengah (5-15)' => (int)$pdo->query("SELECT COUNT(*) FROM products WHERE harga >= 5000000 AND harga < 15000000")->fetchColumn(),
    'Mahal (>15 jt)'  => (int)$pdo->query("SELECT COUNT(*) FROM products WHERE harga >= 15000000")->fetchColumn(),
];

// Recent 30 days product additions
$days = [];
for ($i = 29; $i >= 0; $i--) {
        $d = new DateTime();
        $d->modify("-{$i} days");
        $days[$d->format('Y-m-d')] = 0;
}
$stmt = $pdo->query("SELECT DATE(created_at) d, COUNT(*) c FROM products WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) GROUP BY DATE(created_at)");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $days[$row['d']] = (int)$row['c'];
}
$newProductsDates = array_keys($days);
$newProductsCounts = array_values($days);

// Recent and top products
$recentProducts = $pdo->query("SELECT id, name, jenis, merk, stok, harga, created_at FROM products ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$topProducts    = $pdo->query("SELECT id, name, jenis, merk, stok, harga, (stok*harga) total_value FROM products ORDER BY total_value DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Important insights
$lowStockItems  = $pdo->query("SELECT id, name, stok FROM products WHERE stok < 10 ORDER BY stok ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$highestPrice   = $pdo->query("SELECT id, name, harga FROM products ORDER BY harga DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$largestStock   = $pdo->query("SELECT id, name, stok FROM products ORDER BY stok DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
?>

<!-- Dashboard Statistics -->
<div class="dashboard-stats">
    <div class="stats-grid">
        <!-- Total Products Card -->
        <div class="stat-card primary">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?= number_format($stats['total_products']) ?></div>
                    <div class="stat-label">Total Produk</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
            <div class="stat-change up">
                <i class="fas fa-arrow-up"></i> Active
            </div>
        </div>

        <!-- Total Stock Card -->
        <div class="stat-card success">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?= number_format($stats['total_stock']) ?></div>
                    <div class="stat-label">Total Stok</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-cubes"></i>
                </div>
            </div>
            <div class="stat-change up">
                <i class="fas fa-arrow-up"></i> In Stock
            </div>
        </div>

        <!-- Total Value Card -->
        <div class="stat-card warning">
            <div class="stat-header">
                <div>
                    <div class="stat-value">Rp <?= number_format($stats['total_value'], 0, ',', '.') ?></div>
                    <div class="stat-label">Total Nilai</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
            <div class="stat-change up">
                <i class="fas fa-arrow-up"></i> Total Assets
            </div>
        </div>

        <!-- Low Stock Card -->
        <div class="stat-card danger">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?= number_format($stats['low_stock']) ?></div>
                    <div class="stat-label">Stok Rendah</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="stat-change down">
                <i class="fas fa-arrow-down"></i> Need Restock
            </div>
        </div>
    </div>

    <!-- Baris 1: Chart Distribusi (4 kolom: 2+2) -->
    <div class="row row-4-cols">
        <div class="col-span-2">
            <div class="content-card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie"></i> Distribusi Produk per Jenis</h5>
                </div>
                <div class="chart-wrap large"><canvas id="chartJenis"></canvas></div>
            </div>
        </div>

        <div class="col-span-2">
            <div class="content-card">
                <div class="card-header">
                    <h5><i class="fas fa-coins"></i> Distribusi Rentang Harga</h5>
                </div>
                <div class="chart-wrap large"><canvas id="chartPriceBuckets"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Baris 2: Chart Nilai dan Tren -->
    <div class="row row-4-cols">
        <div class="col-span-2">
            <div class="content-card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar"></i> Nilai Persediaan per Jenis</h5>
                </div>
                <div class="chart-wrap large"><canvas id="chartValueByJenis"></canvas></div>
            </div>
        </div>

        <div class="col-span-2">
            <div class="content-card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line"></i> Produk Baru 30 Hari Terakhir</h5>
                </div>
                <div class="chart-wrap large"><canvas id="chartNewProducts"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Informasi Penting (Full Width) -->
    <div class="content-card">
        <div class="card-header">
            <h5><i class="fas fa-info-circle"></i> Informasi Penting</h5>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div>
                <ul style="list-style:none;padding-left:0;margin:0;">
                    <li style="margin-bottom:8px;">
                        <i class="fas fa-tag" style="color:#3498db"></i>
                        Rata-rata Harga: <strong>Rp <?= number_format($stats['avg_price'], 0, ',', '.') ?></strong>
                    </li>
                    <li style="margin-bottom:8px;">
                        <i class="fas fa-box" style="color:#27ae60"></i>
                        Rata-rata Stok: <strong><?= number_format($stats['avg_stock']) ?></strong>
                    </li>
                    <?php if ($highestPrice): ?>
                    <li style="margin-bottom:8px;">
                        <i class="fas fa-crown" style="color:#f39c12"></i>
                        Harga Tertinggi: <a
                            href="detail.php?id=<?= e($highestPrice['id']) ?>"><strong><?= e($highestPrice['name']) ?></strong></a>
                        (Rp <?= number_format($highestPrice['harga'], 0, ',', '.') ?>)
                    </li>
                    <?php endif; ?>
                    <?php if ($largestStock): ?>
                    <li style="margin-bottom:8px;">
                        <i class="fas fa-warehouse" style="color:#8e44ad"></i>
                        Stok Terbanyak: <a
                            href="detail.php?id=<?= e($largestStock['id']) ?>"><strong><?= e($largestStock['name']) ?></strong></a>
                        (<?= number_format($largestStock['stok']) ?> unit)
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

            <div>
                <h6 style="margin-bottom:15px; font-weight:600; color: var(--dark-text);">
                    <i class="fas fa-exclamation-triangle" style="color: var(--accent-color)"></i> Stok Rendah (&lt;=
                    10)
                </h6>
                <?php if (!empty($lowStockItems)): ?>
                <ul style="list-style:none;padding-left:0;margin:0;">
                    <?php foreach ($lowStockItems as $it): ?>
                    <li
                        style="display:flex;justify-content:space-between;margin-bottom:8px;padding:8px;background:#f8f9fa;border-radius:6px;">
                        <span><a href="detail.php?id=<?= e($it['id']) ?>"><?= e($it['name']) ?></a></span>
                        <span class="badge badge-danger"><?= number_format($it['stok']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="alert alert-success" style="margin:0;padding:10px;">Tidak ada item stok rendah.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Products -->
    <div class="content-card">
        <div class="card-header">
            <h5><i class="fas fa-clock"></i> Produk Terbaru</h5>
            <a href="index.php" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th>Merk</th>
                        <th>Stok</th>
                        <th>Harga</th>
                        <th>Ditambahkan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentProducts as $product): ?>
                    <tr>
                        <td><?= e($product['id']) ?></td>
                        <td><strong><?= e($product['name']) ?></strong></td>
                        <td><span class="badge badge-primary"><?= strtoupper(e($product['jenis'])) ?></span></td>
                        <td><?= e($product['merk']) ?></td>
                        <td>
                            <?php if ($product['stok'] < 10): ?>
                            <span class="badge badge-danger"><?= e($product['stok']) ?></span>
                            <?php else: ?>
                            <?= e($product['stok']) ?>
                            <?php endif; ?>
                        </td>
                        <td>Rp <?= number_format($product['harga'], 0, ',', '.') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($product['created_at'])) ?></td>
                        <td>
                            <a href="detail.php?id=<?= e($product['id']) ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit.php?id=<?= e($product['id']) ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 25px;
    margin-bottom: 25px;
}

.row-2-cols {
    grid-template-columns: repeat(2, 1fr);
}

.row-4-cols {
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
}

.col-span-2 {
    grid-column: span 2;
}

.text-muted {
    color: #7f8c8d;
}

@media (max-width: 768px) {

    .row,
    .row-2-cols,
    .row-4-cols {
        grid-template-columns: 1fr;
    }

    .col-span-2 {
        grid-column: span 1;
    }
}
</style>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Data from PHP
const jenisLabels = <?= json_encode(array_column($productsByType, 'jenis')) ?>.map(j => j.toUpperCase());
const jenisCounts = <?= json_encode(array_map('intval', array_column($productsByType, 'total'))) ?>;
const valueLabels = <?= json_encode(array_column($valueByType, 'jenis')) ?>.map(j => j.toUpperCase());
const valueTotals = <?= json_encode(array_map('intval', array_column($valueByType, 'total'))) ?>;
const newDates = <?= json_encode(array_map(function($d){return date('d M', strtotime($d));}, $newProductsDates)) ?>;
const newCounts = <?= json_encode($newProductsCounts) ?>;
const bucketLabels = <?= json_encode(array_keys($priceBuckets)) ?>;
const bucketCounts = <?= json_encode(array_values($priceBuckets)) ?>;

// Colors
const colors = ['#3498db', '#2ecc71', '#e74c3c', '#9b59b6', '#f39c12', '#1abc9c'];

// Chart size helpers - set all to large size (420px)
document.querySelectorAll('.chart-wrap').forEach(el => {
    const isLarge = el.classList.contains('large');
    el.style.height = '420px';
    el.style.position = 'relative';
});

// Doughnut: Produk per Jenis
new Chart(document.getElementById('chartJenis'), {
    type: 'doughnut',
    data: {
        labels: jenisLabels,
        datasets: [{
            data: jenisCounts,
            backgroundColor: colors
        }]
    },
    options: {
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        size: 14
                    }
                }
            }
        },
        maintainAspectRatio: false
    }
});

// Doughnut: Price Buckets
new Chart(document.getElementById('chartPriceBuckets'), {
    type: 'doughnut',
    data: {
        labels: bucketLabels,
        datasets: [{
            data: bucketCounts,
            backgroundColor: ['#2ecc71', '#f1c40f', '#e74c3c']
        }]
    },
    options: {
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        size: 14
                    }
                }
            }
        },
        maintainAspectRatio: false
    }
});

// Bar: Nilai persediaan per Jenis
new Chart(document.getElementById('chartValueByJenis'), {
    type: 'bar',
    data: {
        labels: valueLabels,
        datasets: [{
            label: 'Nilai (Rp)',
            data: valueTotals,
            backgroundColor: '#3498db'
        }]
    },
    options: {
        scales: {
            y: {
                ticks: {
                    callback: v => 'Rp ' + v.toLocaleString('id-ID')
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        },
        maintainAspectRatio: false
    }
});

// Line: Produk Baru 30 Hari
new Chart(document.getElementById('chartNewProducts'), {
    type: 'line',
    data: {
        labels: newDates,
        datasets: [{
            label: 'Produk Baru',
            data: newCounts,
            borderColor: '#27ae60',
            backgroundColor: 'rgba(39, 174, 96, 0.2)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                precision: 0
            }
        },
        maintainAspectRatio: false
    }
});
</script>

<?php require 'footer.php'; ?>