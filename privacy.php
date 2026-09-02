<?php
$pageTitle = "Privacy Policy - CODE X";
$pageDescription = "Code X Privacy Policy detailing data encryption, user isolation, and security principles.";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="max-w-3xl mx-auto">
        <h1 class="display-5 font-heading text-light mb-4">Privacy Policy</h1>
        
        <div class="codex-card p-4 p-md-5 text-secondary">
            <h4 class="text-light font-heading mb-3">1. Data Privacy & Isolation</h4>
            <p>
                At <strong>Code X</strong>, privacy is a fundamental core requirement. User financial data—including income, expense transactions, category budgets, and savings goals—is strictly isolated per user account. No user can access or view another user's financial information.
            </p>

            <h4 class="text-light font-heading mb-3 mt-4">2. Security & Password Protection</h4>
            <p>
                All account passwords are encrypted using secure cryptographic password hashing algorithms (`PASSWORD_BCRYPT` / `PASSWORD_DEFAULT`). Plaintext passwords are never stored in the database.
            </p>

            <h4 class="text-light font-heading mb-3 mt-4">3. AI Data Usage</h4>
            <p>
                The <strong>Code X AI Assistant</strong> processes financial data strictly for generating real-time educational guidance within your session. Your data is not sold or shared with external third-party data brokers.
            </p>

            <h4 class="text-light font-heading mb-3 mt-4">4. Compliance & Storage</h4>
            <p>
                The system operates using local database architecture (XAMPP / MySQL PDO) designed for secure web deployment.
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
