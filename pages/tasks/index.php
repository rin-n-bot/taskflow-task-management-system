<?php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireLogin();

$page_title = 'Tasks';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>Tasks</h1>
<p class="empty">This page will be built in Phase 6.</p>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
