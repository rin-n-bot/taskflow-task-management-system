<?php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireLogin();

$page_title = 'Delete comment';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>Delete comment</h1>
<p class="empty">This page will be built in Phase 8.</p>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
