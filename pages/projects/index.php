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
    <table class="table">
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
                        <button type="button" class="btn" aria-label="Open actions for <?php echo e($project['title']); ?>" aria-haspopup="dialog" aria-controls="project-actions-<?php echo (int) $project['id']; ?>" data-action-dialog="project-actions-<?php echo (int) $project['id']; ?>">&#8942;</button>
                        <dialog id="project-actions-<?php echo (int) $project['id']; ?>" aria-labelledby="project-actions-title-<?php echo (int) $project['id']; ?>">
                            <div>
                                <header>
                                    <h2 id="project-actions-title-<?php echo (int) $project['id']; ?>">Project actions</h2>
                                    <form method="dialog">
                                        <button type="submit" class="btn" aria-label="Close actions">&times;</button>
                                    </form>
                                </header>
                                <p><?php echo e($project['title']); ?></p>
                                <a class="btn" href="<?php echo BASE_URL; ?>/pages/projects/edit.php?id=<?php echo (int) $project['id']; ?>">Edit</a>
                                <form method="post" action="<?php echo BASE_URL; ?>/pages/projects/delete.php" onsubmit="return confirm('Delete this project? Its tasks and comments will also be deleted.');">
                                    <input type="hidden" name="id" value="<?php echo (int) $project['id']; ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </dialog>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once BASE_PATH . '/includes/layout_end.php'; ?>
