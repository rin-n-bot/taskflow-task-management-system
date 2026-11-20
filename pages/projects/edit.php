<?php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ' . BASE_URL . '/pages/projects/index.php?msg=notfound');
    exit;
}

$errors = [];
$title = '';
$description = '';

try {
    $stmt = $pdo->prepare('SELECT id, title, description FROM projects WHERE id = ?');
    $stmt->execute([$id]);
    $project = $stmt->fetch();

    if (!$project) {
        header('Location: ' . BASE_URL . '/pages/projects/index.php?msg=notfound');
        exit;
    }

    $title = $project['title'];
    $description = $project['description'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');

        $errors = validateProject($_POST);

        if (empty($errors)) {
            $stmt = $pdo->prepare('UPDATE projects SET title = ?, description = ? WHERE id = ?');
            $stmt->execute([$title, $description, $id]);

            header('Location: ' . BASE_URL . '/pages/projects/index.php?msg=updated');
            exit;
        }
    }
} catch (PDOException $e) {
    logError($e->getMessage());
    header('Location: ' . BASE_URL . '/pages/projects/index.php?msg=db');
    exit;
}

$page_title = 'Edit project';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>Edit project</h1>

<form method="post" action="<?php echo BASE_URL; ?>/pages/projects/edit.php?id=<?php echo $id; ?>">
    <div class="field<?php echo isset($errors['title']) ? ' has-error' : ''; ?>">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?php echo e($title); ?>">
        <?php if (isset($errors['title'])): ?>
            <span class="error"><?php echo e($errors['title']); ?></span>
        <?php endif; ?>
    </div>

    <div class="field<?php echo isset($errors['description']) ? ' has-error' : ''; ?>">
        <label for="description">Description</label>
        <textarea id="description" name="description"><?php echo e($description); ?></textarea>
        <?php if (isset($errors['description'])): ?>
            <span class="error"><?php echo e($errors['description']); ?></span>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Save changes</button>
    <a class="btn" href="<?php echo BASE_URL; ?>/pages/projects/index.php">Cancel</a>
</form>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
