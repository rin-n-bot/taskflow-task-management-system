<?php

require_once dirname(__DIR__) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireLogin();

$user_id = (int) $_SESSION['user_id'];
$errors = [];
$name = '';
$user = [];
$notice = '';

try {
    $stmt = $pdo->prepare('SELECT name, email, role, avatar FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    logError($e->getMessage());
    $errors['general'] = 'Something went wrong. Please try again.';
}

if ($user === false) {
    header('Location: ' . BASE_URL . '/pages/logout.php');
    exit;
}

if ($user !== []) {
    $name = $user['name'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user !== []) {
    $name = sanitize($_POST['name'] ?? '');

    if ($name === '') {
        $errors['name'] = 'Name is required.';
    } elseif (mb_strlen($name) > 100) {
        $errors['name'] = 'Name must be 100 characters or fewer.';
    }

    $avatar_file = $_FILES['avatar'] ?? ['error' => UPLOAD_ERR_NO_FILE];
    $has_avatar_upload = $avatar_file['error'] !== UPLOAD_ERR_NO_FILE;
    $avatar_extension = '';

    if ($has_avatar_upload) {
        if ($avatar_file['error'] === UPLOAD_ERR_INI_SIZE || $avatar_file['error'] === UPLOAD_ERR_FORM_SIZE) {
            $errors['avatar'] = 'Avatar must be 2 MB or smaller.';
        } elseif ($avatar_file['error'] !== UPLOAD_ERR_OK) {
            $errors['avatar'] = 'The avatar could not be uploaded. Please try again.';
        } elseif ($avatar_file['size'] > 2 * 1024 * 1024) {
            $errors['avatar'] = 'Avatar must be 2 MB or smaller.';
        } elseif (!is_uploaded_file($avatar_file['tmp_name'])) {
            $errors['avatar'] = 'Choose a valid image file.';
        } else {
            $extension = strtolower(pathinfo($avatar_file['name'], PATHINFO_EXTENSION));
            $allowed_mime_types = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
            ];

            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $file_mime_type = $file_info ? finfo_file($file_info, $avatar_file['tmp_name']) : false;
            if ($file_info) {
                finfo_close($file_info);
            }

            $image_info = @getimagesize($avatar_file['tmp_name']);

            if (!isset($allowed_mime_types[$extension])
                || $file_mime_type !== $allowed_mime_types[$extension]
                || $image_info === false
                || $image_info['mime'] !== $allowed_mime_types[$extension]) {
                $errors['avatar'] = 'Choose a JPG or PNG image.';
            } else {
                $avatar_extension = $extension === 'png' ? 'png' : 'jpg';
            }
        }
    }

    if (empty($errors)) {
        $avatar_name = $user['avatar'];
        $new_avatar_path = '';

        if ($has_avatar_upload) {
            $avatar_name = bin2hex(random_bytes(16)) . '.' . $avatar_extension;
            $new_avatar_path = BASE_PATH . '/uploads/' . $avatar_name;

            if (!move_uploaded_file($avatar_file['tmp_name'], $new_avatar_path)) {
                $errors['avatar'] = 'The avatar could not be saved. Please try again.';
            }
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare('UPDATE users SET name = ?, avatar = ? WHERE id = ?');
                $stmt->execute([$name, $avatar_name, $user_id]);

                $_SESSION['name'] = $name;

                if ($new_avatar_path !== '' && !empty($user['avatar'])
                    && basename($user['avatar']) === $user['avatar']) {
                    $old_avatar_path = BASE_PATH . '/uploads/' . $user['avatar'];
                    if (is_file($old_avatar_path)) {
                        unlink($old_avatar_path);
                    }
                }

                header('Location: ' . BASE_URL . '/pages/profile.php?msg=updated');
                exit;
            } catch (PDOException $e) {
                logError($e->getMessage());

                if ($new_avatar_path !== '' && is_file($new_avatar_path)) {
                    unlink($new_avatar_path);
                }

                $errors['general'] = 'Something went wrong. Please try again.';
            }
        }
    }
}

if (($_GET['msg'] ?? '') === 'updated') {
    $notice = 'Profile updated.';
}

$page_title = 'Profile';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>Profile</h1>

<?php if ($notice !== ''): ?>
    <div class="alert-success"><?php echo e($notice); ?></div>
<?php endif; ?>

<?php if (isset($errors['general'])): ?>
    <div class="alert-error"><?php echo e($errors['general']); ?></div>
<?php endif; ?>

<?php if ($user !== []): ?>
    <?php if (!empty($user['avatar']) && basename($user['avatar']) === $user['avatar']
        && is_file(BASE_PATH . '/uploads/' . $user['avatar'])): ?>
        <img src="<?php echo BASE_URL; ?>/uploads/<?php echo e($user['avatar']); ?>" alt="Current profile avatar" width="96" height="96">
    <?php else: ?>
        <p>No avatar uploaded.</p>
    <?php endif; ?>

    <p><strong>Email:</strong> <?php echo e($user['email']); ?></p>
    <p><strong>Role:</strong> <?php echo e(ucfirst($user['role'])); ?></p>

    <form method="post" action="<?php echo BASE_URL; ?>/pages/profile.php" enctype="multipart/form-data">
        <div class="field<?php echo isset($errors['name']) ? ' has-error' : ''; ?>">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" maxlength="100" value="<?php echo e($name); ?>" required>
            <?php if (isset($errors['name'])): ?>
                <span class="error"><?php echo e($errors['name']); ?></span>
            <?php endif; ?>
        </div>

        <div class="field<?php echo isset($errors['avatar']) ? ' has-error' : ''; ?>">
            <label for="avatar">Avatar (JPG or PNG, up to 2 MB)</label>
            <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png">
            <?php if (isset($errors['avatar'])): ?>
                <span class="error"><?php echo e($errors['avatar']); ?></span>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Save profile</button>
    </form>
<?php endif; ?>

<?php require_once BASE_PATH . '/includes/layout_end.php'; ?>
