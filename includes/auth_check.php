<?php
/**
 * Code X - Authentication Guard & Session Security
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if logged in user is admin
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Guard page for authenticated users only
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('danger', 'Please log in to access this page.');
        header('Location: ' . getBaseUrl() . 'auth/login.php');
        exit;
    }
}

// Guard page for admin users only
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlash('danger', 'Access denied. Administrator privileges required.');
        header('Location: ' . getBaseUrl() . 'dashboard/dashboard.php');
        exit;
    }
}

// Audit Logger Helper
function logActivity($activity, $userId = null) {
    try {
        $db = getDBConnection();
        $uid = $userId ?? ($_SESSION['user_id'] ?? null);
        $stmt = $db->prepare("INSERT INTO system_activity_logs (user_id, activity) VALUES (:uid, :act)");
        $stmt->execute([':uid' => $uid, ':act' => $activity]);
    } catch (Exception $e) {
        // Silently handle log write failures
    }
}
?>
