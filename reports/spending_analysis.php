<?php
$pageTitle = "Financial Spending Analysis - CODE X";
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];

// 1. Income Breakdown Analysis
$incStmt = $db->prepare("
    SELECT c.name, SUM(t.amount) as total_amount, COUNT(t.id) as count
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = :uid AND t.transaction_type = 'income'
    GROUP BY c.id, c.name
    ORDER BY total_amount DESC
");
$incStmt->execute([':uid' => $userId]);
$incomeSources = $incStmt->fetchAll();

$totalIncome = 0;
foreach ($incomeSources as $is) { $totalIncome += (float)$is['total_amount']; }

// 2. Expense Breakdown Analysis
$expStmt = $db->prepare("
    SELECT c.name, SUM(t.amount) as total_amount, COUNT(t.id) as count
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = :uid AND t.transaction_type = 'expense'
    GROUP BY c.id, c.name
    ORDER BY total_amount DESC
");
$expStmt->execute([':uid' => $userId]);
$expenseSources = $expStmt->fetchAll();

$totalExpense = 0;
foreach ($expenseSources as $es) { $totalExpense += (float)$es['total_amount']; }

// 3. Savings & Savings Rate Analysis
// Savings = Total Income - Total Expenses
$totalSavings = max(0, $totalIncome - $totalExpense);

// Savings Rate: (Total Savings / Total Income) * 100
$savingsRate = ($totalIncome > 0) ? round(($totalSavings / $totalIncome) * 100, 2) : 0.0;
?>

<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 font-heading text-light mb-1">Financial Analysis</h1>
            <p class="small text-secondary mb-0">Deep automated mathematical analysis of your income, expense distribution, and savings rate</p>
        </div>
        <div>
            <a href="export_report.php?type=analysis" class="btn btn-codex-outline"><i class="fa-solid fa-file-csv me-1 text-success"></i> Export Analysis CSV</a>
        </div>
    </div>

    <!-- Savings Analysis Metric Card -->
    <div class="codex-card p-4 mb-4 border-primary border-opacity-25">
        <div class="row align-items-center g-4">
            <div class="col-md-4 text-center border-end border-secondary border-opacity-25">
                <span class="text-muted small text-uppercase fw-semibold">Net Total Savings</span>
                <h2 class="display-6 font-heading text-success mt-1 mb-0"><?= formatCurrency($totalSavings) ?></h2>
                <span class="small text-dim">Formula: Total Income - Total Expenses</span>
            </div>
            <div class="col-md-4 text-center border-end border-secondary border-opacity-25">
                <span class="text-muted small text-uppercase fw-semibold">Calculated Savings Rate</span>
                <h2 class="display-6 font-heading text-warning mt-1 mb-0"><?= $savingsRate ?>%</h2>
                <span class="small text-dim">Formula: (Total Savings / Total Income) &times; 100</span>
            </div>
            <div class="col-md-4 text-center">
                <span class="text-muted small text-uppercase fw-semibold">Financial Health Status</span>
                <?php if ($savingsRate >= 20): ?>
                    <h3 class="h4 text-success mt-2 mb-0"><i class="fa-solid fa-circle-check me-1"></i> Excellent</h3>
                    <span class="small text-muted">Exceeds the 20% benchmark</span>
                <?php elseif ($savingsRate > 0): ?>
                    <h3 class="h4 text-warning mt-2 mb-0"><i class="fa-solid fa-triangle-exclamation me-1"></i> Moderate</h3>
                    <span class="small text-muted">Aim for a 20%+ savings rate</span>
                <?php else: ?>
                    <h3 class="h4 text-danger mt-2 mb-0"><i class="fa-solid fa-circle-xmark me-1"></i> Deficit / Neutral</h3>
                    <span class="small text-muted">Expenses meet or exceed income</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Income Analysis Breakdown -->
        <div class="col-lg-6">
            <div class="codex-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="h6 font-heading text-light mb-0"><i class="fa-solid fa-arrow-down-left me-2 text-success"></i>Income Breakdown</h5>
                    <span class="badge badge-income">Total: <?= formatCurrency($totalIncome) ?></span>
                </div>

                <div class="table-responsive">
                    <table class="table table-codex mb-0">
                        <thead>
                            <tr>
                                <th>Source Category</th>
                                <th>Entries</th>
                                <th>Amount</th>
                                <th>Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($incomeSources)): ?>
                                <tr><td colspan="4" class="text-muted text-center py-4">No income entries found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($incomeSources as $is): 
                                    $pct = ($totalIncome > 0) ? round(($is['total_amount'] / $totalIncome) * 100, 1) : 0;
                                ?>
                                    <tr>
                                        <td class="fw-medium text-light"><?= sanitize($is['name']) ?></td>
                                        <td class="text-secondary small"><?= $is['count'] ?></td>
                                        <td class="fw-bold text-success"><?= formatCurrency($is['total_amount']) ?></td>
                                        <td>
                                            <span class="small text-muted"><?= $pct ?>%</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Expense Analysis Breakdown -->
        <div class="col-lg-6">
            <div class="codex-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="h6 font-heading text-light mb-0"><i class="fa-solid fa-arrow-up-right me-2 text-danger"></i>Expense Category Analysis</h5>
                    <span class="badge badge-expense">Total: <?= formatCurrency($totalExpense) ?></span>
                </div>

                <div class="table-responsive">
                    <table class="table table-codex mb-0">
                        <thead>
                            <tr>
                                <th>Expense Category</th>
                                <th>Entries</th>
                                <th>Amount</th>
                                <th>Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($expenseSources)): ?>
                                <tr><td colspan="4" class="text-muted text-center py-4">No expense entries found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($expenseSources as $es): 
                                    $pct = ($totalExpense > 0) ? round(($es['total_amount'] / $totalExpense) * 100, 1) : 0;
                                ?>
                                    <tr>
                                        <td class="fw-medium text-light"><?= sanitize($es['name']) ?></td>
                                        <td class="text-secondary small"><?= $es['count'] ?></td>
                                        <td class="fw-bold text-danger"><?= formatCurrency($es['total_amount']) ?></td>
                                        <td>
                                            <span class="small text-muted"><?= $pct ?>%</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
