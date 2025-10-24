<?php
require_once 'db.php';
require_once 'helpers.php';
require_once 'auth.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = null;

// Note: removed session-based rate limiting per request from earlier implementation

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $token = $_POST['csrf_token'] ?? '';

    if (!csrf_validate($token)) {
        $error = 'Token CSRF tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } elseif ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, username, password_hash, name, role FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                $stored = $user['password_hash'];
                // Prefer secure verification
                if (password_verify($password, $stored)) {
                    login_user($user);
                    flash_set('success', 'Login berhasil. Selamat datang, ' . ($user['name'] ?: $user['username']) . '!');
                    header('Location: dashboard.php');
                    exit;
                }

                // Legacy MD5 support: if stored hash looks like MD5 (32 hex chars), verify and upgrade
                if (is_string($stored) && preg_match('/^[a-f0-9]{32}$/i', $stored) && md5($password) === $stored) {
                    // Re-hash with password_hash and update database
                    try {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $update = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                        $update->execute([$newHash, $user['id']]);
                    } catch (PDOException $e) {
                        // non-fatal: log and continue to login
                        error_log('Failed to upgrade MD5 password hash for user ' . $user['username'] . ': ' . $e->getMessage());
                    }

                    login_user($user);
                    flash_set('success', 'Login berhasil. Password Anda telah diperbarui ke metode yang lebih aman.');
                    header('Location: dashboard.php');
                    exit;
                }

                // Authentication failed
                $error = 'Username atau password salah.';
            } else {
                $error = 'Username atau password salah.';
            }
        } catch (PDOException $e) {
            error_log('Login query error: ' . $e->getMessage());
            $error = 'Terjadi kesalahan saat memproses login. Pastikan tabel users sudah tersedia.';
        }
    }
}

// Flash errors if any
if ($error) {
    flash_set('error', $error);
}

// Prepare flash messages
$success = flash_get('success');
$flashError = flash_get('error');
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - TechStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
    body {
        background: #f4f6f8;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-card {
        width: 100%;
        max-width: 420px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .login-header {
        padding: 24px;
        background: linear-gradient(135deg, #2c3e50, #34495e);
        color: #ecf0f1;
    }

    .login-body {
        padding: 24px;
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 12px;
        justify-content: center;
    }

    .brand i {
        font-size: 28px;
    }

    .brand>div {
        text-align: center;
    }

    .form-control {
        height: 44px;
    }

    .btn-login {
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .small-muted {
        color: #7f8c8d;
        font-size: 0.9rem;
    }
    </style>
    <script>
    // Prevent double submit
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('loginForm');
        if (form) {
            let submitting = false;
            form.addEventListener('submit', (e) => {
                if (submitting) {
                    e.preventDefault();
                    return;
                }
                submitting = true;
            });
        }
    });
    </script>
</head>

<body>
    <div class="login-card">
        <div class="login-header">
            <div class="brand">
                <i class="fas fa-laptop"></i>
                <div>
                    <div style="font-size: 1.1rem; font-weight: 600;">TechStore Admin</div>
                </div>
            </div>
        </div>
        <div class="login-body">
            <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= e($success) ?></div>
            <?php endif; ?>
            <?php if ($flashError): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= e($flashError) ?></div>
            <?php endif; ?>
            <form method="post" id="loginForm" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password"
                        required>
                </div>
                <!-- Centered Box Masuk -->
                <button type="submit" class="btn btn-primary w-100 btn-login">
                    <i class="fas fa-sign-in-alt"></i> Masuk
                </button>
            </form>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>