<?php

require_once __DIR__ . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

// Logged-in users go straight to their dashboard.
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

$page_title = 'Welcome';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>Task Flow</h1>
<p>Plan projects, assign tasks, and track progress with your team.</p>
<div class="row">
    <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/pages/login.php">Log in</a>
    <a class="btn" href="<?php echo BASE_URL; ?>/pages/register.php">Create an account</a>
</div>

<?php require_once BASE_PATH . '/includes/layout_end.php'; ?>
