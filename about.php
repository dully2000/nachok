<?php
$pageTitle = "About CODE X - Intelligent Financial Guidance";
$pageDescription = "Learn about Code X, an AI-powered personal financial management system engineered for clarity, budget discipline, and financial growth.";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row align-items-center mb-5">
        <div class="col-lg-8">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-primary bg-opacity-10 text-primary mb-3">
                <i class="fa-solid fa-graduation-cap"></i>
                <span class="small fw-semibold">2027 Final Year Information Technology Project</span>
            </div>
            <h1 class="display-4 font-heading text-light mb-3">About <span class="brand-name">CODE X</span></h1>
            <p class="lead text-secondary">
                "Understand Your Money. Control Your Future."
            </p>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="codex-card p-4 p-md-5 h-100">
                <h3 class="h4 text-light font-heading mb-3"><i class="fa-solid fa-bullseye text-primary me-2"></i>Our Purpose</h3>
                <p class="text-secondary">
                    Personal financial management is often fragmented, confusing, or overly complex. <strong>Code X</strong> was created to bridge the gap between static transaction tracking and active intelligent financial guidance.
                </p>
                <p class="text-secondary">
                    By combining secure PDO PHP database tracking, interactive Chart.js visualizations, and an intelligent context-aware AI assistant (<strong>Code X AI</strong>), Code X empowers individuals to build healthy financial habits, control monthly overspending, and reach savings goals faster.
                </p>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="codex-card p-4 p-md-5 h-100">
                <h3 class="h4 text-light font-heading mb-3"><i class="fa-solid fa-layer-group text-info me-2"></i>System Architecture</h3>
                <ul class="list-unstyled text-secondary d-flex flex-column gap-3 mb-0">
                    <li class="d-flex align-items-start">
                        <i class="fa-solid fa-code text-primary me-3 mt-1"></i>
                        <div>
                            <strong class="text-light">Frontend Stack:</strong> HTML5, Vanilla CSS3, Bootstrap 5, FontAwesome 6, Chart.js Visualizations.
                        </div>
                    </li>
                    <li class="d-flex align-items-start">
                        <i class="fa-solid fa-server text-success me-3 mt-1"></i>
                        <div>
                            <strong class="text-light">Backend Technology:</strong> PHP 8 with PDO prepared statements, Secure Sessions, and Role-Based Access Guards.
                        </div>
                    </li>
                    <li class="d-flex align-items-start">
                        <i class="fa-solid fa-database text-warning me-3 mt-1"></i>
                        <div>
                            <strong class="text-light">Database System:</strong> MySQL (XAMPP / phpMyAdmin) with relational tables (`users`, `categories`, `transactions`, `budgets`, `financial_goals`, `ai_conversations`).
                        </div>
                    </li>
                    <li class="d-flex align-items-start">
                        <i class="fa-solid fa-brain text-info me-3 mt-1"></i>
                        <div>
                            <strong class="text-light">AI Intelligence Layer:</strong> Contextual financial evaluation engine generating structured 5-part educational insight reports.
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
