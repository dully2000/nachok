<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];
$txId = (int)($_GET['id'] ?? 0);
$token = $_GET['csrf_token'] ?? '';

if (verifyCSRFToken($token) && $txId > 0) {
    try {
        $stmt = $db->prepare("DELETE FROM transactions WHERE id = :id AND user_id = :uid");
        $stmt->execute([':id' => $txId, ':uid' => $userId]);

        if ($stmt->rowCount() > 0) {
            logActivity("Deleted transaction ID #{$txId}");
            setFlash('success', 'Transaction deleted successfully.');
        } else {
            setFlash('danger', 'Transaction not found or permission denied.');
        }
    } catch (Exception $e) {
        setFlash('danger', 'Failed to delete transaction: ' . $e->getMessage());
    }
} else {
    setFlash('danger', 'Invalid security token.');
}

header('Location: transactions.php');
exit;
?>
