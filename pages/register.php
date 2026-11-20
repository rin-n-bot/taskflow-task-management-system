<?php

require_once dirname(__DIR__) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    $errors = validateUser($_POST);

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $errors['email'] = 'That email is already registered.';
            } else {
                // New accounts are always members; the role column default applies.
                $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
                $stmt->execute([
                    $name,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                ]);

                header('Location: ' . BASE_URL . '/pages/login.php?registered=1');
                exit;
            }
        } catch (PDOException $e) {
            logError($e->getMessage());

            if ($e->getCode() === '23000') {
                $errors['email'] = 'That email is already registered.';
            } else {
                $errors['general'] = 'Something went wrong. Please try again.';
            }
        }
    }
}

$page_title = 'Register';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>Register</h1>

<?php if (isset($errors['general'])): ?>
    <div class="alert-error"><?php echo e($errors['general']); ?></div>
<?php endif; ?>

<form method="post" action="<?php echo BASE_URL; ?>/pages/register.php">
    <div class="field<?php echo isset($errors['name']) ? ' has-error' : ''; ?>">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?php echo e($name); ?>">
        <?php if (isset($errors['name'])): ?>
            <span class="error"><?php echo e($errors['name']); ?></span>
        <?php endif; ?>
    </div>

    <div class="field<?php echo isset($errors['email']) ? ' has-error' : ''; ?>">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo e($email); ?>">
        <?php if (isset($errors['email'])): ?>
            <span class="error"><?php echo e($errors['email']); ?></span>
        <?php endif; ?>
    </div>

    <div class="field<?php echo isset($errors['password']) ? ' has-error' : ''; ?>">
        <label for="password">Password</label>
        <input type="password" id="password" name="password">
        <?php if (isset($errors['password'])): ?>
            <span class="error"><?php echo e($errors['password']); ?></span>
        <?php endif; ?>
    </div>

    <div class="field<?php echo isset($errors['confirm']) ? ' has-error' : ''; ?>">
        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm">
        <?php if (isset($errors['confirm'])): ?>
            <span class="error"><?php echo e($errors['confirm']); ?></span>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Create account</button>
</form>

<p>Already registered? <a href="<?php echo BASE_URL; ?>/pages/login.php">Log in</a></p>

<?php require_once BASE_PATH . '/includes/layout_end.php'; ?>
