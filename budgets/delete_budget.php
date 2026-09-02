<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];
$budgetId = (int)($_GET['id'] ?? 0);
$token = $_GET['csrf_token'] ?? '';

if (verifyCSRFToken($token) && $budgetId > 0) {
    try {
        $stmt = $db->prepare("DELETE FROM budgets WHERE id = :id AND user_id = :uid");
        $stmt->execute([':id' => $budgetId, ':uid' => $userId]);

        if ($stmt->rowCount() > 0) {
            logActivity("Deleted budget record ID #{$budgetId}");
            setFlash('success', 'Budget removed successfully.');
        } else {
            setFlash('danger', 'Budget record not found or access denied.');
        }
    } catch (Exception $e) {
        setFlash('danger', 'Failed to remove budget: ' . $e->getMessage());
    }
} else {
    setFlash('danger', 'Invalid security token.');
}

header('Location: budgets.php');
exit;
?>
