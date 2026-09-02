<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];
$goalId = (int)($_GET['id'] ?? 0);
$error = '';

$goal = null;
if ($goalId > 0) {
    $stmt = $db->prepare("SELECT * FROM financial_goals WHERE id = :id AND user_id = :uid");
    $stmt->execute([':id' => $goalId, ':uid' => $userId]);
    $goal = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $targetAmount = (float)($_POST['target_amount'] ?? 0);
    $currentAmount = (float)($_POST['current_amount'] ?? 0);
    $targetDate = $_POST['target_date'] ?? date('Y-m-d', strtotime('+3 months'));
    $description = sanitize($_POST['description'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCSRFToken($csrfToken)) {
        $error = "CSRF verification failed.";
    } elseif (empty($title)) {
        $error = "Please provide a goal title.";
    } elseif ($targetAmount <= 0) {
        $error = "Target amount must be a positive number.";
    } elseif (empty($targetDate)) {
        $error = "Target date is required.";
    } else {
        try {
            if ($currentAmount >= $targetAmount) {
                $status = 'completed';
            }

            if ($goal) {
                $upStmt = $db->prepare("
                    UPDATE financial_goals 
                    SET title = :t, target_amount = :ta, current_amount = :ca, target_date = :td, description = :d, status = :s 
                    WHERE id = :id AND user_id = :uid
                ");
                $upStmt->execute([
                    ':t' => $title, ':ta' => $targetAmount, ':ca' => $currentAmount,
                    ':td' => $targetDate, ':d' => $description, ':s' => $status,
                    ':id' => $goalId, ':uid' => $userId
                ]);
                setFlash('success', 'Savings goal updated successfully.');
            } else {
                $insStmt = $db->prepare("
                    INSERT INTO financial_goals (user_id, title, target_amount, current_amount, target_date, description, status) 
                    VALUES (:uid, :t, :ta, :ca, :td, :d, :s)
                ");
                $insStmt->execute([
                    ':uid' => $userId, ':t' => $title, ':ta' => $targetAmount,
                    ':ca' => $currentAmount, ':td' => $targetDate, ':d' => $description, ':s' => $status
                ]);
                setFlash('success', 'New savings goal established.');
            }
            logActivity("Configured financial goal '{$title}'");
            header('Location: goals.php');
            exit;
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

$pageTitle = "Create Savings Goal - CODE X";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="max-w-xl mx-auto">
        <div class="codex-card p-4 p-md-5">
            <h2 class="h4 font-heading text-light mb-4"><?= $goal ? 'Edit Financial Goal' : 'Create Financial Savings Goal' ?></h2>

            <?php if ($error): ?>
                <div class="alert alert-danger bg-dark border-danger text-danger p-3 rounded mb-4 small">
                    <i class="fa-solid fa-circle-exclamation me-2"></i><?= sanitize($error) ?>
                </div>
            <?php endif; ?>

            <form action="create_goal.php<?= $goalId ? '?id='.$goalId : '' ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <div class="mb-3">
                    <label class="form-label text-light small fw-medium">Goal Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g., Buy a Workstation Laptop, Emergency Reserve" value="<?= $goal ? sanitize($goal['title']) : '' ?>" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label text-light small fw-medium">Target Amount ($)</label>
                        <input type="number" step="0.01" min="1" name="target_amount" class="form-control" placeholder="1000.00" value="<?= $goal ? htmlspecialchars($goal['target_amount']) : '' ?>" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-light small fw-medium">Current Amount Saved ($)</label>
                        <input type="number" step="0.01" min="0" name="current_amount" class="form-control" placeholder="0.00" value="<?= $goal ? htmlspecialchars($goal['current_amount']) : '0.00' ?>">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label text-light small fw-medium">Target Date</label>
                        <input type="date" name="target_date" class="form-control" value="<?= $goal ? htmlspecialchars($goal['target_date']) : date('Y-m-d', strtotime('+3 months')) ?>" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-light small fw-medium">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($goal && $goal['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="completed" <?= ($goal && $goal['status'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= ($goal && $goal['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-light small fw-medium">Description (Optional)</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Why are you saving for this goal?"><?= $goal ? sanitize($goal['description']) : '' ?></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="goals.php" class="btn btn-codex-outline"><i class="fa-solid fa-arrow-left me-1"></i> Cancel</a>
                    <button type="submit" class="btn btn-codex-primary px-4 py-2">
                        <i class="fa-solid fa-check me-1"></i> Save Goal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
