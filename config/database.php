<?php

// Central bootstrap: paths, session, and the single PDO connection.

date_default_timezone_set('Asia/Manila');
ini_set('display_errors', '0');

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/TaskFlow');

require_once BASE_PATH . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Database credentials live here only.
$db_host = 'localhost';
$db_name = 'taskflow';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    logError($e->getMessage());
    die('Something went wrong. Please try again.');
}
