<?php
$pageTitle = "Transactions - CODE X";
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];

// Filter Parameters
$typeFilter = sanitize($_GET['type'] ?? '');
$categoryFilter = (int)($_GET['category'] ?? 0);
$startDate = sanitize($_GET['start_date'] ?? '');
$endDate = sanitize($_GET['end_date'] ?? '');
$searchQuery = sanitize($_GET['search'] ?? '');

// Build Dynamic SQL Query
$sql = "
    SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = :uid
";

$params = [':uid' => $userId];

if (!empty($typeFilter)) {
    $sql .= " AND t.transaction_type = :type";
    $params[':type'] = $typeFilter;
}

if ($categoryFilter > 0) {
    $sql .= " AND t.category_id = :cat";
    $params[':cat'] = $categoryFilter;
}

if (!empty($startDate)) {
    $sql .= " AND t.transaction_date >= :sdate";
    $params[':sdate'] = $startDate;
}

if (!empty($endDate)) {
    $sql .= " AND t.transaction_date <= :edate";
    $params[':edate'] = $endDate;
}

if (!empty($searchQuery)) {
    $sql .= " AND (t.description LIKE :q OR c.name LIKE :q)";
    $params[':q'] = "%$searchQuery%";
}

$sql .= " ORDER BY t.transaction_date DESC, t.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Fetch Categories for Filter Dropdown
$catStmt = $db->prepare("SELECT id, name, type FROM categories WHERE user_id IS NULL OR user_id = :uid ORDER BY name ASC");
$catStmt->execute([':uid' => $userId]);
$categories = $catStmt->fetchAll();
?>

<div class="container py-4">
    <!-- Top Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 font-heading text-light mb-1">Transaction History</h1>
            <p class="small text-secondary mb-0">Record, filter, search, and analyze all income & expense entries</p>
        </div>
        <div class="d-flex gap-2">
            <a href="add_income.php" class="btn btn-success"><i class="fa-solid fa-plus me-1"></i> Add Income</a>
            <a href="add_expense.php" class="btn btn-danger"><i class="fa-solid fa-minus me-1"></i> Add Expense</a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="codex-card p-4 mb-4">
        <form action="transactions.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-light small">Search Description</label>
                <input type="text" name="search" class="form-control" placeholder="Search keywords..." value="<?= sanitize($searchQuery) ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label text-light small">Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="income" <?= $typeFilter === 'income' ? 'selected' : '' ?>>Income Only</option>
                    <option value="expense" <?= $typeFilter === 'expense' ? 'selected' : '' ?>>Expense Only</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label text-light small">Category</label>
                <select name="category" class="form-select">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                            [<?= ucfirst($cat['type']) ?>] <?= sanitize($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-light small">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= sanitize($startDate) ?>">
            </div>

            <div class="col-md-2">
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-codex-primary w-100"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                    <a href="transactions.php" class="btn btn-codex-outline" title="Reset Filters"><i class="fa-solid fa-rotate-left"></i></a>
                </div>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
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
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fa-solid fa-receipt fs-1 mb-2 opacity-25"></i>
                                <p class="mb-0">No transactions match your criteria.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td class="text-nowrap small text-secondary">
                                    <?= formatDate($tx['transaction_date']) ?>
                                </td>
                                <td>
                                    <?php if ($tx['transaction_type'] === 'income'): ?>
                                        <span class="badge badge-income"><i class="fa-solid fa-arrow-down me-1"></i> Income</span>
                                    <?php else: ?>
                                        <span class="badge badge-expense"><i class="fa-solid fa-arrow-up me-1"></i> Expense</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-medium text-light">
                                        <i class="fa-solid <?= sanitize($tx['category_icon'] ?? 'fa-tag') ?> me-1" style="color: <?= sanitize($tx['category_color'] ?? '#3b82f6') ?>"></i>
                                        <?= sanitize($tx['category_name']) ?>
                                    </span>
                                </td>
                                <td class="text-secondary small">
                                    <?= sanitize($tx['description'] ?: 'No notes added') ?>
                                </td>
                                <td class="text-end fw-bold text-nowrap <?= $tx['transaction_type'] === 'income' ? 'text-success' : 'text-danger' ?>">
                                    <?= $tx['transaction_type'] === 'income' ? '+' : '-' ?> <?= formatCurrency($tx['amount']) ?>
                                </td>
                                <td class="text-center text-nowrap">
                                    <a href="edit_transaction.php?id=<?= $tx['id'] ?>" class="btn btn-sm btn-outline-info me-1" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <a href="delete_transaction.php?id=<?= $tx['id'] ?>&csrf_token=<?= generateCSRFToken() ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" title="Delete"><i class="fa-solid fa-trash-can"></i></a>
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
