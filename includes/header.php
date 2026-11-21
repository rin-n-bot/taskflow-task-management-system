<?php

require_once BASE_PATH . '/includes/auth.php';

$page_title = $page_title ?? 'Task Flow';
$is_auth_page = in_array($page_title, ['Log in', 'Register'], true);
$has_action_dialogs = in_array($page_title, ['Tasks', 'Projects', 'Users'], true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?> | Task Flow</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=<?php echo filemtime(BASE_PATH . '/assets/css/style.css'); ?>">
    <?php if ($has_action_dialogs): ?>
        <script src="<?php echo BASE_URL; ?>/assets/js/action-dialog.js" defer></script>
    <?php endif; ?>
</head>
<body class="theme-light"<?php echo $is_auth_page ? ' id="auth-page"' : ''; ?>>
    <div class="layout">
        <?php if (!$is_auth_page): ?>
            <aside class="sidebar">
                <a href="<?php echo BASE_URL; ?>/index.php"><strong>Task Flow</strong></a>

                <nav aria-label="Main navigation">
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo BASE_URL; ?>/pages/dashboard.php">Dashboard</a>
                        <a href="<?php echo BASE_URL; ?>/pages/tasks/index.php">Tasks</a>
                        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                            <a href="<?php echo BASE_URL; ?>/pages/projects/index.php">Projects</a>
                            <a href="<?php echo BASE_URL; ?>/pages/admin/users.php">Users</a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>/pages/profile.php">Profile</a>
                        <a href="<?php echo BASE_URL; ?>/pages/logout.php">Log out</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/pages/login.php">Log in</a>
                        <a href="<?php echo BASE_URL; ?>/pages/register.php">Register</a>
                    <?php endif; ?>
                </nav>
            </aside>
        <?php endif; ?>

        <div class="content">
            <main class="container stack"<?php echo $is_auth_page ? ' id="auth-main"' : ''; ?>>
