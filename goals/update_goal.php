<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];
$goalId = (int)($_POST['goal_id'] ?? 0);
$currentAmount = (float)($_POST['current_amount'] ?? 0);
$token = $_POST['csrf_token'] ?? '';

if (verifyCSRFToken($token) && $goalId > 0) {
    try {
        $stmt = $db->prepare("SELECT target_amount FROM financial_goals WHERE id = :id AND user_id = :uid");
        $stmt->execute([':id' => $goalId, ':uid' => $userId]);
        $goal = $stmt->fetch();

        if ($goal) {
            $status = ($currentAmount >= (float)$goal['target_amount']) ? 'completed' : 'active';
            
            $upStmt = $db->prepare("UPDATE financial_goals SET current_amount = :amt, status = :s WHERE id = :id AND user_id = :uid");
            $upStmt->execute([':amt' => $currentAmount, ':s' => $status, ':id' => $goalId, ':uid' => $userId]);

            logActivity("Updated savings progress for goal #{$goalId}");
            setFlash('success', 'Goal progress updated.');
        } else {
            setFlash('danger', 'Goal not found or permission denied.');
        }
    } catch (Exception $e) {
        setFlash('danger', 'Failed to update goal: ' . $e->getMessage());
    }
} else {
    setFlash('danger', 'Invalid request token.');
}

header('Location: goals.php');
exit;
?>
