<?php
$pageTitle = "Monthly Budgets - CODE X";
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];

// Month & Year Filter (Default to current month/year)
$selectedMonth = (int)($_GET['month'] ?? date('m'));
$selectedYear = (int)($_GET['year'] ?? date('Y'));

// Fetch Budgets with Actual Calculated Expense Spending
$bStmt = $db->prepare("
    SELECT b.*, c.name as category_name, c.icon as category_icon, c.color as category_color,
        COALESCE((
            SELECT SUM(t.amount) 
            FROM transactions t 
            WHERE t.user_id = b.user_id 
              AND t.category_id = b.category_id 
              AND t.transaction_type = 'expense'
              AND MONTH(t.transaction_date) = b.month 
              AND YEAR(t.transaction_date) = b.year
        ), 0) as amount_spent
    FROM budgets b
    JOIN categories c ON b.category_id = c.id
    WHERE b.user_id = :uid AND b.month = :m AND b.year = :y
    ORDER BY c.name ASC
");
$bStmt->execute([':uid' => $userId, ':m' => $selectedMonth, ':y' => $selectedYear]);
$budgets = $bStmt->fetchAll();

// Total Budget Metrics
$totalBudgeted = 0;
$totalSpentInBudgets = 0;
foreach ($budgets as $bg) {
    $totalBudgeted += (float)$bg['amount'];
    $totalSpentInBudgets += (float)$bg['amount_spent'];
}
$overallRemaining = $totalBudgeted - $totalSpentInBudgets;
$overallBudgetPct = ($totalBudgeted > 0) ? min(100, round(($totalSpentInBudgets / $totalBudgeted) * 100)) : 0;
?>

<div class="container py-4">
    <!-- Top Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 font-heading text-light mb-1">Monthly Budget System</h1>
            <p class="small text-secondary mb-0">Control spending limits per category and track visual warnings</p>
        </div>
        <div class="d-flex gap-2">
            <a href="create_budget.php" class="btn btn-codex-primary"><i class="fa-solid fa-plus me-1"></i> Create / Set Budget</a>
        </div>
    </div>

    <!-- Date Picker Filter -->
    <div class="codex-card p-3 mb-4">
        <form action="budgets.php" method="GET" class="row g-3 align-items-center">
            <div class="col-md-4">
                <label class="form-label text-light small mb-0 me-2">Select Month & Year:</label>
            </div>
            <div class="col-md-3">
                <select name="month" class="form-select form-select-sm">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $selectedMonth == $m ? 'selected' : '' ?>>
                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="year" class="form-select form-select-sm">
                    <?php for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++): ?>
                        <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-codex-primary w-100"><i class="fa-solid fa-sync me-1"></i> View</button>
            </div>
        </form>
    </div>

    <!-- Overall Summary Card -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="codex-card p-3 border-primary border-opacity-25">
                <span class="text-muted small text-uppercase fw-semibold">Total Budgeted</span>
                <h3 class="h4 font-heading text-primary mt-1 mb-0"><?= formatCurrency($totalBudgeted) ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="codex-card p-3 border-danger border-opacity-25">
                <span class="text-muted small text-uppercase fw-semibold">Total Spent</span>
                <h3 class="h4 font-heading text-danger mt-1 mb-0"><?= formatCurrency($totalSpentInBudgets) ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="codex-card p-3 border-warning border-opacity-25">
                <span class="text-muted small text-uppercase fw-semibold">Remaining Balance</span>
                <h3 class="h4 font-heading <?= $overallRemaining >= 0 ? 'text-success' : 'text-danger' ?> mt-1 mb-0"><?= formatCurrency($overallRemaining) ?></h3>
            </div>
        </div>
    </div>

    <!-- Category Budgets Grid -->
    <div class="row g-4">
        <?php if (empty($budgets)): ?>
            <div class="col-12 text-center text-muted py-5 codex-card">
                <i class="fa-solid fa-wallet fs-1 mb-2 opacity-25"></i>
                <p class="mb-2">No category budgets established for <?= date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) ?>.</p>
                <a href="create_budget.php" class="btn btn-sm btn-codex-primary">Create First Budget</a>
            </div>
        <?php else: ?>
            <?php foreach ($budgets as $b): 
                $budgetAmt = (float)$b['amount'];
                $spentAmt = (float)$b['amount_spent'];
                $remainingAmt = $budgetAmt - $spentAmt;
                $pctUsed = ($budgetAmt > 0) ? ($spentAmt / $budgetAmt) * 100 : 0;
                
                // Color & Warning Alerts logic
                if ($pctUsed >= 100) {
                    $alertClass = 'alert-danger';
                    $progressClass = 'bg-danger';
                    $warningText = "Warning: You have exceeded your " . sanitize($b['category_name']) . " budget!";
                } elseif ($pctUsed >= 80) {
                    $alertClass = 'alert-warning';
                    $progressClass = 'bg-warning';
                    $warningText = "Notice: You have used " . round($pctUsed) . "% of your " . sanitize($b['category_name']) . " budget.";
                } else {
                    $alertClass = 'alert-success';
                    $progressClass = 'bg-success';
                    $warningText = "On Track: " . round($pctUsed) . "% used.";
                }
            ?>
                <div class="col-md-6">
                    <div class="codex-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="p-2 rounded-3 text-white" style="background-color: <?= sanitize($b['category_color'] ?? '#3b82f6') ?>">
                                    <i class="fa-solid <?= sanitize($b['category_icon'] ?? 'fa-tag') ?>"></i>
                                </div>
                                <div>
                                    <h5 class="h6 font-heading text-light mb-0"><?= sanitize($b['category_name']) ?></h5>
                                    <span class="small text-muted"><?= date('F Y', mktime(0, 0, 0, $b['month'], 1, $b['year'])) ?></span>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-codex-outline border-0" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark border-secondary">
                                    <li><a class="dropdown-item" href="create_budget.php?id=<?= $b['id'] ?>"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Budget</a></li>
                                    <li><a class="dropdown-item text-danger btn-confirm-delete" href="delete_budget.php?id=<?= $b['id'] ?>&csrf_token=<?= generateCSRFToken() ?>"><i class="fa-solid fa-trash me-2"></i> Remove</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Warning Alert Box -->
                        <div class="p-2 rounded mb-3 small <?= $alertClass ?> bg-opacity-10 border border-opacity-25">
                            <i class="fa-solid <?= $pctUsed >= 80 ? 'fa-triangle-exclamation' : 'fa-circle-check' ?> me-1"></i>
                            <?= $warningText ?>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-secondary mb-1">
                                <span>Spent: <strong class="text-light"><?= formatCurrency($spentAmt) ?></strong></span>
                                <span>Target Limit: <strong class="text-light"><?= formatCurrency($budgetAmt) ?></strong></span>
                            </div>
                            <div class="progress-codex" style="height: 12px;">
                                <div class="progress-bar <?= $progressClass ?>" role="progressbar" style="width: <?= min(100, $pctUsed) ?>%"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center small pt-2 border-top border-secondary border-opacity-25">
                            <span class="text-muted">Remaining:</span>
                            <span class="fw-bold <?= $remainingAmt >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= formatCurrency($remainingAmt) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
