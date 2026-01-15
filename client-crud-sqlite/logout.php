<?php
// logout.php - destroy session and redirect to login
if (session_status() === PHP_SESSION_NONE) session_start();

// Clear session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    setcookie(session_name(), '', time() - 42000, '/');
}
session_destroy();

header('Location: login.php?msg=' . urlencode('Déconnecté avec succès.'));
exit;
