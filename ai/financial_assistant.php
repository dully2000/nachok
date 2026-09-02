<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/ai_service.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];
$conversationId = (int)($_GET['conv_id'] ?? 0);

// If form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prompt = sanitize($_POST['prompt'] ?? '');
    $convId = (int)($_POST['conversation_id'] ?? 0);
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (verifyCSRFToken($csrfToken) && !empty($prompt)) {
        $result = generateAIResponse($userId, $prompt, $convId);
        header('Location: financial_assistant.php?conv_id=' . $result['conversation_id']);
        exit;
    }
}

$pageTitle = "Code X AI Assistant - Financial Guidance";
require_once __DIR__ . '/../includes/header.php';

// Fetch user's AI conversations list
$cStmt = $db->prepare("SELECT * FROM ai_conversations WHERE user_id = :uid ORDER BY id DESC LIMIT 10");
$cStmt->execute([':uid' => $userId]);
$conversations = $cStmt->fetchAll();

// If no active conversation selected, pick latest
if ($conversationId === 0 && !empty($conversations)) {
    $conversationId = $conversations[0]['id'];
}

// Fetch messages for active conversation
$messages = [];
if ($conversationId > 0) {
    $mStmt = $db->prepare("SELECT * FROM ai_messages WHERE conversation_id = :cid AND user_id = :uid ORDER BY id ASC");
    $mStmt->execute([':cid' => $conversationId, ':uid' => $userId]);
    $messages = $mStmt->fetchAll();
}
?>

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 font-heading text-light mb-1"><i class="fa-solid fa-robot text-info me-2"></i>Code X AI Financial Assistant</h1>
            <p class="small text-secondary mb-0">Intelligent personal financial education and context-aware budgeting guidance</p>
        </div>
        <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-25 px-3 py-2">
            <i class="fa-solid fa-shield-halved me-1"></i> Educational Guidance Only
        </span>
    </div>

    <div class="row g-4">
        <!-- Sidebar Conversations History -->
        <div class="col-lg-3">
            <div class="codex-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-light fw-medium small text-uppercase"><i class="fa-solid fa-clock-rotate-left me-1"></i> History</span>
                    <a href="financial_assistant.php" class="btn btn-sm btn-codex-primary py-1 px-2" title="Start New Session"><i class="fa-solid fa-plus"></i> New Session</a>
                </div>

                <div class="d-flex flex-column gap-2 overflow-auto" style="max-height: 480px;">
                    <?php if (empty($conversations)): ?>
                        <p class="small text-muted mb-0">No past sessions.</p>
                    <?php else: ?>
                        <?php foreach ($conversations as $c): ?>
                            <a href="financial_assistant.php?conv_id=<?= $c['id'] ?>" class="p-2 rounded text-decoration-none small text-truncate <?= $conversationId == $c['id'] ? 'bg-primary text-white' : 'text-secondary bg-dark bg-opacity-50 hover-white' ?>">
                                <i class="fa-solid fa-message me-1 opacity-75"></i> <?= sanitize($c['title']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Chat Panel -->
        <div class="col-lg-9">
            <div class="codex-card p-4">
                <!-- Chat Message Stream -->
                <div class="chat-box mb-3" id="chatBox">
                    <?php if (empty($messages)): ?>
                        <div class="text-center py-5 text-secondary">
                            <div class="stat-icon bg-info bg-opacity-20 text-info mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.75rem;">
                                <i class="fa-solid fa-robot"></i>
                            </div>
                            <h4 class="h5 font-heading text-light mb-2">Hello, <?= sanitize($_SESSION['user_name']) ?>!</h4>
                            <p class="small text-muted max-w-md mx-auto mb-4">
                                I am <strong>Code X AI</strong>, your personal financial education and budgeting assistant. Ask me questions about your spending patterns, budgets, savings goals, or overall balance.
                            </p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <?php if ($msg['role'] === 'user'): ?>
                                <div class="chat-bubble chat-bubble-user">
                                    <div class="fw-semibold mb-1 small text-white-50"><i class="fa-solid fa-user me-1"></i> You</div>
                                    <div><?= nl2br(sanitize($msg['message'])) ?></div>
                                </div>
                            <?php else: ?>
                                <div class="chat-bubble chat-bubble-ai">
                                    <div class="fw-semibold mb-2 small text-info"><i class="fa-solid fa-robot me-1"></i> Code X AI</div>
                                    <div class="ai-rendered-content"><?= formatAIMarkdown($msg['message']) ?></div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Quick Suggested Question Pills -->
                <div class="mb-3">
                    <span class="small text-muted me-2"><i class="fa-solid fa-lightbulb me-1 text-warning"></i> Suggested Prompts:</span>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-codex-outline rounded-pill text-start" onclick="sendQuickPrompt('Why am I spending too much?')">
                            "Why am I spending too much?"
                        </button>
                        <button type="button" class="btn btn-sm btn-codex-outline rounded-pill text-start" onclick="sendQuickPrompt('How can I improve my savings rate?')">
                            "How can I improve my savings rate?"
                        </button>
                        <button type="button" class="btn btn-sm btn-codex-outline rounded-pill text-start" onclick="sendQuickPrompt('Which category am I spending the most money on?')">
                            "Which category am I spending the most money on?"
                        </button>
                        <button type="button" class="btn btn-sm btn-codex-outline rounded-pill text-start" onclick="sendQuickPrompt('Am I following my budget?')">
                            "Am I following my budget?"
                        </button>
                    </div>
                </div>

                <!-- Input Form -->
                <form action="financial_assistant.php" method="POST" id="aiForm">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="conversation_id" value="<?= $conversationId ?>">

                    <div class="input-group">
                        <input type="text" id="promptInput" name="prompt" class="form-control form-control-lg" placeholder="Ask Code X AI a financial question..." required autocomplete="off">
                        <button type="submit" class="btn btn-codex-primary px-4">
                            <i class="fa-solid fa-paper-plane me-1"></i> Ask AI
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function sendQuickPrompt(promptText) {
    document.getElementById('promptInput').value = promptText;
    document.getElementById('aiForm').submit();
}
</script>

<?php 
// Helper to convert AI Markdown headers and lists to HTML
function formatAIMarkdown($text) {
    $text = sanitize($text);
    $text = preg_replace('/### (.*?)\n/', '<h5 class="h6 font-heading text-light mt-3 mb-2">$1</h5>', $text);
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong class="text-light">$1</strong>', $text);
    $text = preg_replace('/\* (.*?)\n/', '<li class="small text-secondary mb-1">$1</li>', $text);
    $text = preg_replace('/(li class=.*?<\/li>)/s', '<ul class="ps-3 mb-2">$1</ul>', $text);
    return nl2br($text);
}
?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
