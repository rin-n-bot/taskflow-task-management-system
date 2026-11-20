<?php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireRole('admin');

$errors = [];
$title = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');

    $errors = validateProject($_POST);

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO projects (created_by, title, description) VALUES (?, ?, ?)');
            $stmt->execute([
                (int) $_SESSION['user_id'],
                $title,
                $description,
            ]);

            header('Location: ' . BASE_URL . '/pages/projects/index.php?msg=created');
            exit;
        } catch (PDOException $e) {
            logError($e->getMessage());
            $errors['general'] = 'Something went wrong. Please try again.';
        }
    }
}

$page_title = 'New project';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>New project</h1>

<?php if (isset($errors['general'])): ?>
    <div class="alert-error"><?php echo e($errors['general']); ?></div>
<?php endif; ?>

<form method="post" action="<?php echo BASE_URL; ?>/pages/projects/create.php">
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

    <button type="submit" class="btn btn-primary">Create project</button>
    <a class="btn" href="<?php echo BASE_URL; ?>/pages/projects/index.php">Cancel</a>
</form>

<?php require_once BASE_PATH . '/includes/layout_end.php'; ?>
