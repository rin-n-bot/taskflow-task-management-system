<?php

// Trim user input; escaping happens on output with e(), never here.
function sanitize($value)
{
    return trim((string) $value);
}

// Escape any value before printing it to HTML (prevents XSS).
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Write technical errors to logs/error.log; users only see friendly messages.
function logError($message)
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents(BASE_PATH . '/logs/error.log', $line, FILE_APPEND | LOCK_EX);
}

// Validate registration input and return field => message errors.
function validateUser($data)
{
    $errors = [];
    $name = sanitize($data['name'] ?? '');
    $email = sanitize($data['email'] ?? '');
    $password = (string) ($data['password'] ?? '');
    $confirm = (string) ($data['confirm'] ?? '');

    if ($name === '') {
        $errors['name'] = 'Name is required.';
    } elseif (mb_strlen($name) > 100) {
        $errors['name'] = 'Name must be 100 characters or fewer.';
    }

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (mb_strlen($email) > 150) {
        $errors['email'] = 'Email must be 150 characters or fewer.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }

    if ($confirm === '') {
        $errors['confirm'] = 'Please confirm your password.';
    } elseif ($confirm !== $password) {
        $errors['confirm'] = 'Passwords do not match.';
    }

    return $errors;
}

// Validate project input and return field => message errors.
function validateProject($data)
{
    $errors = [];
    $title = sanitize($data['title'] ?? '');
    $description = sanitize($data['description'] ?? '');

    if ($title === '') {
        $errors['title'] = 'Title is required.';
    } elseif (mb_strlen($title) > 150) {
        $errors['title'] = 'Title must be 150 characters or fewer.';
    }

    if (mb_strlen($description) > 1000) {
        $errors['description'] = 'Description must be 1000 characters or fewer.';
    }

    return $errors;
}

// Allowed task statuses with their display labels.
function taskStatuses()
{
    return [
        'todo' => 'To do',
        'in_progress' => 'In progress',
        'done' => 'Done',
    ];
}

// Allowed task priorities with their display labels.
function taskPriorities()
{
    return [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
    ];
}

// Validate task input and return field => message errors.
function validateTask($data)
{
    $errors = [];
    $project_id = (int) ($data['project_id'] ?? 0);
    $assigned_to = (int) ($data['assigned_to'] ?? 0);
    $title = sanitize($data['title'] ?? '');
    $priority = sanitize($data['priority'] ?? '');
    $status = sanitize($data['status'] ?? '');
    $due_date = sanitize($data['due_date'] ?? '');
    $priorities = taskPriorities();
    $statuses = taskStatuses();

    if ($project_id <= 0) {
        $errors['project_id'] = 'Choose a project.';
    }

    if ($assigned_to <= 0) {
        $errors['assigned_to'] = 'Choose a member.';
    }

    if ($title === '') {
        $errors['title'] = 'Title is required.';
    } elseif (mb_strlen($title) > 150) {
        $errors['title'] = 'Title must be 150 characters or fewer.';
    }

    if (!isset($priorities[$priority])) {
        $errors['priority'] = 'Choose a valid priority.';
    }

    if (!isset($statuses[$status])) {
        $errors['status'] = 'Choose a valid status.';
    }

    if ($due_date === '') {
        $errors['due_date'] = 'Due date is required.';
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $due_date);

        // Re-formatting rejects impossible dates such as 2026-02-30.
        if (!$date || $date->format('Y-m-d') !== $due_date) {
            $errors['due_date'] = 'Enter a valid date (YYYY-MM-DD).';
        }
    }

    return $errors;
}
