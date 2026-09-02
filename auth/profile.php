<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch current user details
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $newPassword = $_POST['new_password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCSRFToken($csrfToken)) {
        $error = "CSRF verification failed.";
    } elseif (empty($fullName) || empty($email)) {
        $error = "Name and email cannot be empty.";
    } else {
        try {
            // Check email uniqueness if changed
            if ($email !== $user['email']) {
                $checkStmt = $db->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
                $checkStmt->execute([':email' => $email, ':id' => $userId]);
                if ($checkStmt->fetch()) {
                    $error = "This email is already in use by another user.";
                }
            }

            if (empty($error)) {
                if (!empty($newPassword)) {
                    if (strlen($newPassword) < 6) {
                        $error = "New password must be at least 6 characters.";
                    } else {
                        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                        $upStmt = $db->prepare("UPDATE users SET full_name = :name, email = :email, password = :pass WHERE id = :id");
                        $upStmt->execute([':name' => $fullName, ':email' => $email, ':pass' => $hashed, ':id' => $userId]);
                    }
                } else {
                    $upStmt = $db->prepare("UPDATE users SET full_name = :name, email = :email WHERE id = :id");
                    $upStmt->execute([':name' => $fullName, ':email' => $email, ':id' => $userId]);
                }

                if (empty($error)) {
                    $_SESSION['user_name'] = $fullName;
                    $_SESSION['user_email'] = $email;
                    logActivity("Updated profile details");
                    setFlash('success', 'Profile updated successfully.');
                    header('Location: profile.php');
                    exit;
                }
            }
        } catch (Exception $e) {
            $error = "Error updating profile: " . $e->getMessage();
        }
    }
}

$pageTitle = "My Profile - CODE X";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="max-w-2xl mx-auto">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 font-heading text-light mb-0"><i class="fa-solid fa-user-gear me-2 text-primary"></i>My Profile Settings</h1>
            <span class="badge bg-primary px-3 py-2 text-uppercase">Role: <?= sanitize($user['role']) ?></span>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger bg-dark border-danger text-danger p-3 rounded mb-4 small">
                <i class="fa-solid fa-circle-exclamation me-2"></i><?= sanitize($error) ?>
            </div>
        <?php endif; ?>

        <div class="codex-card p-4 p-md-5">
            <form action="profile.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <div class="mb-3">
                    <label class="form-label text-light small fw-medium">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= sanitize($user['full_name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-medium">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= sanitize($user['email']) ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label text-light small fw-medium">New Password (leave blank to keep current)</label>
                    <input type="password" name="new_password" class="form-control" placeholder="••••••••">
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top border-secondary border-opacity-25">
                    <span class="small text-muted">Account created: <?= formatDate($user['created_at']) ?></span>
                    <button type="submit" class="btn btn-codex-primary">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
