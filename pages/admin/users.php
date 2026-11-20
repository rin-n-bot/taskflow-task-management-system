<?php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireRole('admin');

$own_id = (int) $_SESSION['user_id'];

$errors = [];
$notice = '';
$problem = '';
$users = [];

// Handle role change and delete (both POST, same page).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $target_id = (int) ($_POST['user_id'] ?? 0);

    if ($action === 'change_role') {
        $new_role = sanitize($_POST['role'] ?? '');

        if ($target_id <= 0) {
            $errors['general'] = 'Invalid user.';
        } elseif ($target_id === $own_id) {
            $errors['general'] = 'You cannot change your own role.';
        } elseif (!in_array($new_role, ['admin', 'member'], true)) {
            $errors['general'] = 'Invalid role.';
        } else {
            try {
                $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
                $stmt->execute([$new_role, $target_id]);

                if ($stmt->rowCount() === 0) {
                    header('Location: ' . BASE_URL . '/pages/admin/users.php?msg=notfound');
                    exit;
                }

                header('Location: ' . BASE_URL . '/pages/admin/users.php?msg=role_changed');
                exit;
            } catch (PDOException $e) {
                logError($e->getMessage());
                $errors['general'] = 'Something went wrong. Please try again.';
            }
        }
    } elseif ($action === 'delete_user') {
        if ($target_id <= 0) {
            $errors['general'] = 'Invalid user.';
        } elseif ($target_id === $own_id) {
            $errors['general'] = 'You cannot delete your own account.';
        } else {
            try {
                $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
                $stmt->execute([$target_id]);

                if ($stmt->rowCount() === 0) {
                    header('Location: ' . BASE_URL . '/pages/admin/users.php?msg=notfound');
                    exit;
                }

                header('Location: ' . BASE_URL . '/pages/admin/users.php?msg=user_deleted');
                exit;
            } catch (PDOException $e) {
                logError($e->getMessage());

                // FK constraint: user still owns tasks or projects.
                if ($e->getCode() === '23000') {
                    $errors['general'] = 'Reassign or delete this user\'s tasks and projects first.';
                } else {
                    $errors['general'] = 'Something went wrong. Please try again.';
                }
            }
        }
    }
}

try {
    $stmt = $pdo->query(
        'SELECT id, name, email, role, created_at
         FROM users
         ORDER BY id'
    );
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    logError($e->getMessage());
    $users = [];
}

$roles = ['admin' => 'Admin', 'member' => 'Member'];

$notices = [
    'role_changed' => 'Role updated.',
    'user_deleted' => 'User deleted.',
];

$problems = [
    'notfound' => 'User not found.',
];

$code = $_GET['msg'] ?? '';
$notice = $notices[$code] ?? '';
$problem = $problems[$code] ?? '';

$page_title = 'Users';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>Users</h1>

<?php if ($notice !== ''): ?>
    <div class="alert-success"><?php echo e($notice); ?></div>
<?php endif; ?>

<?php if ($problem !== ''): ?>
    <div class="alert-error"><?php echo e($problem); ?></div>
<?php endif; ?>

<?php if (isset($errors['general'])): ?>
    <div class="alert-error"><?php echo e($errors['general']); ?></div>
<?php endif; ?>

<?php if (empty($users)): ?>
    <p class="empty">No users found.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <?php $is_self = (int) $user['id'] === $own_id; ?>
                <tr>
                    <td><?php echo e($user['name']); ?></td>
                    <td><?php echo e($user['email']); ?></td>
                    <td><?php echo e(ucfirst($user['role'])); ?></td>
                    <td><?php echo e(date('M j, Y', strtotime($user['created_at']))); ?></td>
                    <td>
                        <?php if ($is_self): ?>
                            <span class="empty">You</span>
                        <?php else: ?>
                            <form method="post" action="<?php echo BASE_URL; ?>/pages/admin/users.php" class="row">
                                <input type="hidden" name="action" value="change_role">
                                <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                <select name="role">
                                    <?php foreach ($roles as $value => $label): ?>
                                        <option value="<?php echo e($value); ?>" <?php echo $user['role'] === $value ? 'selected' : ''; ?>>
                                            <?php echo e($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary">Change role</button>
                            </form>
                            <form method="post" action="<?php echo BASE_URL; ?>/pages/admin/users.php" class="row" onsubmit="return confirm('Delete this user? Their owned tasks and projects must be reassigned or deleted first.');">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                <button type="submit" class="btn btn-danger">Delete user</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
