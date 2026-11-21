<?php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireLogin();

$is_admin = (($_SESSION['role'] ?? '') === 'admin');
$user_id = (int) $_SESSION['user_id'];

$statuses = taskStatuses();
$priorities = taskPriorities();

$status = sanitize($_GET['status'] ?? '');
$priority = sanitize($_GET['priority'] ?? '');
$project_id = (int) ($_GET['project_id'] ?? 0);
$assignee_id = $is_admin ? (int) ($_GET['assignee_id'] ?? 0) : 0;
$keyword = sanitize($_GET['keyword'] ?? '');

// Ignore values that are not in the allowed lists.
if (!isset($statuses[$status])) {
    $status = '';
}

if (!isset($priorities[$priority])) {
    $priority = '';
}

$tasks = [];
$projects = [];
$members = [];
$db_error = '';

try {
    $sql = 'SELECT t.id, t.title, t.description, t.priority, t.status, t.due_date,
                   t.created_at, t.assigned_to, t.project_id,
                   p.title AS project_title, u.name AS assignee_name
            FROM tasks t
            JOIN projects p ON t.project_id = p.id
            JOIN users u ON t.assigned_to = u.id';

    $where = [];
    $params = [];

    // Members only ever see tasks assigned to them.
    if (!$is_admin) {
        $where[] = 't.assigned_to = ?';
        $params[] = $user_id;
    } elseif ($assignee_id > 0) {
        $where[] = 't.assigned_to = ?';
        $params[] = $assignee_id;
    }

    if ($status !== '') {
        $where[] = 't.status = ?';
        $params[] = $status;
    }

    if ($priority !== '') {
        $where[] = 't.priority = ?';
        $params[] = $priority;
    }

    if ($project_id > 0) {
        $where[] = 't.project_id = ?';
        $params[] = $project_id;
    }

    if ($keyword !== '') {
        $where[] = 't.title LIKE ?';
        $params[] = '%' . $keyword . '%';
    }

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY t.due_date ASC, t.created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();

    $stmt = $pdo->query('SELECT id, title FROM projects ORDER BY title');
    $projects = $stmt->fetchAll();

    if ($is_admin) {
        $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'member' ORDER BY name");
        $members = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    logError($e->getMessage());
    $db_error = 'Something went wrong. Please try again.';
}

$problems = [
    'notfound' => 'Task not found.',
    'forbidden' => 'You do not have access to that task.',
    'db' => 'Something went wrong. Please try again.',
];

$notices = [
    'created' => 'Task created.',
    'updated' => 'Task updated.',
    'deleted' => 'Task deleted.',
];

$code = $_GET['msg'] ?? '';
$problem = $problems[$code] ?? '';
$notice = $notices[$code] ?? '';

$has_filters = $status !== '' || $priority !== ''
    || $project_id > 0 || $assignee_id > 0 || $keyword !== '';

$page_title = 'Tasks';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>Tasks</h1>

<?php if ($notice !== ''): ?>
    <div class="alert-success"><?php echo e($notice); ?></div>
<?php endif; ?>

<?php if ($problem !== ''): ?>
    <div class="alert-error"><?php echo e($problem); ?></div>
<?php endif; ?>

<?php if ($db_error !== ''): ?>
    <div class="alert-error"><?php echo e($db_error); ?></div>
<?php endif; ?>

<?php if ($is_admin): ?>
    <div class="row">
        <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/pages/tasks/create.php">New task</a>
    </div>
<?php endif; ?>

<form method="get" action="<?php echo BASE_URL; ?>/pages/tasks/index.php" class="row">
    <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $value => $label): ?>
                <option value="<?php echo e($value); ?>" <?php echo $status === $value ? 'selected' : ''; ?>>
                    <?php echo e($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field">
        <label for="priority">Priority</label>
        <select id="priority" name="priority">
            <option value="">All priorities</option>
            <?php foreach ($priorities as $value => $label): ?>
                <option value="<?php echo e($value); ?>" <?php echo $priority === $value ? 'selected' : ''; ?>>
                    <?php echo e($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field">
        <label for="project_id">Project</label>
        <select id="project_id" name="project_id">
            <option value="0">All projects</option>
            <?php foreach ($projects as $project): ?>
                <option value="<?php echo (int) $project['id']; ?>" <?php echo $project_id === (int) $project['id'] ? 'selected' : ''; ?>>
                    <?php echo e($project['title']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($is_admin): ?>
        <div class="field">
            <label for="assignee_id">Assignee</label>
            <select id="assignee_id" name="assignee_id">
                <option value="0">All members</option>
                <?php foreach ($members as $member): ?>
                    <option value="<?php echo (int) $member['id']; ?>" <?php echo $assignee_id === (int) $member['id'] ? 'selected' : ''; ?>>
                        <?php echo e($member['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <div class="field">
        <label for="keyword">Keyword</label>
        <input type="text" id="keyword" name="keyword" value="<?php echo e($keyword); ?>">
    </div>

    <button type="submit" class="btn btn-primary">Filter</button>
    <?php if ($has_filters): ?>
        <a class="btn" href="<?php echo BASE_URL; ?>/pages/tasks/index.php">Clear</a>
    <?php endif; ?>
</form>

<?php if ($db_error === '' && empty($tasks)): ?>
    <p class="empty">No tasks found.</p>
<?php elseif (!empty($tasks)): ?>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Project</th>
                <th>Assignee</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Due date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?php echo e($task['title']); ?></td>
                    <td><?php echo e($task['project_title']); ?></td>
                    <td><?php echo e($task['assignee_name']); ?></td>
                    <td>
                        <span class="badge-<?php echo e($task['priority']); ?>">
                            <?php echo e($priorities[$task['priority']]); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge-<?php echo e(str_replace('_', '-', $task['status'])); ?>">
                            <?php echo e($statuses[$task['status']]); ?>
                        </span>
                    </td>
                    <td><?php echo e(date('M j, Y', strtotime($task['due_date']))); ?></td>
                    <td>
                        <button type="button" class="btn" aria-label="Open actions for <?php echo e($task['title']); ?>" aria-haspopup="dialog" aria-controls="task-actions-<?php echo (int) $task['id']; ?>" data-action-dialog="task-actions-<?php echo (int) $task['id']; ?>">&#8942;</button>
                        <dialog id="task-actions-<?php echo (int) $task['id']; ?>" aria-labelledby="task-actions-title-<?php echo (int) $task['id']; ?>">
                            <div>
                                <header>
                                    <h2 id="task-actions-title-<?php echo (int) $task['id']; ?>">Task actions</h2>
                                    <form method="dialog">
                                        <button type="submit" class="btn" aria-label="Close actions">&times;</button>
                                    </form>
                                </header>
                                <p><?php echo e($task['title']); ?></p>
                                <a class="btn" href="<?php echo BASE_URL; ?>/pages/tasks/view.php?id=<?php echo (int) $task['id']; ?>">View</a>
                                <?php if ($is_admin): ?>
                                    <a class="btn" href="<?php echo BASE_URL; ?>/pages/tasks/edit.php?id=<?php echo (int) $task['id']; ?>">Edit</a>
                                    <form method="post" action="<?php echo BASE_URL; ?>/pages/tasks/delete.php" onsubmit="return confirm('Delete this task? Its comments will also be deleted.');">
                                        <input type="hidden" name="id" value="<?php echo (int) $task['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </dialog>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once BASE_PATH . '/includes/layout_end.php'; ?>
