<?php
$baseUrl = getBaseUrl();
?>
<footer class="py-5 text-secondary">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-lg-5 col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <span class="brand-name fs-4">CODE X</span>
                </div>
                <p class="small text-muted mb-3">
                    <?= APP_TAGLINE ?>
                </p>
                <p class="small text-dim mb-0">
                    Built for 2027 Final Year Information Technology Project Presentation. Designed for intelligent personal financial education and budgeting automation.
                </p>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="text-light font-heading mb-3">Navigation</h6>
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                    <li><a href="<?= $baseUrl ?>index.php" class="text-secondary text-decoration-none hover-white">Home</a></li>
                    <li><a href="<?= $baseUrl ?>about.php" class="text-secondary text-decoration-none hover-white">About System</a></li>
                    <li><a href="<?= $baseUrl ?>contact.php" class="text-secondary text-decoration-none hover-white">Contact & Support</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="text-light font-heading mb-3">Legal & Compliance</h6>
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                    <li><a href="<?= $baseUrl ?>privacy.php" class="text-secondary text-decoration-none hover-white">Privacy Policy</a></li>
                    <li><a href="<?= $baseUrl ?>disclaimer.php" class="text-secondary text-decoration-none hover-white">Financial Disclaimer</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="text-light font-heading mb-3">Educational Guidance</h6>
                <div class="disclaimer-banner p-3 rounded small">
                    <i class="fa-solid fa-shield-halved me-1"></i>
                    <?= FINANCIAL_DISCLAIMER ?>
                </div>
            </div>
        </div>

        <hr class="border-secondary opacity-25 my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-muted">
            <p class="mb-2 mb-md-0">&copy; <?= date('Y') ?> CODE X System. All rights reserved.</p>
            <p class="mb-0">Powered by PHP 8, MySQL, Bootstrap 5 & Code X AI Engine</p>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?= $baseUrl ?>assets/js/main.js"></script>
</body>
</html>
