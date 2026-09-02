<?php
$pageTitle = "Dashboard - CODE X";
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];

// 1. Calculate Overall Total Income, Total Expenses, Current Balance
$totStmt = $db->prepare("
    SELECT 
        SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) AS total_income,
        SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) AS total_expenses
    FROM transactions 
    WHERE user_id = :uid
");
$totStmt->execute([':uid' => $userId]);
$totals = $totStmt->fetch();

$totalIncome = (float)($totals['total_income'] ?? 0);
$totalExpenses = (float)($totals['total_expenses'] ?? 0);
$currentBalance = $totalIncome - $totalExpenses;

// Savings Rate Formula: (Total Savings / Total Income) * 100
$totalSavings = max(0, $currentBalance);
$savingsRate = ($totalIncome > 0) ? round(($totalSavings / $totalIncome) * 100, 1) : 0;

// 2. Current Month Calculations
$currentMonth = (int)date('m');
$currentYear = (int)date('Y');

$mStmt = $db->prepare("
    SELECT 
        SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) AS m_income,
        SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) AS m_expenses
    FROM transactions 
    WHERE user_id = :uid AND MONTH(transaction_date) = :m AND YEAR(transaction_date) = :y
");
$mStmt->execute([':uid' => $userId, ':m' => $currentMonth, ':y' => $currentYear]);
$mTotals = $mStmt->fetch();

$monthlyIncome = (float)($mTotals['m_income'] ?? 0);
$monthlyExpenses = (float)($mTotals['m_expenses'] ?? 0);
$monthlySavings = $monthlyIncome - $monthlyExpenses;

// 3. Budgets & Over-budget alerts
$bStmt = $db->prepare("
    SELECT b.*, c.name as category_name,
        COALESCE((
            SELECT SUM(t.amount) 
            FROM transactions t 
            WHERE t.user_id = b.user_id 
              AND t.category_id = b.category_id 
              AND t.transaction_type = 'expense'
              AND MONTH(t.transaction_date) = b.month 
              AND YEAR(t.transaction_date) = b.year
        ), 0) as spent
    FROM budgets b
    JOIN categories c ON b.category_id = c.id
    WHERE b.user_id = :uid AND b.month = :m AND b.year = :y
");
$bStmt->execute([':uid' => $userId, ':m' => $currentMonth, ':y' => $currentYear]);
$budgets = $bStmt->fetchAll();

$totalBudgetsCount = count($budgets);
$overBudgetWarnings = [];
$nearBudgetWarnings = [];

foreach ($budgets as $b) {
    $pct = ($b['amount'] > 0) ? ($b['spent'] / $b['amount']) * 100 : 0;
    if ($pct >= 100) {
        $overBudgetWarnings[] = "Warning: You have exceeded your <strong>" . sanitize($b['category_name']) . "</strong> budget by " . formatCurrency($b['spent'] - $b['amount']) . "!";
    } elseif ($pct >= 80) {
        $nearBudgetWarnings[] = "Notice: You have used " . round($pct) . "% of your <strong>" . sanitize($b['category_name']) . "</strong> budget.";
    }
}

// 4. Financial Goals Progress
$gStmt = $db->prepare("
    SELECT 
        COUNT(*) as total_goals,
        SUM(target_amount) as total_target,
        SUM(current_amount) as total_saved
    FROM financial_goals
    WHERE user_id = :uid AND status = 'active'
");
$gStmt->execute([':uid' => $userId]);
$goalStats = $gStmt->fetch();
$activeGoalsCount = (int)($goalStats['total_goals'] ?? 0);
$goalTarget = (float)($goalStats['total_target'] ?? 0);
$goalSaved = (float)($goalStats['total_saved'] ?? 0);
$overallGoalProgress = ($goalTarget > 0) ? min(100, round(($goalSaved / $goalTarget) * 100, 1)) : 0;

// 5. Category Expense Distribution for Chart 1
$catStmt = $db->prepare("
    SELECT c.name, SUM(t.amount) as total_amount
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = :uid AND t.transaction_type = 'expense'
    GROUP BY c.id, c.name
    ORDER BY total_amount DESC
");
$catStmt->execute([':uid' => $userId]);
$categoryExpenses = $catStmt->fetchAll();

$catLabels = [];
$catData = [];
$topExpenseCategory = "None";
$topExpenseAmount = 0;

foreach ($categoryExpenses as $index => $ce) {
    $catLabels[] = $ce['name'];
    $catData[] = (float)$ce['total_amount'];
    if ($index === 0) {
        $topExpenseCategory = $ce['name'];
        $topExpenseAmount = (float)$ce['total_amount'];
    }
}

// 6. Monthly Income vs Expenses for Chart 2 (Last 6 Months)
$monthlyTrend = [];
for ($i = 5; $i >= 0; $i--) {
    $time = strtotime("-$i months");
    $m = (int)date('m', $time);
    $y = (int)date('Y', $time);
    $label = date('M Y', $time);

    $trStmt = $db->prepare("
        SELECT 
            SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) AS inc,
            SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) AS exp
        FROM transactions
        WHERE user_id = :uid AND MONTH(transaction_date) = :m AND YEAR(transaction_date) = :y
    ");
    $trStmt->execute([':uid' => $userId, ':m' => $m, ':y' => $y]);
    $res = $trStmt->fetch();

    $monthlyTrend[] = [
        'month' => $label,
        'income' => (float)($res['inc'] ?? 0),
        'expense' => (float)($res['exp'] ?? 0)
    ];
}

// 7. Dynamic Data-Driven AI Insights Generation
$aiInsights = [];
if ($totalIncome > 0) {
    $aiInsights[] = "Your current overall savings rate is <strong>{$savingsRate}%</strong> of total income.";
} else {
    $aiInsights[] = "Add your income sources to unlock savings rate analysis.";
}

if (!empty($topExpenseCategory) && $topExpenseCategory !== "None") {
    $aiInsights[] = "<strong>{$topExpenseCategory}</strong> is currently your largest spending category (" . formatCurrency($topExpenseAmount) . ").";
}

if (!empty($overBudgetWarnings)) {
    foreach ($overBudgetWarnings as $obw) {
        $aiInsights[] = $obw;
    }
} elseif (!empty($nearBudgetWarnings)) {
    foreach ($nearBudgetWarnings as $nbw) {
        $aiInsights[] = $nbw;
    }
}

if ($activeGoalsCount > 0) {
    $aiInsights[] = "You have saved " . formatCurrency($goalSaved) . " towards your active financial goals (<strong>{$overallGoalProgress}%</strong> achieved).";
}
?>

<div class="container py-4">
    <!-- Top Greeting Banner -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h1 class="h2 font-heading text-light mb-1">
                Welcome back, <?= sanitize($_SESSION['user_name']) ?>!
            </h1>
            <p class="text-secondary small mb-0">
                <i class="fa-solid fa-calendar-day me-1 text-primary"></i> <?= date('F j, Y') ?> &bull; Code X Financial Overview
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= getBaseUrl() ?>transactions/add_income.php" class="btn btn-sm btn-success">
                <i class="fa-solid fa-plus me-1"></i> Add Income
            </a>
            <a href="<?= getBaseUrl() ?>transactions/add_expense.php" class="btn btn-sm btn-danger">
                <i class="fa-solid fa-minus me-1"></i> Add Expense
            </a>
            <a href="<?= getBaseUrl() ?>ai/financial_assistant.php" class="btn btn-sm btn-info text-dark fw-semibold">
                <i class="fa-solid fa-robot me-1"></i> Ask AI Assistant
            </a>
        </div>
    </div>

    <!-- AI Insights Section -->
    <div class="codex-card p-4 mb-4 border-info border-opacity-25 bg-gradient">
        <div class="d-flex align-items-center mb-3">
            <div class="bg-info bg-opacity-20 text-info rounded-3 p-2 me-3">
                <i class="fa-solid fa-brain fs-4"></i>
            </div>
            <div>
                <h5 class="h6 font-heading text-light mb-0">Code X AI Automated Financial Insights</h5>
                <p class="small text-muted mb-0">Calculated directly from your actual transaction, budget, and goal data.</p>
            </div>
        </div>

        <div class="row g-3">
            <?php foreach ($aiInsights as $insight): ?>
                <div class="col-md-6">
                    <div class="p-3 bg-dark bg-opacity-50 rounded-3 border border-secondary border-opacity-25 text-light small d-flex align-items-start gap-2">
                        <i class="fa-solid fa-circle-info text-info mt-1"></i>
                        <div><?= $insight ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 7 Key Financial Metrics -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Income -->
        <div class="col-xl-3 col-md-6">
            <div class="codex-card stat-card stat-income h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Total Income</span>
                        <h3 class="h3 font-heading text-success mt-1 mb-0"><?= formatCurrency($totalIncome) ?></h3>
                    </div>
                    <div class="stat-icon stat-icon-income"><i class="fa-solid fa-arrow-down-left"></i></div>
                </div>
                <span class="small text-dim">Lifetime registered income</span>
            </div>
        </div>

        <!-- 2. Total Expenses -->
        <div class="col-xl-3 col-md-6">
            <div class="codex-card stat-card stat-expense h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Total Expenses</span>
                        <h3 class="h3 font-heading text-danger mt-1 mb-0"><?= formatCurrency($totalExpenses) ?></h3>
                    </div>
                    <div class="stat-icon stat-icon-expense"><i class="fa-solid fa-arrow-up-right"></i></div>
                </div>
                <span class="small text-dim">Lifetime total spent</span>
            </div>
        </div>

        <!-- 3. Current Balance -->
        <div class="col-xl-3 col-md-6">
            <div class="codex-card stat-card stat-balance h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Current Balance</span>
                        <h3 class="h3 font-heading <?= $currentBalance >= 0 ? 'text-primary' : 'text-danger' ?> mt-1 mb-0"><?= formatCurrency($currentBalance) ?></h3>
                    </div>
                    <div class="stat-icon stat-icon-balance"><i class="fa-solid fa-wallet"></i></div>
                </div>
                <span class="small text-dim">Net financial position</span>
            </div>
        </div>

        <!-- 4. Monthly Savings & Savings Rate -->
        <div class="col-xl-3 col-md-6">
            <div class="codex-card stat-card stat-savings h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Savings Rate</span>
                        <h3 class="h3 font-heading text-warning mt-1 mb-0"><?= $savingsRate ?>%</h3>
                    </div>
                    <div class="stat-icon stat-icon-savings"><i class="fa-solid fa-piggy-bank"></i></div>
                </div>
                <span class="small text-dim">Monthly Net: <?= formatCurrency($monthlySavings) ?></span>
            </div>
        </div>
    </div>

    <!-- Second Row Stats: Budget & Goals -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="codex-card p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-light fw-medium"><i class="fa-solid fa-sliders text-warning me-2"></i>Monthly Budget Status</span>
                    <span class="badge bg-warning bg-opacity-20 text-warning"><?= $totalBudgetsCount ?> Categories Configured</span>
                </div>
                <?php if (empty($budgets)): ?>
                    <p class="small text-muted mb-0">No budgets set for this month. <a href="<?= getBaseUrl() ?>budgets/create_budget.php" class="text-primary">Set a budget now</a>.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2 mt-2">
                        <?php foreach (array_slice($budgets, 0, 3) as $bg): 
                            $pct = ($bg['amount'] > 0) ? min(100, round(($bg['spent'] / $bg['amount']) * 100)) : 0;
                            $barClass = $pct >= 100 ? 'bg-danger' : ($pct >= 80 ? 'bg-warning' : 'bg-success');
                        ?>
                            <div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-secondary"><?= sanitize($bg['category_name']) ?></span>
                                    <span class="text-light"><?= formatCurrency($bg['spent']) ?> / <?= formatCurrency($bg['amount']) ?> (<?= $pct ?>%)</span>
                                </div>
                                <div class="progress-codex">
                                    <div class="progress-bar <?= $barClass ?>" role="progressbar" style="width: <?= $pct ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="codex-card p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-light fw-medium"><i class="fa-solid fa-bullseye text-info me-2"></i>Savings Goal Progress</span>
                    <span class="badge bg-info bg-opacity-20 text-info"><?= $overallGoalProgress ?>% Overall Target</span>
                </div>
                <div class="mb-2">
                    <div class="d-flex justify-content-between small text-secondary mb-1">
                        <span>Total Target: <?= formatCurrency($goalTarget) ?></span>
                        <span class="text-info fw-bold">Saved: <?= formatCurrency($goalSaved) ?></span>
                    </div>
                    <div class="progress-codex" style="height: 12px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $overallGoalProgress ?>%"></div>
                    </div>
                </div>
                <div class="text-end">
                    <a href="<?= getBaseUrl() ?>goals/goals.php" class="small text-primary text-decoration-none">Manage Financial Goals &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Chart.js Analytics Grid -->
    <div class="row g-4 mb-4">
        <!-- Chart 1: Expense Distribution -->
        <div class="col-lg-5">
            <div class="codex-card h-100">
                <div class="codex-card-header d-flex justify-content-between align-items-center">
                    <h5 class="h6 font-heading text-light mb-0"><i class="fa-solid fa-chart-pie me-2 text-danger"></i>Expense Distribution</h5>
                    <span class="small text-muted">By Category</span>
                </div>
                <div class="p-4 d-flex align-items-center justify-content-center" style="min-height: 320px;">
                    <?php if (empty($catData)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fa-solid fa-chart-pie fs-1 mb-2 opacity-25"></i>
                            <p class="small mb-0">No expense records found to generate chart.</p>
                        </div>
                    <?php else: ?>
                        <canvas id="expenseCategoryChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Chart 2: Monthly Income vs Expenses -->
        <div class="col-lg-7">
            <div class="codex-card h-100">
                <div class="codex-card-header d-flex justify-content-between align-items-center">
                    <h5 class="h6 font-heading text-light mb-0"><i class="fa-solid fa-chart-column me-2 text-primary"></i>Monthly Income vs Expenses</h5>
                    <span class="small text-muted">Past 6 Months</span>
                </div>
                <div class="p-4" style="min-height: 320px;">
                    <canvas id="incomeExpenseChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script Initialization -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Expense Distribution Doughnut Chart
    <?php if (!empty($catData)): ?>
    const ctxCat = document.getElementById('expenseCategoryChart');
    if (ctxCat) {
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($catLabels) ?>,
                datasets: [{
                    data: <?= json_encode($catData) ?>,
                    backgroundColor: [
                        '#ef4444', '#f97316', '#f59e0b', '#6366f1', '#ec4899', '#a855f7', '#14b8a6', '#94a3b8'
                    ],
                    borderWidth: 2,
                    borderColor: '#131b2e'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#94a3b8', font: { family: 'Inter', size: 11 } }
                    }
                }
            }
        });
    }
    <?php endif; ?>

    // 2. Income vs Expenses Bar Chart
    const ctxTrend = document.getElementById('incomeExpenseChart');
    if (ctxTrend) {
        const trendData = <?= json_encode($monthlyTrend) ?>;
        const labels = trendData.map(item => item.month);
        const incomes = trendData.map(item => item.income);
        const expenses = trendData.map(item => item.expense);

        new Chart(ctxTrend, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Income ($)',
                        data: incomes,
                        backgroundColor: '#10b981',
                        borderRadius: 6
                    },
                    {
                        label: 'Expense ($)',
                        data: expenses,
                        backgroundColor: '#ef4444',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: { color: '#94a3b8' },
                        grid: { color: 'rgba(255, 255, 255, 0.05)' }
                    },
                    y: {
                        ticks: { color: '#94a3b8' },
                        grid: { color: 'rgba(255, 255, 255, 0.05)' }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: '#f8fafc', font: { family: 'Inter' } }
                    }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
