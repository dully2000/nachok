<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();

$db = getDBConnection();
$currentAdminId = $_SESSION['user_id'];

// Change Role Action
if (isset($_POST['action']) && $_POST['action'] === 'toggle_role') {
    $targetUid = (int)$_POST['user_id'];
    $newRole = sanitize($_POST['new_role']);
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (verifyCSRFToken($csrfToken) && $targetUid !== $currentAdminId) {
        $stmt = $db->prepare("UPDATE users SET role = :role WHERE id = :id");
        $stmt->execute([':role' => $newRole, ':id' => $targetUid]);
        logActivity("Admin changed role of user ID #{$targetUid} to {$newRole}");
        setFlash('success', 'User role updated successfully.');
    }
    header('Location: users.php');
    exit;
}

// Delete User Action
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $targetUid = (int)($_GET['id'] ?? 0);
    $token = $_GET['csrf_token'] ?? '';

    if (verifyCSRFToken($token) && $targetUid > 0 && $targetUid !== $currentAdminId) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $targetUid]);
        logActivity("Admin deleted user ID #{$targetUid}");
        setFlash('success', 'User account removed successfully.');
    } else {
        setFlash('danger', 'Cannot delete your own admin account or invalid request.');
    }
    header('Location: users.php');
    exit;
}

// Fetch all registered users
$users = $db->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();

$pageTitle = "User Management - CODE X Admin";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 font-heading text-light mb-1"><i class="fa-solid fa-users me-2 text-primary"></i>User Account Management</h1>
            <p class="small text-secondary mb-0">View registered users, change system roles, and delete accounts</p>
        </div>
        <a href="dashboard.php" class="btn btn-codex-outline"><i class="fa-solid fa-arrow-left me-1"></i> Admin Dashboard</a>
    </div>

    <div class="codex-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-codex mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email Address</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>#<?= $u['id'] ?></td>
                            <td class="fw-bold text-light"><?= sanitize($u['full_name']) ?></td>
                            <td class="text-secondary small"><?= sanitize($u['email']) ?></td>
                            <td>
                                <span class="badge <?= $u['role'] === 'admin' ? 'bg-warning text-dark' : 'bg-primary text-white' ?> text-uppercase fs-7">
                                    <?= sanitize($u['role']) ?>
                                </span>
                            </td>
                            <td class="small text-secondary"><?= formatDate($u['created_at']) ?></td>
                            <td class="text-center text-nowrap">
                                <?php if ($u['id'] !== $currentAdminId): ?>
                                    <form action="users.php" method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="toggle_role">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                        <input type="hidden" name="new_role" value="<?= $u['role'] === 'admin' ? 'user' : 'admin' ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-info me-1" title="Switch Role">
                                            <i class="fa-solid fa-user-shield"></i> Set <?= $u['role'] === 'admin' ? 'User' : 'Admin' ?>
                                        </button>
                                    </form>
                                    <a href="users.php?action=delete&id=<?= $u['id'] ?>&csrf_token=<?= generateCSRFToken() ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" title="Delete User">
                                        <i class="fa-solid fa-user-xmark"></i> Delete
                                    </a>
                                <?php else: ?>
                                    <span class="small text-muted italic">Your Account</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
