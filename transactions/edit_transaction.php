<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];
$txId = (int)($_GET['id'] ?? 0);
$error = '';

// Ownership Authorization Check
$stmt = $db->prepare("SELECT * FROM transactions WHERE id = :id AND user_id = :uid");
$stmt->execute([':id' => $txId, ':uid' => $userId]);
$transaction = $stmt->fetch();

if (!$transaction) {
    setFlash('danger', 'Transaction not found or access denied.');
    header('Location: transactions.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)($_POST['amount'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $transactionDate = $_POST['transaction_date'] ?? $transaction['transaction_date'];
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCSRFToken($csrfToken)) {
        $error = "CSRF verification failed.";
    } elseif ($amount <= 0) {
        $error = "Amount must be a positive number.";
    } elseif ($categoryId <= 0) {
        $error = "Please select a category.";
    } else {
        try {
            $upStmt = $db->prepare("
                UPDATE transactions 
                SET category_id = :cat, amount = :amt, description = :desc, transaction_date = :tdate 
                WHERE id = :id AND user_id = :uid
            ");
            $upStmt->execute([
                ':cat' => $categoryId,
                ':amt' => $amount,
                ':desc' => $description,
                ':tdate' => $transactionDate,
                ':id' => $txId,
                ':uid' => $userId
            ]);

            logActivity("Updated transaction ID #{$txId}");
            setFlash('success', 'Transaction updated successfully.');
            header('Location: transactions.php');
            exit;
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

// Fetch categories for this type
$catStmt = $db->prepare("SELECT * FROM categories WHERE (user_id IS NULL OR user_id = :uid) AND type = :type ORDER BY name ASC");
$catStmt->execute([':uid' => $userId, ':type' => $transaction['transaction_type']]);
$categories = $catStmt->fetchAll();

$pageTitle = "Edit Transaction - CODE X";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="max-w-xl mx-auto">
        <div class="codex-card p-4 p-md-5">
            <h2 class="h4 font-heading text-light mb-4">Edit <?= ucfirst($transaction['transaction_type']) ?> Entry</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger bg-dark border-danger text-danger p-3 rounded mb-4 small">
                    <i class="fa-solid fa-circle-exclamation me-2"></i><?= sanitize($error) ?>
                </div>
            <?php endif; ?>

            <form action="edit_transaction.php?id=<?= $txId ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <div class="mb-3">
                    <label class="form-label text-light small fw-medium">Amount ($)</label>
                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="<?= htmlspecialchars($transaction['amount']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-medium">Category</label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $transaction['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= sanitize($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-medium">Transaction Date</label>
                    <input type="date" name="transaction_date" class="form-control" value="<?= htmlspecialchars($transaction['transaction_date']) ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label text-light small fw-medium">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= sanitize($transaction['description']) ?></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="transactions.php" class="btn btn-codex-outline"><i class="fa-solid fa-arrow-left me-1"></i> Cancel</a>
                    <button type="submit" class="btn btn-codex-primary px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
