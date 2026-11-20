<?php

require_once dirname(__DIR__) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireLogin();

$page_title = 'Profile';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>Profile</h1>
<p class="empty">This page will be built in Phase 10.</p>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
