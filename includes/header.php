<?php

require_once BASE_PATH . '/includes/auth.php';

$page_title = $page_title ?? 'Task Flow';

// Theme preference comes from a cookie; only light or dark is accepted.
$theme = 'light';
if (isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], ['light', 'dark'], true)) {
    $theme = $_COOKIE['theme'];
}
$next_theme = $theme === 'dark' ? 'light' : 'dark';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?> | Task Flow</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="theme-<?php echo e($theme); ?>">
    <div class="layout">
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
                <a href="<?php echo BASE_URL; ?>/pages/theme.php?theme=<?php echo e($next_theme); ?>">
                    <?php echo $theme === 'dark' ? 'Light mode' : 'Dark mode'; ?>
                </a>
            </nav>
        </aside>

        <div class="content">
            <main class="container stack">
