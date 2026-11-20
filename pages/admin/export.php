<?php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireRole('admin');

$page_title = 'Export tasks';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>Export tasks</h1>
<p class="empty">This page will be built in Phase 11.</p>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
