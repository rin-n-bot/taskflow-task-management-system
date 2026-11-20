<?php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ' . BASE_URL . '/pages/tasks/index.php?msg=notfound');
    exit;
}

$errors = [];
$projects = [];
$members = [];

try {
    $stmt = $pdo->prepare(
        'SELECT id, project_id, assigned_to, title, description, priority, status, due_date
         FROM tasks WHERE id = ?'
    );
    $stmt->execute([$id]);
    $task = $stmt->fetch();

    if (!$task) {
        header('Location: ' . BASE_URL . '/pages/tasks/index.php?msg=notfound');
        exit;
    }

    $stmt = $pdo->query('SELECT id, title FROM projects ORDER BY title');
    $projects = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'member' ORDER BY name");
    $members = $stmt->fetchAll();
} catch (PDOException $e) {
    logError($e->getMessage());
    header('Location: ' . BASE_URL . '/pages/tasks/index.php?msg=db');
    exit;
}

$project_id = (int) $task['project_id'];
$assigned_to = (int) $task['assigned_to'];
$title = $task['title'];
$description = $task['description'] ?? '';
$priority = $task['priority'];
$status = $task['status'];
$due_date = $task['due_date'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = (int) ($_POST['project_id'] ?? 0);
    $assigned_to = (int) ($_POST['assigned_to'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $priority = sanitize($_POST['priority'] ?? '');
    $status = sanitize($_POST['status'] ?? '');
    $due_date = sanitize($_POST['due_date'] ?? '');

    $errors = validateTask($_POST);

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('SELECT id FROM projects WHERE id = ?');
            $stmt->execute([$project_id]);
            $project_exists = (bool) $stmt->fetch();

            $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'member'");
            $stmt->execute([$assigned_to]);
            $member_exists = (bool) $stmt->fetch();

            if (!$project_exists) {
                $errors['project_id'] = 'Choose a valid project.';
            }

            if (!$member_exists) {
                $errors['assigned_to'] = 'Choose a valid member.';
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare(
                    'UPDATE tasks
                     SET project_id = ?, assigned_to = ?, title = ?, description = ?,
                         priority = ?, status = ?, due_date = ?
                     WHERE id = ?'
                );
                $stmt->execute([
                    $project_id,
                    $assigned_to,
                    $title,
                    $description,
                    $priority,
                    $status,
                    $due_date,
                    $id,
                ]);

                header('Location: ' . BASE_URL . '/pages/tasks/index.php?msg=updated');
                exit;
            }
        } catch (PDOException $e) {
            logError($e->getMessage());
            $errors['general'] = 'Something went wrong. Please try again.';
        }
    }
}

$statuses = taskStatuses();
$priorities = taskPriorities();

$page_title = 'Edit task';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>Edit task</h1>

<?php if (isset($errors['general'])): ?>
    <div class="alert-error"><?php echo e($errors['general']); ?></div>
<?php endif; ?>

<form method="post" action="<?php echo BASE_URL; ?>/pages/tasks/edit.php?id=<?php echo $id; ?>">
    <div class="field<?php echo isset($errors['project_id']) ? ' has-error' : ''; ?>">
        <label for="project_id">Project</label>
        <select id="project_id" name="project_id">
            <option value="0">Select project</option>
            <?php foreach ($projects as $project): ?>
                <option value="<?php echo (int) $project['id']; ?>" <?php echo $project_id === (int) $project['id'] ? 'selected' : ''; ?>>
                    <?php echo e($project['title']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['project_id'])): ?>
            <span class="error"><?php echo e($errors['project_id']); ?></span>
        <?php endif; ?>
    </div>

    <div class="field<?php echo isset($errors['assigned_to']) ? ' has-error' : ''; ?>">
        <label for="assigned_to">Assignee</label>
        <select id="assigned_to" name="assigned_to">
            <option value="0">Select member</option>
            <?php foreach ($members as $member): ?>
                <option value="<?php echo (int) $member['id']; ?>" <?php echo $assigned_to === (int) $member['id'] ? 'selected' : ''; ?>>
                    <?php echo e($member['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['assigned_to'])): ?>
            <span class="error"><?php echo e($errors['assigned_to']); ?></span>
        <?php endif; ?>
    </div>

    <div class="field<?php echo isset($errors['title']) ? ' has-error' : ''; ?>">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?php echo e($title); ?>">
        <?php if (isset($errors['title'])): ?>
            <span class="error"><?php echo e($errors['title']); ?></span>
        <?php endif; ?>
    </div>

    <div class="field">
        <label for="description">Description</label>
        <textarea id="description" name="description"><?php echo e($description); ?></textarea>
    </div>

    <div class="field<?php echo isset($errors['priority']) ? ' has-error' : ''; ?>">
        <label for="priority">Priority</label>
        <select id="priority" name="priority">
            <?php foreach ($priorities as $value => $label): ?>
                <option value="<?php echo e($value); ?>" <?php echo $priority === $value ? 'selected' : ''; ?>>
                    <?php echo e($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['priority'])): ?>
            <span class="error"><?php echo e($errors['priority']); ?></span>
        <?php endif; ?>
    </div>

    <div class="field<?php echo isset($errors['status']) ? ' has-error' : ''; ?>">
        <label for="status">Status</label>
        <select id="status" name="status">
            <?php foreach ($statuses as $value => $label): ?>
                <option value="<?php echo e($value); ?>" <?php echo $status === $value ? 'selected' : ''; ?>>
                    <?php echo e($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['status'])): ?>
            <span class="error"><?php echo e($errors['status']); ?></span>
        <?php endif; ?>
    </div>

    <div class="field<?php echo isset($errors['due_date']) ? ' has-error' : ''; ?>">
        <label for="due_date">Due date</label>
        <input type="date" id="due_date" name="due_date" value="<?php echo e($due_date); ?>">
        <?php if (isset($errors['due_date'])): ?>
            <span class="error"><?php echo e($errors['due_date']); ?></span>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Save changes</button>
    <a class="btn" href="<?php echo BASE_URL; ?>/pages/tasks/index.php">Cancel</a>
</form>

<?php require_once BASE_PATH . '/includes/layout_end.php'; ?>
