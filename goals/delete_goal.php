<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];
$goalId = (int)($_GET['id'] ?? 0);
$token = $_GET['csrf_token'] ?? '';

if (verifyCSRFToken($token) && $goalId > 0) {
    try {
        $stmt = $db->prepare("DELETE FROM financial_goals WHERE id = :id AND user_id = :uid");
        $stmt->execute([':id' => $goalId, ':uid' => $userId]);

        if ($stmt->rowCount() > 0) {
            logActivity("Deleted financial goal ID #{$goalId}");
            setFlash('success', 'Savings goal deleted successfully.');
        } else {
            setFlash('danger', 'Goal not found or permission denied.');
        }
    } catch (Exception $e) {
        setFlash('danger', 'Failed to delete goal: ' . $e->getMessage());
    }
} else {
    setFlash('danger', 'Invalid security token.');
}

header('Location: goals.php');
exit;
?>
