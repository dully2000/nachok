<?php
$pageTitle = "Activity Logs - CODE X Admin";
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$db = getDBConnection();

// Fetch System Activity Audit Logs
$logs = $db->query("
    SELECT l.*, u.full_name, u.email 
    FROM system_activity_logs l 
    LEFT JOIN users u ON l.user_id = u.id 
    ORDER BY l.id DESC LIMIT 100
")->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 font-heading text-light mb-1"><i class="fa-solid fa-list-check me-2 text-info"></i>System Activity Logs</h1>
            <p class="small text-secondary mb-0">Audit log trail of user logins, registrations, and system events</p>
        </div>
        <a href="dashboard.php" class="btn btn-codex-outline"><i class="fa-solid fa-arrow-left me-1"></i> Admin Dashboard</a>
    </div>

    <div class="codex-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-codex mb-0">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>Timestamp</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Activity Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="5" class="text-muted text-center py-4">No activity logs recorded.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="small text-muted">#<?= $log['id'] ?></td>
                                <td class="small text-secondary text-nowrap"><?= formatDate($log['created_at']) ?> <?= date('H:i:s', strtotime($log['created_at'])) ?></td>
                                <td class="fw-medium text-light"><?= sanitize($log['full_name'] ?: 'System / Guest') ?></td>
                                <td class="small text-secondary"><?= sanitize($log['email'] ?: 'N/A') ?></td>
                                <td class="small text-light"><?= sanitize($log['activity']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
