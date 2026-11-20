<?php

// A user is logged in when the session holds their id.
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Block guests from protected pages.
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/pages/login.php');
        exit;
    }
}

// Block users whose role does not match the page's requirement.
function requireRole($role)
{
    requireLogin();

    if (($_SESSION['role'] ?? '') !== $role) {
        header('Location: ' . BASE_URL . '/pages/dashboard.php');
        exit;
    }
}
