<?php
// header.php - bagian head + flash messages
if (session_status() === PHP_SESSION_NONE) session_start();
// Load auth helpers for user state
require_once __DIR__ . '/auth.php';
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Dashboard - Manajemen Produk</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h3><i class="fas fa-laptop"></i> TechStore</h3>
            <p>Admin Panel</p>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="index.php">
                    <i class="fas fa-list"></i>
                    <span>Daftar Produk</span>
                </a>
            </li>
            <li>
                <a href="create.php">
                    <i class="fas fa-plus-circle"></i>
                    <span>Tambah Produk</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="window.print(); return false;">
                    <i class="fas fa-print"></i>
                    <span>Cetak Laporan</span>
                </a>
            </li>
            <li style="margin-top: 30px; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.1);">
                <?php if (function_exists('is_logged_in') && is_logged_in()): ?>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
                <?php else: ?>
                <a href="login.php">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </a>
                <?php endif; ?>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="navbar-left">
                <button class="btn btn-outline-primary btn-sm" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h4 style="margin-left: 15px; display: inline-block;">Manajemen Produk</h4>
            </div>

            <div class="navbar-right">
                <div id="currentTime" style="color: #7f8c8d; font-size: 0.9rem;">
                    <i class="far fa-clock"></i> <span id="timeDisplay"></span>
                </div>
                <div
                    style="margin-left: 15px; color: #2c3e50; font-size: 0.9rem; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-user-circle"></i>
                    <?php if (function_exists('current_user') && is_logged_in()): $u = current_user(); ?>
                    <span><?= e($u['name'] ?: $u['username']) ?></span>
                    <?php else: ?>
                    <a href="login.php" style="text-decoration:none;">Tamu (Login)</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        <div style="padding: 20px 30px 0;">
            <?php
            $success = flash_get('success');
            $error = flash_get('error');
            if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?= e($success) ?></span>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= e($error) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Page Content -->
        <div style="padding: 0 30px 30px;">