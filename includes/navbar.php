<?php
$baseUrl = getBaseUrl();
$currentScript = basename($_SERVER['SCRIPT_NAME']);
?>
<nav class="navbar navbar-expand-lg navbar-codex sticky-top py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= $baseUrl ?>index.php">
            <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center me-2 shadow" style="width: 38px; height: 38px;">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <span class="brand-name fs-4">CODE X</span>
                <span class="brand-badge ms-1">v2.0</span>
            </div>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#codexNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="codexNavbar">
            <ul class="navbar-nav me-auto ms-lg-4 mb-2 mb-lg-0">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentScript == 'dashboard.php' ? 'active' : '' ?>" href="<?= $baseUrl ?>dashboard/dashboard.php">
                            <i class="fa-solid fa-gauge-high me-1 text-primary"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentScript == 'transactions.php' ? 'active' : '' ?>" href="<?= $baseUrl ?>transactions/transactions.php">
                            <i class="fa-solid fa-receipt me-1 text-success"></i> Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentScript == 'budgets.php' ? 'active' : '' ?>" href="<?= $baseUrl ?>budgets/budgets.php">
                            <i class="fa-solid fa-wallet me-1 text-warning"></i> Budgets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentScript == 'goals.php' ? 'active' : '' ?>" href="<?= $baseUrl ?>goals/goals.php">
                            <i class="fa-solid fa-bullseye me-1 text-info"></i> Savings Goals
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-chart-pie me-1 text-purple"></i> Reports
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark border-secondary">
                            <li><a class="dropdown-item" href="<?= $baseUrl ?>reports/spending_analysis.php"><i class="fa-solid fa-magnifying-glass-chart me-2"></i> Spending Analysis</a></li>
                            <li><a class="dropdown-item" href="<?= $baseUrl ?>reports/monthly_report.php"><i class="fa-solid fa-file-invoice-dollar me-2"></i> Monthly Reports</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-info fw-semibold <?= $currentScript == 'financial_assistant.php' ? 'active' : '' ?>" href="<?= $baseUrl ?>ai/financial_assistant.php">
                            <i class="fa-solid fa-robot me-1"></i> Code X AI
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link text-warning fw-semibold dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-user-shield me-1"></i> Admin Panel
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark border-secondary">
                                <li><a class="dropdown-item" href="<?= $baseUrl ?>admin/dashboard.php"><i class="fa-solid fa-chart-column me-2"></i> System Statistics</a></li>
                                <li><a class="dropdown-item" href="<?= $baseUrl ?>admin/users.php"><i class="fa-solid fa-users me-2"></i> Manage Users</a></li>
                                <li><a class="dropdown-item" href="<?= $baseUrl ?>admin/categories.php"><i class="fa-solid fa-tags me-2"></i> Manage Categories</a></li>
                                <li><a class="dropdown-item" href="<?= $baseUrl ?>admin/logs.php"><i class="fa-solid fa-list-check me-2"></i> Activity Logs</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link <?= $currentScript == 'index.php' ? 'active' : '' ?>" href="<?= $baseUrl ?>index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>index.php#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>contact.php">Contact</a></li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if (isLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="btn btn-codex-outline dropdown-toggle px-3 py-2" type="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-circle-user me-1 text-primary"></i> <?= sanitize($_SESSION['user_name'] ?? 'User') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark border-secondary shadow">
                            <li><span class="dropdown-header text-uppercase fs-7 text-secondary">Role: <?= sanitize($_SESSION['user_role'] ?? 'User') ?></span></li>
                            <li><a class="dropdown-item" href="<?= $baseUrl ?>auth/profile.php"><i class="fa-solid fa-user-gear me-2"></i> My Profile</a></li>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>auth/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Log Out</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>auth/login.php" class="btn btn-codex-outline me-2">Log In</a>
                    <a href="<?= $baseUrl ?>auth/register.php" class="btn btn-codex-primary">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
