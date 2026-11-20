<?php

require_once dirname(__DIR__) . '/config/database.php';
require_once BASE_PATH . '/includes/auth.php';

requireLogin();

$user_id = (int) $_SESSION['user_id'];
$is_admin = (($_SESSION['role'] ?? '') === 'admin');
$user_name = $_SESSION['name'] ?? 'there';
$dashboard_error = '';

$task_counts = [
    'todo' => 0,
    'in_progress' => 0,
    'done' => 0,
    'overdue' => 0,
];
$project_count = 0;
$member_count = 0;

try {
    $task_sql = 'SELECT
                    COUNT(CASE WHEN status = ? THEN 1 END) AS todo_count,
                    COUNT(CASE WHEN status = ? THEN 1 END) AS in_progress_count,
                    COUNT(CASE WHEN status = ? THEN 1 END) AS done_count,
                    COUNT(CASE WHEN due_date < CURDATE() AND status <> ? THEN 1 END) AS overdue_count
                 FROM tasks';
    $task_params = ['todo', 'in_progress', 'done', 'done'];

    if (!$is_admin) {
        $task_sql .= ' WHERE assigned_to = ?';
        $task_params[] = $user_id;
    }

    $task_stmt = $pdo->prepare($task_sql);
    $task_stmt->execute($task_params);
    $task_summary = $task_stmt->fetch();

    $task_counts = [
        'todo' => (int) $task_summary['todo_count'],
        'in_progress' => (int) $task_summary['in_progress_count'],
        'done' => (int) $task_summary['done_count'],
        'overdue' => (int) $task_summary['overdue_count'],
    ];

    if ($is_admin) {
        $project_stmt = $pdo->query('SELECT COUNT(*) FROM projects');
        $project_count = (int) $project_stmt->fetchColumn();

        $member_stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'member'");
        $member_count = (int) $member_stmt->fetchColumn();
    }
} catch (PDOException $e) {
    logError($e->getMessage());
    $dashboard_error = 'Something went wrong. Please try again.';
}

$page_title = 'Dashboard';
require_once BASE_PATH . '/includes/header.php';
?>

<h1>Dashboard</h1>
<p>Welcome, <?php echo e($user_name); ?>. Here is your task summary.</p>

<?php if ($dashboard_error !== ''): ?>
    <div class="alert-error"><?php echo e($dashboard_error); ?></div>
<?php else: ?>
    <section aria-labelledby="task-summary-heading">
        <h2 id="task-summary-heading">Tasks</h2>
        <div class="row">
            <section aria-label="To do tasks">
                <h3>To do</h3>
                <p><strong><?php echo $task_counts['todo']; ?></strong></p>
            </section>
            <section aria-label="In progress tasks">
                <h3>In progress</h3>
                <p><strong><?php echo $task_counts['in_progress']; ?></strong></p>
            </section>
            <section aria-label="Completed tasks">
                <h3>Done</h3>
                <p><strong><?php echo $task_counts['done']; ?></strong></p>
            </section>
            <section aria-label="Overdue tasks">
                <h3>Overdue</h3>
                <p class="is-overdue"><strong><?php echo $task_counts['overdue']; ?></strong></p>
            </section>
        </div>
    </section>

    <?php if ($is_admin): ?>
        <section aria-labelledby="team-summary-heading">
            <h2 id="team-summary-heading">Team</h2>
            <div class="row">
                <section aria-label="Projects">
                    <h3>Projects</h3>
                    <p><strong><?php echo $project_count; ?></strong></p>
                </section>
                <section aria-label="Members">
                    <h3>Members</h3>
                    <p><strong><?php echo $member_count; ?></strong></p>
                </section>
            </div>
        </section>
    <?php endif; ?>

    <p><a class="btn btn-primary" href="<?php echo BASE_URL; ?>/pages/tasks/index.php">View tasks</a></p>
<?php endif; ?>

<?php require_once BASE_PATH . '/includes/layout_end.php'; ?>
