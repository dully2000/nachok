<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (isLoggedIn()) {
    header('Location: ' . getBaseUrl() . 'dashboard/dashboard.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCSRFToken($csrfToken)) {
        $error = "Security validation failed. Please try again.";
    } elseif (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        try {
            $db = getDBConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate Session for security
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                logActivity("User logged in", $user['id']);

                setFlash('success', 'Welcome back, ' . htmlspecialchars($user['full_name']) . '!');
                
                if ($user['role'] === 'admin') {
                    header('Location: ' . getBaseUrl() . 'admin/dashboard.php');
                } else {
                    header('Location: ' . getBaseUrl() . 'dashboard/dashboard.php');
                }
                exit;
            } else {
                $error = "Invalid email address or password.";
            }
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

$pageTitle = "Login - CODE X";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="codex-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="bg-primary text-white rounded-3 d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        <i class="fa-solid fa-right-to-bracket"></i>
                    </div>
                    <h2 class="h3 text-light font-heading mb-1">Account Login</h2>
                    <p class="small text-secondary">Access your Code X financial dashboard</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger bg-dark border-danger text-danger p-3 rounded mb-4 small">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= sanitize($error) ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" id="emailInput" name="email" class="form-control" placeholder="user@codex.com" value="<?= sanitize($email) ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-light small fw-medium">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" id="passwordInput" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-codex-primary btn-lg">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Log In
                        </button>
                    </div>

                    <!-- Quick Demo Credentials Selector -->
                    <div class="p-3 bg-dark border border-secondary border-opacity-25 rounded-3 mb-3 text-center">
                        <span class="small text-muted d-block mb-2"><i class="fa-solid fa-key me-1 text-warning"></i> Quick Demo Logins:</span>
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="fillDemo('user@codex.com', 'password123')">
                                Demo User
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="fillDemo('admin@codex.com', 'password123')">
                                Demo Admin
                            </button>
                        </div>
                    </div>

                    <div class="text-center small text-secondary">
                        Don't have an account? <a href="register.php" class="text-primary fw-medium text-decoration-none">Register here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function fillDemo(email, pass) {
    document.getElementById('emailInput').value = email;
    document.getElementById('passwordInput').value = pass;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
