<?php
// auth.php
// Simple authentication helper: start session and protect pages

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect to login page if not authenticated.
 * Keeps the current URL in "next" so we can return after login.
 */
function require_auth() {
    if (!isset($_SESSION['user_id'])) {
        $current = $_SERVER['REQUEST_URI'] ?? '/';
        header('Location: login.php?next=' . urlencode($current));
        exit;
    }
}

/** Return current user's email or null */
function current_user_email() {
    return $_SESSION['user_email'] ?? null;
}
