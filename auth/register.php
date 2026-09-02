<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (isLoggedIn()) {
    header('Location: ' . getBaseUrl() . 'dashboard/dashboard.php');
    exit;
}

$error = '';
$fullName = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCSRFToken($csrfToken)) {
        $error = "Security validation failed (Invalid CSRF Token). Please try again.";
    } elseif (empty($fullName) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        try {
            $db = getDBConnection();
            
            // Check if email exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $error = "An account with this email address already exists.";
            } else {
                // Insert User
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $db->prepare("INSERT INTO users (full_name, email, password, role) VALUES (:name, :email, :pass, 'user')");
                $insertStmt->execute([
                    ':name' => $fullName,
                    ':email' => $email,
                    ':pass' => $hashedPassword
                ]);

                $newUserId = $db->lastInsertId();

                // Log Activity
                logActivity("User registered new account", $newUserId);

                // Auto Login
                $_SESSION['user_id'] = $newUserId;
                $_SESSION['user_name'] = $fullName;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'user';

                setFlash('success', 'Welcome to Code X! Your account has been registered successfully.');
                header('Location: ' . getBaseUrl() . 'dashboard/dashboard.php');
                exit;
            }
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

$pageTitle = "Register Account - CODE X";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="codex-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="bg-primary text-white rounded-3 d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h2 class="h3 text-light font-heading mb-1">Create Code X Account</h2>
                    <p class="small text-secondary">Start understanding and controlling your money today</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger bg-dark border-danger text-danger p-3 rounded mb-4 small">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= sanitize($error) ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-user"></i></span>
                            <input type="text" name="full_name" class="form-control" placeholder="Alex Johnson" value="<?= sanitize($fullName) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="alex@example.com" value="<?= sanitize($email) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="At least 6 characters" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-light small fw-medium">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-shield"></i></span>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-codex-primary btn-lg">
                            <i class="fa-solid fa-user-check me-2"></i>Create Account
                        </button>
                    </div>

                    <div class="text-center small text-secondary">
                        Already have an account? <a href="login.php" class="text-primary fw-medium text-decoration-none">Log In here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
