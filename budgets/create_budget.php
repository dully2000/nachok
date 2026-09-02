<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];
$budgetId = (int)($_GET['id'] ?? 0);
$error = '';

$budget = null;
if ($budgetId > 0) {
    $stmt = $db->prepare("SELECT * FROM budgets WHERE id = :id AND user_id = :uid");
    $stmt->execute([':id' => $budgetId, ':uid' => $userId]);
    $budget = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $month = (int)($_POST['month'] ?? date('m'));
    $year = (int)($_POST['year'] ?? date('Y'));
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCSRFToken($csrfToken)) {
        $error = "CSRF security check failed.";
    } elseif ($categoryId <= 0) {
        $error = "Please select an expense category.";
    } elseif ($amount <= 0) {
        $error = "Budget amount must be greater than zero.";
    } else {
        try {
            // Upsert (Insert or Update if budget for category/month/year already exists)
            $checkStmt = $db->prepare("SELECT id FROM budgets WHERE user_id = :uid AND category_id = :cat AND month = :m AND year = :y AND id != :id");
            $checkStmt->execute([':uid' => $userId, ':cat' => $categoryId, ':m' => $month, ':y' => $year, ':id' => $budgetId]);
            
            if ($checkStmt->fetch()) {
                $error = "A budget limit for this category in the selected month already exists.";
            } else {
                if ($budget) {
                    $upStmt = $db->prepare("UPDATE budgets SET category_id = :cat, amount = :amt, month = :m, year = :y WHERE id = :id AND user_id = :uid");
                    $upStmt->execute([':cat' => $categoryId, ':amt' => $amount, ':m' => $month, ':y' => $year, ':id' => $budgetId, ':uid' => $userId]);
                    setFlash('success', 'Budget allocation updated successfully.');
                } else {
                    $insStmt = $db->prepare("INSERT INTO budgets (user_id, category_id, amount, month, year) VALUES (:uid, :cat, :amt, :m, :y)");
                    $insStmt->execute([':uid' => $userId, ':cat' => $categoryId, ':amt' => $amount, ':m' => $month, ':y' => $year]);
                    setFlash('success', 'New monthly budget created successfully.');
                }
                logActivity("Configured budget allocation for category ID #{$categoryId}");
                header('Location: budgets.php?month=' . $month . '&year=' . $year);
                exit;
            }
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

// Fetch Expense categories
$catStmt = $db->prepare("SELECT * FROM categories WHERE (user_id IS NULL OR user_id = :uid) AND type = 'expense' ORDER BY name ASC");
$catStmt->execute([':uid' => $userId]);
$categories = $catStmt->fetchAll();

$pageTitle = "Set Budget - CODE X";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="max-w-xl mx-auto">
        <div class="codex-card p-4 p-md-5">
            <h2 class="h4 font-heading text-light mb-4"><?= $budget ? 'Edit Category Budget' : 'Set Category Budget' ?></h2>

            <?php if ($error): ?>
                <div class="alert alert-danger bg-dark border-danger text-danger p-3 rounded mb-4 small">
                    <i class="fa-solid fa-circle-exclamation me-2"></i><?= sanitize($error) ?>
                </div>
            <?php endif; ?>

            <form action="create_budget.php<?= $budgetId ? '?id='.$budgetId : '' ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <div class="mb-3">
                    <label class="form-label text-light small fw-medium">Expense Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($budget && $budget['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= sanitize($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-medium">Monthly Target Budget ($)</label>
                    <input type="number" step="0.01" min="1" name="amount" class="form-control" placeholder="e.g. 500.00" value="<?= $budget ? htmlspecialchars($budget['amount']) : '' ?>" required>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <label class="form-label text-light small fw-medium">Month</label>
                        <select name="month" class="form-select">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= ($budget ? $budget['month'] : date('m')) == $m ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-light small fw-medium">Year</label>
                        <select name="year" class="form-select">
                            <?php for ($y = date('Y'); $y <= date('Y') + 2; $y++): ?>
                                <option value="<?= $y ?>" <?= ($budget ? $budget['year'] : date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="budgets.php" class="btn btn-codex-outline"><i class="fa-solid fa-arrow-left me-1"></i> Cancel</a>
                    <button type="submit" class="btn btn-codex-primary px-4 py-2">
                        <i class="fa-solid fa-check me-1"></i> Save Budget
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
