<?php
$pageTitle = "Contact & Support - CODE X";
$pageDescription = "Get in touch with the Code X team for support, feedback, or project demonstration inquiries.";
require_once __DIR__ . '/includes/header.php';

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sent = true;
    setFlash('success', 'Thank you for contacting Code X! Your message has been received.');
}
?>

<div class="container py-5">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-5">
            <h1 class="display-5 font-heading text-light mb-2">Contact & Support</h1>
            <p class="text-secondary">Have questions or feedback about the Code X system? Send us a message.</p>
        </div>

        <div class="codex-card p-4 p-md-5">
            <form action="contact.php" method="POST">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-light fw-medium">Your Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Alex Johnson" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-light fw-medium">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="alex@example.com" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-label text-light fw-medium">Subject</div>
                    <input type="text" name="subject" class="form-control" placeholder="System Inquiry / Feedback" required>
                </div>

                <div class="mb-4">
                    <label class="form-label text-light fw-medium">Message</label>
                    <textarea name="message" class="form-control" rows="5" placeholder="Enter your message here..." required></textarea>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-codex-primary btn-lg">
                        <i class="fa-solid fa-paper-plane me-2"></i>Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
