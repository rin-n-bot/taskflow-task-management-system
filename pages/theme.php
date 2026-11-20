<?php

require_once dirname(__DIR__) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

// Only light and dark are valid theme values.
$allowed_themes = ['light', 'dark'];
$theme = sanitize($_GET['theme'] ?? '');

if (in_array($theme, $allowed_themes, true)) {
    setcookie('theme', $theme, [
        'expires' => time() + (60 * 60 * 24 * 30),
        'path' => BASE_URL,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// Return to the previous page only when it belongs to this app.
$back = $_SERVER['HTTP_REFERER'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';
$same_app = false;

if ($back !== '' && $host !== '') {
    $parts = parse_url($back);
    $host_name = parse_url('http://' . $host, PHP_URL_HOST);

    if (isset($parts['host'], $parts['path'])
        && $parts['host'] === $host_name
        && strpos($parts['path'], BASE_URL) === 0) {
        $same_app = true;
    }
}

if (!$same_app) {
    $back = isLoggedIn()
        ? BASE_URL . '/pages/dashboard.php'
        : BASE_URL . '/index.php';
}

header('Location: ' . $back);
exit;
