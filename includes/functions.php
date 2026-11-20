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
