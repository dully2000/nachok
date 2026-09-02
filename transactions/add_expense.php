<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)($_POST['amount'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $transactionDate = $_POST['transaction_date'] ?? date('Y-m-d');
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCSRFToken($csrfToken)) {
        $error = "CSRF security check failed.";
    } elseif ($amount <= 0) {
        $error = "Please enter a valid positive expense amount.";
    } elseif ($categoryId <= 0) {
        $error = "Please select an expense category.";
    } elseif (empty($transactionDate)) {
        $error = "Please choose a transaction date.";
    } else {
        try {
            $stmt = $db->prepare("
                INSERT INTO transactions (user_id, category_id, transaction_type, amount, description, transaction_date)
                VALUES (:uid, :cat, 'expense', :amt, :desc, :tdate)
            ");
            $stmt->execute([
                ':uid' => $userId,
                ':cat' => $categoryId,
                ':amt' => $amount,
                ':desc' => $description,
                ':tdate' => $transactionDate
            ]);

            logActivity("Added Expense: " . formatCurrency($amount));
            setFlash('success', 'Expense transaction recorded successfully.');
            header('Location: transactions.php');
            exit;
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

// Fetch Expense Categories
$catStmt = $db->prepare("SELECT * FROM categories WHERE (user_id IS NULL OR user_id = :uid) AND type = 'expense' ORDER BY name ASC");
$catStmt->execute([':uid' => $userId]);
$categories = $catStmt->fetchAll();

$pageTitle = "Add Expense - CODE X";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="max-w-xl mx-auto">
        <div class="codex-card p-4 p-md-5">
            <div class="d-flex align-items-center mb-4">
                <div class="stat-icon stat-icon-expense me-3">
                    <i class="fa-solid fa-arrow-up-right"></i>
                </div>
                <div>
                    <h2 class="h4 font-heading text-light mb-0">Record Expense</h2>
                    <p class="small text-secondary mb-0">Track food, transport, rent, utilities, or entertainment costs</p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger bg-dark border-danger text-danger p-3 rounded mb-4 small">
                    <i class="fa-solid fa-circle-exclamation me-2"></i><?= sanitize($error) ?>
                </div>
            <?php endif; ?>

            <form action="add_expense.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <div class="mb-3">
                    <label class="form-label text-light small fw-medium">Amount ($)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-danger fw-bold">$</span>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control form-control-lg text-danger fw-bold" placeholder="0.00" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-medium">Expense Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= sanitize($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-medium">Transaction Date</label>
                    <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label text-light small fw-medium">Description / Notes (Optional)</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="e.g., Weekly supermarket shopping or Electricity bill"></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="transactions.php" class="btn btn-codex-outline"><i class="fa-solid fa-arrow-left me-1"></i> Cancel</a>
                    <button type="submit" class="btn btn-danger px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i> Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
