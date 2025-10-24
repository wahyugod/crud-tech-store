<?php
// auth.php - authentication helpers
if (session_status() === PHP_SESSION_NONE) session_start();

// CSRF helpers
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_validate($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

// Auth state helpers
function is_logged_in(): bool {
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}

function current_user(): ?array {
    return is_logged_in() ? $_SESSION['user'] : null;
}

function login_user(array $user): void {
    // Minimal user info in session
    $_SESSION['user'] = [
        'id' => $user['id'] ?? null,
        'username' => $user['username'] ?? null,
        'name' => $user['name'] ?? null,
        'role' => $user['role'] ?? 'user',
    ];
    if (function_exists('session_regenerate_id')) {
        session_regenerate_id(true);
    }
    // reset login attempts on success
    unset($_SESSION['login_attempts'], $_SESSION['login_lock_until']);
}

function logout_user(): void {
    // Preserve flash but clear other session data
    $flash = $_SESSION['flash'] ?? null;
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    session_start();
    if ($flash) $_SESSION['flash'] = $flash;
}

function require_login(): void {
    if (!is_logged_in()) {
        // Minimal flash function compatibility
        if (!isset($_SESSION['flash'])) $_SESSION['flash'] = [];
        $_SESSION['flash']['error'] = 'Silakan login terlebih dahulu.';
        header('Location: login.php');
        exit;
    }
}
