<?php
$pageTitle = "CODE X - Understand Your Money. Control Your Future.";
$pageDescription = "Code X is an AI-powered personal financial management and intelligent guidance platform. Track income, expenses, budgets, savings goals, and receive smart AI insights.";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="py-5 my-lg-4 position-relative">
    <div class="container py-lg-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-primary bg-opacity-10 border border-primary border-opacity-25 text-primary mb-3">
                    <i class="fa-solid fa-sparkles"></i>
                    <span class="small fw-semibold">Next-Gen Financial Intelligence (2027 IT Project)</span>
                </div>
                <h1 class="display-3 fw-bold mb-3 font-heading lh-sm">
                    <span class="brand-name">CODE X</span>
                </h1>
                <h2 class="h3 text-light fw-medium mb-4">
                    "Understand Your Money. Control Your Future."
                </h2>
                <p class="lead text-secondary mb-4 col-lg-10">
                    Take full control of your personal finances with automated transaction tracking, category-based budgeting, smart savings goals, and personalized financial insights powered by <strong>Code X AI</strong>.
                </p>

                <div class="d-flex flex-wrap gap-3 mb-5">
                    <?php if (isLoggedIn()): ?>
                        <a href="<?= getBaseUrl() ?>dashboard/dashboard.php" class="btn btn-codex-primary btn-lg px-4 py-3">
                            <i class="fa-solid fa-gauge-high me-2"></i>Go to Your Dashboard
                        </a>
                        <a href="<?= getBaseUrl() ?>ai/financial_assistant.php" class="btn btn-codex-outline btn-lg px-4 py-3">
                            <i class="fa-solid fa-robot me-2 text-info"></i>Ask Code X AI
                        </a>
                    <?php else: ?>
                        <a href="<?= getBaseUrl() ?>auth/register.php" class="btn btn-codex-primary btn-lg px-4 py-3">
                            <i class="fa-solid fa-user-plus me-2"></i>Get Started Free
                        </a>
                        <a href="<?= getBaseUrl() ?>auth/login.php" class="btn btn-codex-outline btn-lg px-4 py-3">
                            <i class="fa-solid fa-right-to-bracket me-2 text-primary"></i>Login to Account
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Stats summary -->
                <div class="row g-3 pt-3 border-top border-secondary border-opacity-25">
                    <div class="col-4">
                        <h4 class="h3 fw-bold text-light mb-0 font-heading">100%</h4>
                        <p class="small text-muted mb-0">Data Privacy</p>
                    </div>
                    <div class="col-4">
                        <h4 class="h3 fw-bold text-light mb-0 font-heading">Real-Time</h4>
                        <p class="small text-muted mb-0">AI Analytics</p>
                    </div>
                    <div class="col-4">
                        <h4 class="h3 fw-bold text-light mb-0 font-heading">Smart</h4>
                        <p class="small text-muted mb-0">Budget Warnings</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="codex-card p-4 p-md-5 text-center position-relative">
                    <div class="stat-icon bg-primary bg-opacity-20 text-primary mx-auto mb-4" style="width: 80px; height: 80px; font-size: 2.2rem;">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <h3 class="h4 text-light font-heading mb-2">Code X AI Assistant</h3>
                    <p class="small text-secondary mb-4">
                        Contextual financial advice calculated from your actual transactions, budgets, and savings goals.
                    </p>
                    <div class="bg-dark p-3 rounded-3 text-start mb-4 border border-secondary border-opacity-25">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success bg-opacity-20 text-success me-2">Insight</span>
                            <span class="small text-muted">Budget Performance</span>
                        </div>
                        <p class="small text-light mb-0">
                            "You have used 85% of your monthly Food budget. Transportation spending is down 12% compared to last month."
                        </p>
                    </div>
                    <a href="<?= getBaseUrl() ?>auth/register.php" class="btn btn-sm btn-codex-outline w-100">Try Interactive AI Assistant</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5 bg-dark bg-opacity-50">
    <div class="container py-lg-4">
        <div class="text-center max-w-2xl mx-auto mb-5">
            <h2 class="h1 font-heading text-light mb-3">Intelligent Features for Smart Financial Growth</h2>
            <p class="text-secondary">Designed with modern web technology and automated data analytics to give you absolute financial clarity.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="codex-card p-4 h-100">
                    <div class="stat-icon stat-icon-balance mb-3">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <h4 class="h5 text-light font-heading mb-2">Transaction Management</h4>
                    <p class="text-secondary small mb-0">
                        Add, edit, search, and filter income and expenses effortlessly. Categorize every transaction to analyze where your money flows.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="codex-card p-4 h-100">
                    <div class="stat-icon stat-icon-expense mb-3">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <h4 class="h5 text-light font-heading mb-2">Monthly Budget Control</h4>
                    <p class="text-secondary small mb-0">
                        Set spending limits per category. Receive visual progress indicators and instant warning alerts when approaching or exceeding limits.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="codex-card p-4 h-100">
                    <div class="stat-icon stat-icon-income mb-3">
                        <i class="fa-solid fa-bullseye"></i>
                    </div>
                    <h4 class="h5 text-light font-heading mb-2">Financial Savings Goals</h4>
                    <p class="text-secondary small mb-0">
                        Define targets for emergency funds, tech purchases, or tuition. Monitor real-time progress percentages and target dates.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="codex-card p-4 h-100">
                    <div class="stat-icon stat-icon-savings mb-3">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h4 class="h5 text-light font-heading mb-2">Visual Chart Analytics</h4>
                    <p class="text-secondary small mb-0">
                        Explore interactive Chart.js visualizations including Category Expense Distribution, Income vs Expense comparison, and Balance Trends.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="codex-card p-4 h-100">
                    <div class="stat-icon bg-info bg-opacity-15 text-info mb-3">
                        <i class="fa-solid fa-brain"></i>
                    </div>
                    <h4 class="h5 text-light font-heading mb-2">Code X AI Assistant</h4>
                    <p class="text-secondary small mb-0">
                        Ask questions directly about your money. Code X AI evaluates your real database context to deliver structured 5-part educational guidance.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="codex-card p-4 h-100">
                    <div class="stat-icon bg-purple bg-opacity-15 text-purple mb-3">
                        <i class="fa-solid fa-file-export"></i>
                    </div>
                    <h4 class="h5 text-light font-heading mb-2">Reports & CSV Export</h4>
                    <p class="text-secondary small mb-0">
                        Generate daily, weekly, monthly, and custom date range financial summaries with instant CSV spreadsheet export functionality.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5">
    <div class="container py-lg-4">
        <div class="text-center mb-5">
            <h2 class="h1 font-heading text-light mb-3">How Code X Works</h2>
            <p class="text-secondary">Four simple steps to total financial control.</p>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="p-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow" style="width: 60px; height: 60px; font-size: 1.5rem;">1</div>
                    <h5 class="text-light font-heading">Register Account</h5>
                    <p class="small text-secondary">Create your private user account with secure password encryption.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow" style="width: 60px; height: 60px; font-size: 1.5rem;">2</div>
                    <h5 class="text-light font-heading">Record Transactions</h5>
                    <p class="small text-secondary">Input your income sources and daily expense transactions in custom categories.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow" style="width: 60px; height: 60px; font-size: 1.5rem;">3</div>
                    <h5 class="text-light font-heading">Set Budgets & Goals</h5>
                    <p class="small text-secondary">Establish monthly budget thresholds and savings goals to track your discipline.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow" style="width: 60px; height: 60px; font-size: 1.5rem;">4</div>
                    <h5 class="text-light font-heading">Get AI Guidance</h5>
                    <p class="small text-secondary">Engage with Code X AI for practical, automated educational insights.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Financial Disclaimer Section -->
<section class="py-4">
    <div class="container">
        <div class="disclaimer-banner p-4 rounded-4 shadow">
            <div class="d-flex align-items-start gap-3">
                <i class="fa-solid fa-shield-halved fs-3 mt-1 text-warning"></i>
                <div>
                    <h5 class="text-warning font-heading mb-1">Important Financial Educational Disclaimer</h5>
                    <p class="mb-0 small text-warning opacity-90">
                        Code X provides <strong>educational financial guidance only</strong>. It is designed to assist users in budgeting, expense tracking, and understanding personal spending patterns. Code X does NOT provide professional financial, investment, tax, banking, or legal advice. Users are encouraged to consult certified professional advisors for individual legal or financial decisions.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
