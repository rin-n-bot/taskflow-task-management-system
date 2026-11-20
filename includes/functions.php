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
