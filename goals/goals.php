<?php
$pageTitle = "Savings Goals - CODE X";
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];

// Fetch User Goals
$stmt = $db->prepare("SELECT * FROM financial_goals WHERE user_id = :uid ORDER BY status ASC, target_date ASC");
$stmt->execute([':uid' => $userId]);
$goals = $stmt->fetchAll();

// Overall Stats
$totalTarget = 0;
$totalSaved = 0;
$completedCount = 0;

foreach ($goals as $g) {
    $totalTarget += (float)$g['target_amount'];
    $totalSaved += (float)$g['current_amount'];
    if ($g['status'] === 'completed') $completedCount++;
}

$overallPct = ($totalTarget > 0) ? min(100, round(($totalSaved / $totalTarget) * 100, 1)) : 0;
?>

<div class="container py-4">
    <!-- Top Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 font-heading text-light mb-1">Financial Savings Goals</h1>
            <p class="small text-secondary mb-0">Set targets for major life milestones and track progress over time</p>
        </div>
        <div class="d-flex gap-2">
            <a href="create_goal.php" class="btn btn-codex-primary"><i class="fa-solid fa-plus me-1"></i> Create New Goal</a>
        </div>
    </div>

    <!-- Summary Overview -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="codex-card p-3 border-info border-opacity-25">
                <span class="text-muted small text-uppercase fw-semibold">Total Savings Target</span>
                <h3 class="h4 font-heading text-info mt-1 mb-0"><?= formatCurrency($totalTarget) ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="codex-card p-3 border-success border-opacity-25">
                <span class="text-muted small text-uppercase fw-semibold">Currently Saved</span>
                <h3 class="h4 font-heading text-success mt-1 mb-0"><?= formatCurrency($totalSaved) ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="codex-card p-3 border-warning border-opacity-25">
                <span class="text-muted small text-uppercase fw-semibold">Overall Goal Completion</span>
                <h3 class="h4 font-heading text-warning mt-1 mb-0"><?= $overallPct ?>%</h3>
            </div>
        </div>
    </div>

    <!-- Goals Grid -->
    <div class="row g-4">
        <?php if (empty($goals)): ?>
            <div class="col-12 text-center text-muted py-5 codex-card">
                <i class="fa-solid fa-bullseye fs-1 mb-2 opacity-25"></i>
                <p class="mb-2">You have not created any financial savings goals yet.</p>
                <a href="create_goal.php" class="btn btn-sm btn-codex-primary">Create Your First Goal</a>
            </div>
        <?php else: ?>
            <?php foreach ($goals as $goal): 
                $targetAmt = (float)$goal['target_amount'];
                $currentAmt = (float)$goal['current_amount'];
                $remainingAmt = max(0, $targetAmt - $currentAmt);
                $pct = ($targetAmt > 0) ? min(100, round(($currentAmt / $targetAmt) * 100, 1)) : 0;
                
                $statusBadge = 'bg-info text-info';
                if ($goal['status'] === 'completed' || $pct >= 100) {
                    $statusBadge = 'bg-success text-success';
                } elseif ($goal['status'] === 'cancelled') {
                    $statusBadge = 'bg-secondary text-secondary';
                }
            ?>
                <div class="col-md-6">
                    <div class="codex-card p-4 h-100 position-relative">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge <?= $statusBadge ?> bg-opacity-20 border border-opacity-25 me-2 text-uppercase fs-7">
                                    <?= sanitize($goal['status']) ?>
                                </span>
                                <h4 class="h5 font-heading text-light d-inline-block mb-0"><?= sanitize($goal['title']) ?></h4>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-codex-outline border-0" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark border-secondary">
                                    <li><a class="dropdown-item" href="create_goal.php?id=<?= $goal['id'] ?>"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Goal</a></li>
                                    <li><a class="dropdown-item text-danger btn-confirm-delete" href="delete_goal.php?id=<?= $goal['id'] ?>&csrf_token=<?= generateCSRFToken() ?>"><i class="fa-solid fa-trash me-2"></i> Delete</a></li>
                                </ul>
                            </div>
                        </div>

                        <p class="small text-secondary mb-3"><?= sanitize($goal['description'] ?: 'No details specified.') ?></p>

                        <!-- Progress Bar -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-secondary mb-1">
                                <span>Saved: <strong class="text-light"><?= formatCurrency($currentAmt) ?></strong></span>
                                <span>Target: <strong class="text-light"><?= formatCurrency($targetAmt) ?></strong> (<?= $pct ?>%)</span>
                            </div>
                            <div class="progress-codex" style="height: 14px;">
                                <div class="progress-bar <?= $pct >= 100 ? 'bg-success' : 'bg-info' ?>" role="progressbar" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>

                        <!-- Update Saved Amount inline form -->
                        <form action="update_goal.php" method="POST" class="d-flex gap-2 align-items-center pt-3 border-top border-secondary border-opacity-25">
                            <input type="hidden" name="goal_id" value="<?= $goal['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-dark border-secondary text-secondary">$</span>
                                <input type="number" step="0.01" min="0" name="current_amount" class="form-control" value="<?= htmlspecialchars($currentAmt) ?>" placeholder="New Saved Amount">
                            </div>
                            <button type="submit" class="btn btn-sm btn-codex-outline text-nowrap" title="Update saved progress">
                                <i class="fa-solid fa-sync me-1"></i> Update
                            </button>
                        </form>

                        <div class="d-flex justify-content-between align-items-center small text-muted mt-2">
                            <span><i class="fa-solid fa-calendar me-1"></i> Target Date: <?= formatDate($goal['target_date']) ?></span>
                            <span class="fw-semibold text-info">Needed: <?= formatCurrency($remainingAmt) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
