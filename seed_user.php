<?php
// seed_user.php - create database, products and users tables and seed sample data
// Note: This script is idempotent and safe to run multiple times.

// Database connection params (must match db.php)
$DB_HOST = '127.0.0.1';
$DB_NAME = 'crud_produk_laptop';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

try {
    // Connect to MySQL server without specifying database to allow CREATE DATABASE
    $dsnServer = "mysql:host={$DB_HOST};charset={$DB_CHARSET}";
    $pdoServer = new PDO($dsnServer, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Create database if not exists
    $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `{$DB_NAME}` CHARACTER SET {$DB_CHARSET} COLLATE {$DB_CHARSET}_unicode_ci");
    echo "Database '{$DB_NAME}' ensured.\n";

    // Connect to the newly created (or existing) database
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Create products table
    $sqlProducts = "CREATE TABLE IF NOT EXISTS `products` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(255) NOT NULL,
      `jenis` VARCHAR(50) DEFAULT NULL,
      `merk` VARCHAR(100) DEFAULT NULL,
      `stok` INT NOT NULL DEFAULT 0,
      `harga` BIGINT NOT NULL DEFAULT 0,
      `processor` VARCHAR(255) DEFAULT NULL,
      `vga` VARCHAR(255) DEFAULT NULL,
      `ram` VARCHAR(100) DEFAULT NULL,
      `storage` VARCHAR(100) DEFAULT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sqlProducts);
    echo "Table 'products' ensured.\n";

    // Create users table
    $sqlUsers = "CREATE TABLE IF NOT EXISTS `users` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `username` VARCHAR(50) NOT NULL UNIQUE,
      `password_hash` VARCHAR(255) NOT NULL,
      `name` VARCHAR(100) DEFAULT NULL,
      `role` VARCHAR(50) NOT NULL DEFAULT 'admin',
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sqlUsers);
    echo "Table 'users' ensured.\n";

    // Seed admin user if not exists
    $adminUser = 'admin';
    $adminPass = 'admin';
    $adminName = 'Administrator';

    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$adminUser]);
    if ($stmt->fetch()) {
        echo "Admin user '{$adminUser}' already exists.\n";
    } else {
        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $ins = $pdo->prepare('INSERT INTO users (username, password_hash, name, role) VALUES (?, ?, ?, ?)');
        $ins->execute([$adminUser, $hash, $adminName, 'admin']);
        echo "Created admin user '{$adminUser}' with password '{$adminPass}'. Please change it after first login.\n";
    }

    // Seed sample products if table empty
    $cnt = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    if ($cnt > 0) {
        echo "Products table already has {$cnt} rows — skipping sample insert.\n";
    } else {
        $samples = [
            ['Laptop A', 'laptop', 'Lenovo', 15, 7500000, 'Intel i5', 'Intel UHD', '8GB', '512GB SSD'],
            ['Laptop B', 'laptop', 'Asus', 8, 12500000, 'Intel i7', 'NVIDIA MX250', '16GB', '1TB SSD'],
            ['Laptop C', 'laptop', 'Acer', 20, 4500000, 'AMD Ryzen 3', 'Integrated', '4GB', '256GB SSD'],
            ['HP Phone X', 'hp', 'HP', 25, 2000000, NULL, NULL, '4GB', '64GB'],
            ['Galaxy S', 'hp', 'Samsung', 18, 8000000, NULL, NULL, '6GB', '128GB'],
            ['iPhone Mini', 'hp', 'Apple', 10, 12000000, NULL, NULL, '4GB', '64GB'],
            ['Tablet One', 'tablet', 'Samsung', 12, 3500000, NULL, NULL, '3GB', '32GB'],
            ['Tablet Pro', 'tablet', 'Apple', 5, 9500000, NULL, NULL, '4GB', '128GB'],
            ['Gaming Laptop', 'laptop', 'MSI', 6, 22000000, 'Intel i9', 'NVIDIA RTX 3070', '32GB', '2TB SSD'],
            ['Ultrabook', 'laptop', 'Dell', 14, 15000000, 'Intel i7', 'Intel Iris', '16GB', '512GB SSD'],
            ['Budget Phone', 'hp', 'Xiaomi', 30, 1500000, NULL, NULL, '3GB', '32GB'],
            ['Chromebook', 'laptop', 'Acer', 9, 4000000, 'Intel Celeron', 'Integrated', '4GB', '64GB eMMC'],
            ['Workstation', 'laptop', 'HP', 4, 30000000, 'Intel Xeon', 'NVIDIA Quadro', '64GB', '4TB SSD'],
            ['Convertible', 'tablet', 'Lenovo', 7, 6800000, 'Intel i5', 'Integrated', '8GB', '256GB SSD'],
            ['Phone Pro', 'hp', 'OnePlus', 11, 9500000, NULL, NULL, '8GB', '128GB'],
            ['Entry Laptop', 'laptop', 'Asus', 22, 3200000, 'Intel i3', 'Integrated', '4GB', '500GB HDD'],
            ['Designer Laptop', 'laptop', 'Apple', 3, 28000000, 'Apple M1', 'Integrated', '16GB', '1TB SSD'],
            ['Media Tablet', 'tablet', 'Huawei', 13, 4200000, NULL, NULL, '4GB', '64GB'],
            ['Pro Phone', 'hp', 'Sony', 6, 11000000, NULL, NULL, '6GB', '256GB'],
            ['Mini Laptop', 'laptop', 'Samsung', 16, 5200000, 'Intel i5', 'Integrated', '8GB', '256GB SSD'],
        ];

        $ins = $pdo->prepare('INSERT INTO products (name, jenis, merk, stok, harga, processor, vga, ram, storage, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $pdo->beginTransaction();
        foreach ($samples as $s) {
            $ins->execute([
                $s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8]
            ]);
        }
        $pdo->commit();
        echo "Inserted " . count($samples) . " sample products.\n";
    }

    echo "Seeding finished. You can now login at /login.php using the admin credentials.\n";

} catch (PDOException $e) {
    echo "DB error: " . $e->getMessage() . "\n";
    exit(1);
}

?>