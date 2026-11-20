<?php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireLogin();

$is_admin = (($_SESSION['role'] ?? '') === 'admin');
$user_id = (int) $_SESSION['user_id'];
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ' . BASE_URL . '/pages/tasks/index.php?msg=notfound');
    exit;
}

try {
    $stmt = $pdo->prepare(
        'SELECT t.id, t.title, t.description, t.priority, t.status, t.due_date,
                t.created_at, t.assigned_to, t.project_id,
                p.title AS project_title, u.name AS assignee_name
         FROM tasks t
         JOIN projects p ON t.project_id = p.id
         JOIN users u ON t.assigned_to = u.id
         WHERE t.id = ?'
    );
    $stmt->execute([$id]);
    $task = $stmt->fetch();
} catch (PDOException $e) {
    logError($e->getMessage());
    header('Location: ' . BASE_URL . '/pages/tasks/index.php?msg=db');
    exit;
}

if (!$task) {
    header('Location: ' . BASE_URL . '/pages/tasks/index.php?msg=notfound');
    exit;
}

// Members may only open tasks assigned to them.
if (!$is_admin && (int) $task['assigned_to'] !== $user_id) {
    header('Location: ' . BASE_URL . '/pages/tasks/index.php?msg=forbidden');
    exit;
}

$statuses = taskStatuses();
$priorities = taskPriorities();
$errors = [];

// Add a comment: admin or the assigned member may comment on this task.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = sanitize($_POST['body'] ?? '');

    if ($body === '') {
        $errors['body'] = 'Comment is required.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO comments (task_id, user_id, body) VALUES (?, ?, ?)'
            );
            $stmt->execute([$id, $user_id, $body]);

            header('Location: ' . BASE_URL . '/pages/tasks/view.php?id=' . $id . '&msg=comment_added');
            exit;
        } catch (PDOException $e) {
            logError($e->getMessage());
            $errors['general'] = 'Something went wrong. Please try again.';
        }
    }
}

try {
    $stmt = $pdo->prepare(
        'SELECT c.id, c.body, c.created_at, u.name AS commenter_name
         FROM comments c
         JOIN users u ON c.user_id = u.id
         WHERE c.task_id = ?
         ORDER BY c.created_at DESC'
    );
    $stmt->execute([$id]);
    $comments = $stmt->fetchAll();
} catch (PDOException $e) {
    logError($e->getMessage());
    $db_error = 'Something went wrong. Please try again.';
    $comments = [];
}

$notices = [
    'updated'        => 'Task status updated.',
    'comment_added'  => 'Comment added.',
    'comment_deleted' => 'Comment deleted.',
];

$problems = [
    'notfound'      => 'Task not found.',
    'forbidden'     => 'You do not have access to that task.',
    'comment_forbidden' => 'You can only delete your own comments.',
    'db'            => 'Something went wrong. Please try again.',
];

$code = $_GET['msg'] ?? '';
$notice = $notices[$code] ?? '';
$problem = $problems[$code] ?? '';

$page_title = 'Task details';
require_once BASE_PATH . '/includes/header.php';
?>

<h1><?php echo e($task['title']); ?></h1>

<?php if ($notice !== ''): ?>
    <div class="alert-success"><?php echo e($notice); ?></div>
<?php endif; ?>

<?php if ($problem !== ''): ?>
    <div class="alert-error"><?php echo e($problem); ?></div>
<?php endif; ?>

<?php if (isset($db_error)): ?>
    <div class="alert-error"><?php echo e($db_error); ?></div>
<?php endif; ?>

<?php if (isset($errors['general'])): ?>
    <div class="alert-error"><?php echo e($errors['general']); ?></div>
<?php endif; ?>

<div class="row">
    <span class="badge-<?php echo e($task['priority']); ?>">
        <?php echo e($priorities[$task['priority']]); ?> priority
    </span>
    <span class="badge-<?php echo e(str_replace('_', '-', $task['status'])); ?>">
        <?php echo e($statuses[$task['status']]); ?>
    </span>
</div>

<p><?php echo $task['description'] !== '' ? e($task['description']) : 'No description.'; ?></p>

<table class="table">
    <tbody>
        <tr>
            <th>Project</th>
            <td><?php echo e($task['project_title']); ?></td>
        </tr>
        <tr>
            <th>Assignee</th>
            <td><?php echo e($task['assignee_name']); ?></td>
        </tr>
        <tr>
            <th>Due date</th>
            <td><?php echo e(date('M j, Y', strtotime($task['due_date']))); ?></td>
        </tr>
        <tr>
            <th>Created</th>
            <td><?php echo e(date('M j, Y', strtotime($task['created_at']))); ?></td>
        </tr>
    </tbody>
</table>

<h2>Comments</h2>

<?php if (empty($comments)): ?>
    <p class="empty">No comments yet.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Commenter</th>
                <th>Comment</th>
                <th>Posted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comments as $comment): ?>
                <tr>
                    <td><?php echo e($comment['commenter_name']); ?></td>
                    <td><?php echo nl2br(e($comment['body'])); ?></td>
                    <td><?php echo e(date('M j, Y \a\t H:i', strtotime($comment['created_at']))); ?></td>
                    <td>
                        <?php if ($is_admin || (int) $comment['user_id'] === $user_id): ?>
                            <form method="post" action="<?php echo BASE_URL; ?>/pages/tasks/comment_delete.php" onsubmit="return confirm('Delete this comment?');">
                                <input type="hidden" name="id" value="<?php echo (int) $comment['id']; ?>">
                                <input type="hidden" name="task_id" value="<?php echo (int) $task['id']; ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<form method="post" action="<?php echo BASE_URL; ?>/pages/tasks/view.php?id=<?php echo (int) $task['id']; ?>">
    <div class="field<?php echo isset($errors['body']) ? ' has-error' : ''; ?>">
        <label for="body">Add a comment</label>
        <textarea id="body" name="body" rows="3"><?php echo isset($_POST['body']) ? e(sanitize($_POST['body'])) : ''; ?></textarea>
        <?php if (isset($errors['body'])): ?>
            <span class="error"><?php echo e($errors['body']); ?></span>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Add comment</button>
</form>

<form method="post" action="<?php echo BASE_URL; ?>/pages/tasks/view.php?id=<?php echo (int) $task['id']; ?>" class="row">
    <div class="field<?php echo isset($errors['status']) ? ' has-error' : ''; ?>">
        <label for="status">Update status</label>
        <select id="status" name="status">
            <?php foreach ($statuses as $value => $label): ?>
                <option value="<?php echo e($value); ?>" <?php echo $task['status'] === $value ? 'selected' : ''; ?>>
                    <?php echo e($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['status'])): ?>
            <span class="error"><?php echo e($errors['status']); ?></span>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Save status</button>
</form>

<p><a href="<?php echo BASE_URL; ?>/pages/tasks/index.php">Back to tasks</a></p>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
