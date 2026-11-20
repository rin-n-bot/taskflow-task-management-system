<?php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireRole('admin');

$errors = [];
$db_error = '';
$projects = [];
$members = [];

try {
    $stmt = $pdo->query('SELECT id, title FROM projects ORDER BY title');
    $projects = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'member' ORDER BY name");
    $members = $stmt->fetchAll();
} catch (PDOException $e) {
    logError($e->getMessage());
    $db_error = 'Something went wrong. Please try again.';
}

$project_id = 0;
$assigned_to = 0;
$title = '';
$description = '';
$priority = 'medium';
$status = 'todo';
$due_date = '';

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
                    'INSERT INTO tasks
                        (project_id, assigned_to, title, description, priority, status, due_date)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $project_id,
                    $assigned_to,
                    $title,
                    $description,
                    $priority,
                    $status,
                    $due_date,
                ]);

                header('Location: ' . BASE_URL . '/pages/tasks/index.php?msg=created');
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

$page_title = 'New task';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>New task</h1>

<?php if ($db_error !== ''): ?>
    <div class="alert-error"><?php echo e($db_error); ?></div>
<?php endif; ?>

<?php if (isset($errors['general'])): ?>
    <div class="alert-error"><?php echo e($errors['general']); ?></div>
<?php endif; ?>

<?php if (empty($projects) || empty($members)): ?>
    <div class="alert-info">Create a project and have at least one member account before adding tasks.</div>
<?php endif; ?>

<form method="post" action="<?php echo BASE_URL; ?>/pages/tasks/create.php">
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

    <button type="submit" class="btn btn-primary">Create task</button>
    <a class="btn" href="<?php echo BASE_URL; ?>/pages/tasks/index.php">Cancel</a>
</form>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
