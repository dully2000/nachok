<?php
/**
 * Code X - Automated Database Installer & Seeder Script
 * Runs database initialization safely via Web or CLI.
 */

require_once __DIR__ . '/config/app.php';

$host = '127.0.0.1';
$user = 'root';
$pass = '';

$message = '';
$error = '';
$success = false;

try {
    // 1. Connect without selecting database
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 2. Read database.sql file
    $sqlFile = __DIR__ . '/database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("database.sql file not found at " . $sqlFile);
    }

    $sqlContent = file_get_contents($sqlFile);
    
    // Split statements safely
    $statements = array_filter(array_map('trim', explode(';', $sqlContent)));

    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $pdo->exec($stmt);
        }
    }

    $success = true;
    $message = "Code X Database (code_x_db) successfully created and seeded with default categories, admin user (admin@codex.com / password123), and demo user (user@codex.com / password123)!";

} catch (Exception $e) {
    $error = $e->getMessage();
}

// CLI output check
if (php_sapi_name() === 'cli') {
    if ($success) {
        echo "[SUCCESS] " . $message . "\n";
        exit(0);
    } else {
        echo "[ERROR] " . $error . "\n";
        exit(1);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - CODE X</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: 'Inter', sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-setup { background: #1e293b; border: 1px solid #334155; border-radius: 1rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); max-width: 550px; width: 100%; }
        .brand-logo { font-weight: 800; font-size: 1.8rem; background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body>
    <div class="card-setup p-4 p-md-5">
        <div class="text-center mb-4">
            <h1 class="brand-logo"><i class="fa-solid fa-chart-line me-2"></i>CODE X</h1>
            <p class="text-secondary small">Database Setup & Installer</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success bg-dark border-success text-success p-3 rounded mb-4">
                <i class="fa-solid fa-circle-check me-2"></i>
                <strong>Setup Complete!</strong><br>
                <?= sanitize($message) ?>
            </div>
            <div class="card bg-dark border-secondary p-3 mb-4 text-secondary small">
                <strong class="text-light mb-1"><i class="fa-solid fa-key me-1 text-primary"></i> Default Login Credentials:</strong>
                <div><strong>Admin Account:</strong> admin@codex.com / password123</div>
                <div><strong>Demo User Account:</strong> user@codex.com / password123</div>
            </div>
            <div class="d-grid gap-2">
                <a href="index.php" class="btn btn-primary btn-lg fw-bold"><i class="fa-solid fa-house me-2"></i>Go to Code X Homepage</a>
                <a href="auth/login.php" class="btn btn-outline-light"><i class="fa-solid fa-right-to-bracket me-2"></i>Log In Now</a>
            </div>
        <?php else: ?>
            <div class="alert alert-danger bg-dark border-danger text-danger p-3 rounded mb-4">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <strong>Installation Failed!</strong><br>
                <?= sanitize($error) ?>
            </div>
            <div class="d-grid">
                <a href="setup_database.php" class="btn btn-warning fw-bold"><i class="fa-solid fa-rotate-right me-2"></i>Try Again</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
