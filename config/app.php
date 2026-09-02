<?php
/**
 * Code X - Global Application Settings & Helper Functions
 */

if (!ob_get_level()) {
    ob_start();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'CODE X');
define('APP_TAGLINE', 'Understand Your Money. Control Your Future.');
define('APP_VERSION', '2.0.27 (Final Year IT Project Edition)');
define('CURRENCY_SYMBOL', '$'); // Standard financial symbol (or custom format)

// Standard Financial Disclaimer required on all guidance interfaces
define('FINANCIAL_DISCLAIMER', 'Educational Financial Guidance Only. Code X provides personal financial education and automated budget tracking assistance only. It does NOT constitute professional financial, investment, tax, banking, or legal advice.');

// Sanitize Output Helper
function sanitize($data) {
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

// Format Currency Helper
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . number_format((float)$amount, 2);
}

// Format Date Helper
function formatDate($dateStr) {
    if (empty($dateStr)) return 'N/A';
    return date('M d, Y', strtotime($dateStr));
}

// Security CSRF Generator
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Validator
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Flash Message Helpers
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
