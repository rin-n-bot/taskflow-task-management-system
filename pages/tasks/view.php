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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = sanitize($_POST['status'] ?? '');

    if (!isset($statuses[$new_status])) {
        $errors['status'] = 'Choose a valid status.';
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE tasks SET status = ? WHERE id = ?');
            $stmt->execute([$new_status, $id]);

            header('Location: ' . BASE_URL . '/pages/tasks/view.php?id=' . $id . '&msg=updated');
            exit;
        } catch (PDOException $e) {
            logError($e->getMessage());
            $errors['general'] = 'Something went wrong. Please try again.';
        }
    }
}

$notice = isset($_GET['msg']) && $_GET['msg'] === 'updated'
    ? 'Task status updated.'
    : '';

$page_title = 'Task details';
require_once BASE_PATH . '/includes/header.php';
?>

<h1><?php echo e($task['title']); ?></h1>

<?php if ($notice !== ''): ?>
    <div class="alert-success"><?php echo e($notice); ?></div>
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
