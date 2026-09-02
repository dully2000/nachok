<?php
$pageTitle = "Financial Reports - CODE X";
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];

// Filter options: daily, weekly, monthly, custom
$range = sanitize($_GET['range'] ?? 'monthly');
$startDate = sanitize($_GET['start_date'] ?? '');
$endDate = sanitize($_GET['end_date'] ?? '');

$today = date('Y-m-d');

if ($range === 'daily') {
    $startDate = $today;
    $endDate = $today;
} elseif ($range === 'weekly') {
    $startDate = date('Y-m-d', strtotime('-7 days'));
    $endDate = $today;
} elseif ($range === 'monthly') {
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-t');
}

$sql = "
    SELECT t.*, c.name as category_name
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = :uid
";
$params = [':uid' => $userId];

if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND t.transaction_date BETWEEN :sdate AND :edate";
    $params[':sdate'] = $startDate;
    $params[':edate'] = $endDate;
}

$sql .= " ORDER BY t.transaction_date DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$reportTransactions = $stmt->fetchAll();

// Aggregations
$repIncome = 0;
$repExpense = 0;
foreach ($reportTransactions as $rt) {
    if ($rt['transaction_type'] === 'income') $repIncome += (float)$rt['amount'];
    else $repExpense += (float)$rt['amount'];
}
$repNet = $repIncome - $repExpense;
?>

<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 font-heading text-light mb-1">Financial Report Generator</h1>
            <p class="small text-secondary mb-0">Generate summary reports for daily, weekly, monthly, or custom date ranges</p>
        </div>
        <div>
            <a href="export_report.php?range=<?= $range ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-success"><i class="fa-solid fa-download me-1"></i> Export CSV Report</a>
        </div>
    </div>

    <!-- Range Selector Form -->
    <div class="codex-card p-4 mb-4">
        <form action="monthly_report.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-light small">Predefined Period</label>
                <select name="range" class="form-select" onchange="this.form.submit()">
                    <option value="daily" <?= $range === 'daily' ? 'selected' : '' ?>>Today (Daily)</option>
                    <option value="weekly" <?= $range === 'weekly' ? 'selected' : '' ?>>Last 7 Days (Weekly)</option>
                    <option value="monthly" <?= $range === 'monthly' ? 'selected' : '' ?>>This Month (Monthly)</option>
                    <option value="custom" <?= $range === 'custom' ? 'selected' : '' ?>>Custom Date Range</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label text-light small">From Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= sanitize($startDate) ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label text-light small">To Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= sanitize($endDate) ?>">
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-codex-primary w-100"><i class="fa-solid fa-magnifying-glass me-1"></i> Generate Report</button>
            </div>
        </form>
    </div>

    <!-- Report Summary Banner -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="codex-card p-3 border-success border-opacity-25">
                <span class="text-muted small text-uppercase fw-semibold">Period Total Income</span>
                <h3 class="h4 font-heading text-success mt-1 mb-0"><?= formatCurrency($repIncome) ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="codex-card p-3 border-danger border-opacity-25">
                <span class="text-muted small text-uppercase fw-semibold">Period Total Expenses</span>
                <h3 class="h4 font-heading text-danger mt-1 mb-0"><?= formatCurrency($repExpense) ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="codex-card p-3 border-info border-opacity-25">
                <span class="text-muted small text-uppercase fw-semibold">Period Net Balance</span>
                <h3 class="h4 font-heading <?= $repNet >= 0 ? 'text-info' : 'text-danger' ?> mt-1 mb-0"><?= formatCurrency($repNet) ?></h3>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="codex-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-codex mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportTransactions)): ?>
                        <tr><td colspan="5" class="text-muted text-center py-4">No records found for the selected date range.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reportTransactions as $rt): ?>
                            <tr>
                                <td class="small text-secondary"><?= formatDate($rt['transaction_date']) ?></td>
                                <td>
                                    <span class="badge <?= $rt['transaction_type'] === 'income' ? 'badge-income' : 'badge-expense' ?>">
                                        <?= ucfirst($rt['transaction_type']) ?>
                                    </span>
                                </td>
                                <td class="fw-medium text-light"><?= sanitize($rt['category_name']) ?></td>
                                <td class="small text-secondary"><?= sanitize($rt['description'] ?: 'N/A') ?></td>
                                <td class="text-end fw-bold <?= $rt['transaction_type'] === 'income' ? 'text-success' : 'text-danger' ?>">
                                    <?= formatCurrency($rt['amount']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
