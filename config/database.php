<?php
/**
 * Code X - Database Connection Configuration
 * Uses PHP Data Objects (PDO) with prepared statements for high security.
 */

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'code_x_db');
define('DB_CHARSET', 'utf8mb4');

function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Check if database doesn't exist yet and redirect to setup
            if ($e->getCode() == 1049 || strpos($e->getMessage(), "Unknown database") !== false) {
                header("Location: " . getBaseUrl() . "setup_database.php");
                exit;
            }
            die("Database Connection Error: " . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

// Global Helper to get Base URL dynamically
function getBaseUrl() {
    $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';

    if ($docRoot && stripos($projectRoot, $docRoot) === 0) {
        $relativePath = substr($projectRoot, strlen($docRoot));
        $relativePath = str_replace('\\', '/', trim($relativePath, '/\\'));
        return $relativePath === '' ? '/' : '/' . $relativePath . '/';
    }

    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dir = preg_replace('#/(auth|admin|dashboard|transactions|budgets|goals|reports|ai)(/.*)?$#i', '', dirname($scriptName));
    $dir = trim(str_replace('\\', '/', $dir), '/.');
    return $dir === '' ? '/' : '/' . $dir . '/';
}
?>
