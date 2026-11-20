<?php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireLogin();

$is_admin = (($_SESSION['role'] ?? '') === 'admin');
$user_id = (int) $_SESSION['user_id'];

// Deleting a comment only happens through the confirmation form (POST).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/tasks/index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$task_id = (int) ($_POST['task_id'] ?? 0);

if ($id <= 0 || $task_id <= 0) {
    header('Location: ' . BASE_URL . '/pages/tasks/index.php?msg=notfound');
    exit;
}

try {
    $stmt = $pdo->prepare(
        'SELECT task_id, user_id FROM comments WHERE id = ?'
    );
    $stmt->execute([$id]);
    $comment = $stmt->fetch();

    if (!$comment) {
        header('Location: ' . BASE_URL . '/pages/tasks/index.php?msg=notfound');
        exit;
    }

    // Admins may delete any comment; members may delete only their own.
    if (!$is_admin && (int) $comment['user_id'] !== $user_id) {
        header('Location: ' . BASE_URL . '/pages/tasks/view.php?id=' . $task_id . '&msg=comment_forbidden');
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM comments WHERE id = ?');
    $stmt->execute([$id]);

    header('Location: ' . BASE_URL . '/pages/tasks/view.php?id=' . $task_id . '&msg=comment_deleted');
    exit;
} catch (PDOException $e) {
    logError($e->getMessage());
    header('Location: ' . BASE_URL . '/pages/tasks/view.php?id=' . $task_id . '&msg=db');
    exit;
}
