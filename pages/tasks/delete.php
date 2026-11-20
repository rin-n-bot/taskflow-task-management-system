<?php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireRole('admin');

// Deletes only happen through the confirmation form (POST).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/tasks/index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: ' . BASE_URL . '/pages/tasks/index.php?msg=notfound');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id FROM tasks WHERE id = ?');
    $stmt->execute([$id]);

    if (!$stmt->fetch()) {
        header('Location: ' . BASE_URL . '/pages/tasks/index.php?msg=notfound');
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ?');
    $stmt->execute([$id]);

    header('Location: ' . BASE_URL . '/pages/tasks/index.php?msg=deleted');
    exit;
} catch (PDOException $e) {
    logError($e->getMessage());
    header('Location: ' . BASE_URL . '/pages/tasks/index.php?msg=db');
    exit;
}
