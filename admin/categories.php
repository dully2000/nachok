<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();

$db = getDBConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $type = sanitize($_POST['type'] ?? 'expense');
    $icon = sanitize($_POST['icon'] ?? 'fa-tag');
    $color = sanitize($_POST['color'] ?? '#3b82f6');
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCSRFToken($csrfToken)) {
        $error = "CSRF verification failed.";
    } elseif (empty($name)) {
        $error = "Category name is required.";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO categories (user_id, name, type, icon, color) VALUES (NULL, :name, :type, :icon, :color)");
            $stmt->execute([':name' => $name, ':type' => $type, ':icon' => $icon, ':color' => $color]);
            logActivity("Admin added new global category '{$name}'");
            setFlash('success', 'Global category added successfully.');
            header('Location: categories.php');
            exit;
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

// Fetch all categories
$categories = $db->query("SELECT * FROM categories ORDER BY type ASC, name ASC")->fetchAll();

$pageTitle = "Manage Categories - CODE X Admin";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 font-heading text-light mb-1"><i class="fa-solid fa-tags me-2 text-warning"></i>Global Category Governance</h1>
            <p class="small text-secondary mb-0">Add and inspect system default categories for income and expenses</p>
        </div>
        <a href="dashboard.php" class="btn btn-codex-outline"><i class="fa-solid fa-arrow-left me-1"></i> Admin Dashboard</a>
    </div>

    <div class="row g-4">
        <!-- Add Category Form -->
        <div class="col-lg-4">
            <div class="codex-card p-4">
                <h5 class="h6 font-heading text-light mb-3"><i class="fa-solid fa-plus-circle me-1 text-success"></i> Add Global Category</h5>

                <?php if ($error): ?>
                    <div class="alert alert-danger bg-dark border-danger text-danger p-2 rounded mb-3 small">
                        <?= sanitize($error) ?>
                    </div>
                <?php endif; ?>

                <form action="categories.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Travel & Shopping" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="expense">Expense Category</option>
                            <option value="income">Income Category</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">FontAwesome Icon Class</label>
                        <input type="text" name="icon" class="form-control" value="fa-tag" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-light small fw-medium">Color Badge (HEX)</label>
                        <input type="color" name="color" class="form-control form-control-color w-100" value="#3b82f6">
                    </div>

                    <button type="submit" class="btn btn-codex-primary w-100">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Create Category
                    </button>
                </form>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="col-lg-8">
            <div class="codex-card overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-codex mb-0">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Type</th>
                                <th>Icon</th>
                                <th>User Association</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td class="fw-bold text-light">
                                        <i class="fa-solid <?= sanitize($cat['icon']) ?> me-2" style="color: <?= sanitize($cat['color']) ?>"></i>
                                        <?= sanitize($cat['name']) ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $cat['type'] === 'income' ? 'badge-income' : 'badge-expense' ?>">
                                            <?= ucfirst($cat['type']) ?>
                                        </span>
                                    </td>
                                    <td class="small text-secondary"><code><?= sanitize($cat['icon']) ?></code></td>
                                    <td class="small text-muted">
                                        <?= $cat['user_id'] ? 'User #' . $cat['user_id'] : 'Global System Default' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
