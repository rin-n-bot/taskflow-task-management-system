<?php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireRole('admin');

$search = sanitize($_GET['q'] ?? '');
$projects = [];
$db_error = '';

try {
    if ($search !== '') {
        $stmt = $pdo->prepare(
            'SELECT p.id, p.title, p.description, p.created_at, u.name AS creator_name
             FROM projects p
             JOIN users u ON p.created_by = u.id
             WHERE p.title LIKE ?
             ORDER BY p.created_at DESC, p.id DESC'
        );
        $stmt->execute(['%' . $search . '%']);
    } else {
        $stmt = $pdo->query(
            'SELECT p.id, p.title, p.description, p.created_at, u.name AS creator_name
             FROM projects p
             JOIN users u ON p.created_by = u.id
             ORDER BY p.created_at DESC, p.id DESC'
        );
    }

    $projects = $stmt->fetchAll();
} catch (PDOException $e) {
    logError($e->getMessage());
    $db_error = 'Something went wrong. Please try again.';
}

$notices = [
    'created' => 'Project created.',
    'updated' => 'Project updated.',
    'deleted' => 'Project deleted.',
];

$problems = [
    'notfound' => 'Project not found.',
    'db' => 'Something went wrong. Please try again.',
];

$code = $_GET['msg'] ?? '';
$notice = $notices[$code] ?? '';
$problem = $problems[$code] ?? '';

$page_title = 'Projects';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>Projects</h1>

<?php if ($notice !== ''): ?>
    <div class="alert-success"><?php echo e($notice); ?></div>
<?php endif; ?>

<?php if ($problem !== ''): ?>
    <div class="alert-error"><?php echo e($problem); ?></div>
<?php endif; ?>

<?php if ($db_error !== ''): ?>
    <div class="alert-error"><?php echo e($db_error); ?></div>
<?php endif; ?>

<div class="row">
    <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/pages/projects/create.php">New project</a>
</div>

<form method="get" action="<?php echo BASE_URL; ?>/pages/projects/index.php" class="row">
    <div class="field">
        <label for="q">Search by title</label>
        <input type="text" id="q" name="q" value="<?php echo e($search); ?>">
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
    <?php if ($search !== ''): ?>
        <a class="btn" href="<?php echo BASE_URL; ?>/pages/projects/index.php">Clear</a>
    <?php endif; ?>
</form>

<?php if ($db_error === '' && empty($projects)): ?>
    <p class="empty">No projects found.</p>
<?php elseif (!empty($projects)): ?>
    <table class="table" data-row-list>
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Created by</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?php echo e($project['title']); ?></td>
                    <td><?php echo e($project['description']); ?></td>
                    <td><?php echo e($project['creator_name']); ?></td>
                    <td><?php echo e(date('M j, Y', strtotime($project['created_at']))); ?></td>
                    <td>
                        <button type="button" class="btn" data-row-actions-trigger
                            data-dialog-title="Project actions"
                            data-record-label="<?php echo e($project['title']); ?>"
                            data-edit-url="<?php echo BASE_URL; ?>/pages/projects/edit.php?id=<?php echo (int) $project['id']; ?>"
                            data-delete-url="<?php echo BASE_URL; ?>/pages/projects/delete.php"
                            data-delete-field="id"
                            data-delete-id="<?php echo (int) $project['id']; ?>"
                            data-delete-message="Delete this project? Its tasks and comments will also be deleted."
                            aria-label="More actions for <?php echo e($project['title']); ?>">&hellip;</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
