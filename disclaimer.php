<?php
$pageTitle = "Financial Disclaimer - CODE X";
$pageDescription = "Official financial disclaimer for Code X AI Personal Financial Management System.";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="max-w-3xl mx-auto">
        <h1 class="display-5 font-heading text-light mb-4">Financial Disclaimer</h1>
        
        <div class="disclaimer-banner p-4 rounded-4 mb-4">
            <h4 class="text-warning font-heading mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>Educational Guidance Only</h4>
            <p class="mb-0 text-warning opacity-90">
                <?= FINANCIAL_DISCLAIMER ?>
            </p>
        </div>

        <div class="codex-card p-4 p-md-5 text-secondary">
            <h4 class="text-light font-heading mb-3">1. Non-Professional Advice Notice</h4>
            <p>
                The information provided by <strong>Code X</strong> and the <strong>Code X AI Assistant</strong> is for general educational, analytical, and personal budgeting purposes only. It does not constitute professional financial, investment, accounting, tax, banking, or legal advice.
            </p>

            <h4 class="text-light font-heading mb-3 mt-4">2. No Financial Guarantees</h4>
            <p>
                Code X does not guarantee specific savings results, investment returns, or financial outcomes. All calculations, budget progress warnings, and AI insights are derived from data entered directly by the user.
            </p>

            <h4 class="text-light font-heading mb-3 mt-4">3. User Responsibility</h4>
            <p>
                Users are solely responsible for their financial decisions. You should seek independent advice from a qualified financial advisor, accountant, or legal professional before making financial commitments.
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
