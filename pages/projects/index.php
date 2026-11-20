<?php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireRole('admin');

$page_title = 'Projects';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>Projects</h1>
<p class="empty">This page will be built in Phase 5.</p>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
