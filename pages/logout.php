<?php

require_once dirname(__DIR__) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/login.php');
    exit;
}

$_SESSION = [];

// Expire the session cookie so the browser drops it too.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax',
    ]);
}

session_destroy();

header('Location: ' . BASE_URL . '/pages/login.php');
exit;
