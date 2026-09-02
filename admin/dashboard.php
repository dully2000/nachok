<?php
$pageTitle = "Admin Dashboard - CODE X";
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$db = getDBConnection();

// System Statistics
$uCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$txCount = $db->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$totVol = $db->query("SELECT SUM(amount) FROM transactions")->fetchColumn() ?: 0;
$catCount = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// Recent System Activity
$logStmt = $db->query("
    SELECT l.*, u.full_name, u.email 
    FROM system_activity_logs l 
    LEFT JOIN users u ON l.user_id = u.id 
    ORDER BY l.id DESC LIMIT 10
");
$logs = $logStmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 font-heading text-light mb-1"><i class="fa-solid fa-user-shield me-2 text-warning"></i>Administrator Portal</h1>
            <p class="small text-secondary mb-0">Monitor system statistics, manage users, categories, and system logs</p>
        </div>
        <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-25 px-3 py-2">
            Administrator Mode
        </span>
    </div>

    <!-- Admin Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="codex-card p-3 border-primary border-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small text-uppercase">Total Users</span>
                        <h3 class="h3 font-heading text-light mt-1 mb-0"><?= $uCount ?></h3>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-20 text-primary"><i class="fa-solid fa-users"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="codex-card p-3 border-success border-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small text-uppercase">Total Transactions</span>
                        <h3 class="h3 font-heading text-success mt-1 mb-0"><?= $txCount ?></h3>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-20 text-success"><i class="fa-solid fa-receipt"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="codex-card p-3 border-info border-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small text-uppercase">System Volume</span>
                        <h3 class="h3 font-heading text-info mt-1 mb-0"><?= formatCurrency($totVol) ?></h3>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-20 text-info"><i class="fa-solid fa-coins"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="codex-card p-3 border-warning border-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small text-uppercase">System Categories</span>
                        <h3 class="h3 font-heading text-warning mt-1 mb-0"><?= $catCount ?></h3>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-20 text-warning"><i class="fa-solid fa-tags"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Admin Action Shortcuts -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="users.php" class="codex-card p-3 text-decoration-none d-block hover-white">
                <h5 class="h6 font-heading text-light mb-1"><i class="fa-solid fa-user-gear me-2 text-primary"></i>Manage User Accounts &rarr;</h5>
                <p class="small text-secondary mb-0">View user details, update user roles, and enforce security.</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="categories.php" class="codex-card p-3 text-decoration-none d-block hover-white">
                <h5 class="h6 font-heading text-light mb-1"><i class="fa-solid fa-list-check me-2 text-warning"></i>Manage System Categories &rarr;</h5>
                <p class="small text-secondary mb-0">Configure standard income and expense category options.</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="logs.php" class="codex-card p-3 text-decoration-none d-block hover-white">
                <h5 class="h6 font-heading text-light mb-1"><i class="fa-solid fa-shield-halved me-2 text-info"></i>Inspect Activity Logs &rarr;</h5>
                <p class="small text-secondary mb-0">Review system activity audit trails and security logs.</p>
            </a>
        </div>
    </div>

    <!-- System Activity Stream -->
    <div class="codex-card p-4">
        <h5 class="h6 font-heading text-light mb-3"><i class="fa-solid fa-stream me-2 text-primary"></i>Recent System Activity Audit Feed</h5>
        <div class="table-responsive">
            <table class="table table-codex mb-0">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Activity Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="small text-secondary text-nowrap"><?= formatDate($log['created_at']) ?> <?= date('H:i', strtotime($log['created_at'])) ?></td>
                            <td class="fw-medium text-light"><?= sanitize($log['full_name'] ?: 'System / Guest') ?></td>
                            <td class="small text-secondary"><?= sanitize($log['activity']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
