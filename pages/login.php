<?php

require_once dirname(__DIR__) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

$errors = [];
$email = '';
$remember = false;

// The remember-username cookie pre-fills the email on later visits.
if (isset($_COOKIE['remember_email'])) {
    $email = sanitize($_COOKIE['remember_email']);
    $remember = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('SELECT id, name, password_hash, role FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // New session id after login prevents session fixation.
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                if ($remember) {
                    setcookie('remember_email', $email, [
                        'expires' => time() + (60 * 60 * 24 * 30),
                        'path' => BASE_URL,
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
                } else {
                    setcookie('remember_email', '', [
                        'expires' => time() - 3600,
                        'path' => BASE_URL,
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
                }

                header('Location: ' . BASE_URL . '/pages/dashboard.php');
                exit;
            }

            // Same message for unknown email and wrong password.
            $errors['general'] = 'Invalid email or password.';
        } catch (PDOException $e) {
            logError($e->getMessage());
            $errors['general'] = 'Something went wrong. Please try again.';
        }
    }
}

$page_title = 'Log in';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>Log in</h1>

<?php if (isset($_GET['registered'])): ?>
    <div class="alert-success">Account created. You can log in now.</div>
<?php endif; ?>

<?php if (isset($errors['general'])): ?>
    <div class="alert-error"><?php echo e($errors['general']); ?></div>
<?php endif; ?>

<form method="post" action="<?php echo BASE_URL; ?>/pages/login.php">
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

    <div class="field">
        <label>
            <input type="checkbox" name="remember" value="1" <?php echo $remember ? 'checked' : ''; ?>>
            Remember my email
        </label>
    </div>

    <button type="submit" class="btn btn-primary">Log in</button>
</form>

<p>No account yet? <a href="<?php echo BASE_URL; ?>/pages/register.php">Register</a></p>

<?php require_once BASE_PATH . '/includes/layout_end.php'; ?>
