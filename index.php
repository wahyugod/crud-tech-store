<?php
require_once 'db.php';
require_once 'helpers.php';
require_once 'auth.php';
require_login();
require_once 'header.php';


// Pagination, Search & Sorting
$perPage = isset($_GET['per_page']) ? max(5, min(100, intval($_GET['per_page']))) : 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$filterJenis = isset($_GET['jenis']) ? trim($_GET['jenis']) : '';
$filterHarga = isset($_GET['harga']) ? trim($_GET['harga']) : '';

// Sorting
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sortOrder = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';

// Allowed sort columns (security)
$allowedSortColumns = ['id', 'name', 'jenis', 'merk', 'stok', 'harga', 'created_at'];
if (!in_array($sortBy, $allowedSortColumns)) {
    $sortBy = 'created_at';
}

// Build WHERE clause
$where = '';
$params = [];
$conditions = [];

if ($search !== '') {
    $conditions[] = "(name LIKE ? OR merk LIKE ? OR jenis LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($filterJenis !== '') {
    $conditions[] = "jenis = ?";
    $params[] = $filterJenis;
}

if ($filterHarga !== '') {
    // Filter harga berdasarkan range
    switch ($filterHarga) {
        case 'murah':
            $conditions[] = "harga < 5000000";
            break;
        case 'menengah':
            $conditions[] = "harga >= 5000000 AND harga < 15000000";
            break;
        case 'mahal':
            $conditions[] = "harga >= 15000000";
            break;
    }
}

if (!empty($conditions)) {
    $where = "WHERE " . implode(" AND ", $conditions);
}

// Get unique values for filters
$stmtJenis = $pdo->query("SELECT DISTINCT jenis FROM products ORDER BY jenis");
$jenisOptions = $stmtJenis->fetchAll(PDO::FETCH_COLUMN);

// Harga range options
$hargaOptions = [
    'murah' => 'Murah (< Rp 5 Juta)',
    'menengah' => 'Menengah (Rp 5 - 15 Juta)',
    'mahal' => 'Mahal (> Rp 15 Juta)'
];

// Total count
$sqlCount = "SELECT COUNT(*) FROM products " . $where;
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$total = (int)$stmtCount->fetchColumn();
$totalPages = (int)ceil($total / $perPage);
$offset = ($page - 1) * $perPage;

// Fetch data with sorting
$sql = "SELECT id, name, jenis, merk, stok, harga, created_at FROM products " . $where . " ORDER BY {$sortBy} {$sortOrder} LIMIT ?, ?";
$stmt = $pdo->prepare($sql);
$executeParams = array_merge($params, [$offset, $perPage]);
$stmt->execute($executeParams);
$rows = $stmt->fetchAll();
?>

<!-- Products List Content -->
<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-list"></i> Daftar Produk</h5>
        <div class="d-flex gap-2">
            <a href="create.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Produk
            </a>
            <button class="btn btn-success btn-sm" onclick="exportTableToCSV('produk-<?= date('Y-m-d') ?>.csv')">
                <i class="fas fa-file-export"></i> Export CSV
            </button>
            <button class="btn btn-warning btn-sm" onclick="printTable()">
                <i class="fas fa-print"></i> Print
            </button>
            <button class="btn btn-outline-primary btn-sm" id="refreshData">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Advanced Search & Filter Form -->
    <form method="get" action="index.php" id="filterForm">
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-4">
                <label class="form-label" style="font-size: 0.9rem; font-weight: 500;">
                    <i class="fas fa-search"></i> Pencarian
                </label>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" class="form-control" placeholder="Cari nama, merk, atau jenis..."
                        value="<?= e($search) ?>">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label" style="font-size: 0.9rem; font-weight: 500;">
                    <i class="fas fa-filter"></i> Jenis
                </label>
                <select name="jenis" class="form-control" onchange="document.getElementById('filterForm').submit()">
                    <option value="">-- Semua Jenis --</option>
                    <?php foreach ($jenisOptions as $j): ?>
                    <option value="<?= e($j) ?>" <?= $filterJenis === $j ? 'selected' : '' ?>>
                        <?= strtoupper(e($j)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label" style="font-size: 0.9rem; font-weight: 500;">
                    <i class="fas fa-dollar-sign"></i> Harga
                </label>
                <select name="harga" class="form-control" onchange="document.getElementById('filterForm').submit()">
                    <option value="">-- Semua Harga --</option>
                    <?php foreach ($hargaOptions as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $filterHarga === $key ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label" style="font-size: 0.9rem; font-weight: 500;">
                    <i class="fas fa-list-ol"></i> Per Halaman
                </label>
                <select name="per_page" class="form-control" onchange="document.getElementById('filterForm').submit()">
                    <option value="5" <?= $perPage == 5 ? 'selected' : '' ?>>5</option>
                    <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label" style="font-size: 0.9rem; font-weight: 500;">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <a href="index.php" class="btn btn-outline-primary" title="Reset Filter">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Hidden fields to preserve sorting -->
        <input type="hidden" name="sort" value="<?= e($sortBy) ?>">
        <input type="hidden" name="order" value="<?= e($sortOrder) ?>">
    </form>

    <!-- Active Filters Display -->
    <?php if ($search || $filterJenis || $filterHarga): ?>
    <div style="margin-bottom: 15px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
        <span style="font-weight: 500; color: #7f8c8d;">Filter Aktif:</span>
        <?php if ($search): ?>
        <span class="badge badge-primary" style="padding: 8px 12px; font-size: 0.85rem;">
            Pencarian: "<?= e($search) ?>"
            <a href="?<?= http_build_query(array_diff_key($_GET, ['q' => ''])) ?>"
                style="color: white; margin-left: 5px;">&times;</a>
        </span>
        <?php endif; ?>
        <?php if ($filterJenis): ?>
        <span class="badge badge-success" style="padding: 8px 12px; font-size: 0.85rem;">
            Jenis: <?= strtoupper(e($filterJenis)) ?>
            <a href="?<?= http_build_query(array_diff_key($_GET, ['jenis' => ''])) ?>"
                style="color: white; margin-left: 5px;">&times;</a>
        </span>
        <?php endif; ?>
        <?php if ($filterHarga): ?>
        <span class="badge badge-warning" style="padding: 8px 12px; font-size: 0.85rem;">
            Harga: <?= e($hargaOptions[$filterHarga]) ?>
            <a href="?<?= http_build_query(array_diff_key($_GET, ['harga' => ''])) ?>"
                style="color: white; margin-left: 5px;">&times;</a>
        </span>
        <?php endif; ?>
        <a href="index.php" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-times"></i> Hapus Semua Filter
        </a>
    </div>
    <?php endif; ?>

    <!-- Info -->
    <div class="alert alert-info" style="background-color: rgba(52, 152, 219, 0.1); border-left: 4px solid #3498db;">
        <i class="fas fa-info-circle"></i>
        Menampilkan <strong><?= count($rows) ?></strong> dari <strong><?= $total ?></strong> data produk.
        <?php if ($search || $filterJenis || $filterHarga): ?>
        <span style="color: #e74c3c;">(Terfilter)</span>
        <?php endif; ?>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <?php
                    // Function to create sortable column header
                    function sortableHeader($column, $label, $currentSort, $currentOrder, $params) {
                        $newOrder = ($currentSort === $column && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
                        $params['sort'] = $column;
                        $params['order'] = $newOrder;
                        $url = '?' . http_build_query($params);
                        
                        $icon = '';
                        if ($currentSort === $column) {
                            $icon = $currentOrder === 'ASC' 
                                ? '<i class="fas fa-sort-up"></i>' 
                                : '<i class="fas fa-sort-down"></i>';
                        } else {
                            $icon = '<i class="fas fa-sort" style="opacity: 0.3;"></i>';
                        }
                        
                        $activeClass = $currentSort === $column ? 'style="color: #3498db; font-weight: 600;"' : '';
                        
                        return "<th $activeClass><a href=\"$url\" style=\"color: inherit; text-decoration: none; display: flex; align-items: center; gap: 8px;\">$label $icon</a></th>";
                    }
                    
                    $sortParams = $_GET;
                    unset($sortParams['sort']);
                    unset($sortParams['order']);
                    ?>

                    <?= sortableHeader('id', '#', $sortBy, $sortOrder, $sortParams) ?>
                    <?= sortableHeader('name', 'Nama Produk', $sortBy, $sortOrder, $sortParams) ?>
                    <?= sortableHeader('jenis', 'Jenis', $sortBy, $sortOrder, $sortParams) ?>
                    <?= sortableHeader('merk', 'Merk', $sortBy, $sortOrder, $sortParams) ?>
                    <?= sortableHeader('stok', 'Stok', $sortBy, $sortOrder, $sortParams) ?>
                    <?= sortableHeader('harga', 'Harga', $sortBy, $sortOrder, $sortParams) ?>
                    <?= sortableHeader('created_at', 'Ditambahkan', $sortBy, $sortOrder, $sortParams) ?>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="8" class="text-center" style="padding: 40px;">
                        <i class="fas fa-inbox fa-3x" style="color: #bdc3c7; margin-bottom: 15px;"></i>
                        <p style="color: #7f8c8d; margin: 0;">Tidak ada data produk.</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= e($r['id']) ?></strong></td>
                    <td>
                        <strong><?= e($r['name']) ?></strong>
                    </td>
                    <td>
                        <span class="badge badge-primary"><?= strtoupper(e($r['jenis'])) ?></span>
                    </td>
                    <td><?= e($r['merk']) ?></td>
                    <td>
                        <?php if ($r['stok'] < 10): ?>
                        <span class="badge badge-danger">
                            <i class="fas fa-exclamation-triangle"></i> <?= e($r['stok']) ?>
                        </span>
                        <?php elseif ($r['stok'] < 20): ?>
                        <span class="badge badge-warning"><?= e($r['stok']) ?></span>
                        <?php else: ?>
                        <span class="badge badge-success"><?= e($r['stok']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><strong>Rp <?= number_format($r['harga'], 0, ',', '.') ?></strong></td>
                    <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                    <td class="text-center">
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="detail.php?id=<?= e($r['id']) ?>" class="btn btn-primary btn-sm" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit.php?id=<?= e($r['id']) ?>" class="btn btn-warning btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?= e($r['id']) ?>" class="btn btn-danger btn-sm" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <?php 
            $prevParams = $_GET;
            $prevParams['page'] = $page - 1;
            ?>
        <a href="?<?= http_build_query($prevParams) ?>">
            <i class="fas fa-chevron-left"></i> Prev
        </a>
        <?php endif; ?>

        <?php 
        // Smart pagination - show first, last, and 5 pages around current
        $start = max(1, $page - 2);
        $end = min($totalPages, $page + 2);
        
        if ($start > 1): ?>
        <?php 
            $firstParams = $_GET;
            $firstParams['page'] = 1;
            ?>
        <a href="?<?= http_build_query($firstParams) ?>">1</a>
        <?php if ($start > 2): ?>
        <span style="padding: 8px;">...</span>
        <?php endif; ?>
        <?php endif; ?>

        <?php for ($p = $start; $p <= $end; $p++): ?>
        <?php if ($p == $page): ?>
        <strong><?= $p ?></strong>
        <?php else: ?>
        <?php 
                $pageParams = $_GET;
                $pageParams['page'] = $p;
                ?>
        <a href="?<?= http_build_query($pageParams) ?>"><?= $p ?></a>
        <?php endif; ?>
        <?php endfor; ?>

        <?php if ($end < $totalPages): ?>
        <?php if ($end < $totalPages - 1): ?>
        <span style="padding: 8px;">...</span>
        <?php endif; ?>
        <?php 
            $lastParams = $_GET;
            $lastParams['page'] = $totalPages;
            ?>
        <a href="?<?= http_build_query($lastParams) ?>"><?= $totalPages ?></a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
        <?php 
            $nextParams = $_GET;
            $nextParams['page'] = $page + 1;
            ?>
        <a href="?<?= http_build_query($nextParams) ?>">
            Next <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Keyboard Shortcuts Info -->
    <div
        style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #3498db;">
        <p style="margin: 0; color: #7f8c8d; font-size: 0.9rem;">
            <i class="fas fa-keyboard"></i> <strong>Keyboard Shortcuts:</strong>
            <kbd style="padding: 3px 8px; background: #2c3e50; color: white; border-radius: 4px; margin: 0 5px;">Alt +
                S</kbd> Focus Search
            <kbd style="padding: 3px 8px; background: #2c3e50; color: white; border-radius: 4px; margin: 0 5px;">Alt +
                R</kbd> Reset Filters
            <kbd style="padding: 3px 8px; background: #2c3e50; color: white; border-radius: 4px; margin: 0 5px;">Alt +
                N</kbd> New Product
        </p>
    </div>
</div>


<?php require 'footer.php'; ?>