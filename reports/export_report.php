<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];

$startDate = sanitize($_GET['start_date'] ?? '');
$endDate = sanitize($_GET['end_date'] ?? '');

$sql = "
    SELECT t.transaction_date, t.transaction_type, c.name as category_name, t.amount, t.description
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = :uid
";
$params = [':uid' => $userId];

if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND t.transaction_date BETWEEN :sdate AND :edate";
    $params[':sdate'] = $startDate;
    $params[':edate'] = $endDate;
}
$sql .= " ORDER BY t.transaction_date DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Set CSV Headers
$filename = "CodeX_Financial_Report_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Header Row
fputcsv($output, ['Date', 'Type', 'Category', 'Amount ($)', 'Description']);

foreach ($rows as $row) {
    fputcsv($output, [
        $row['transaction_date'],
        ucfirst($row['transaction_type']),
        $row['category_name'],
        number_format((float)$row['amount'], 2, '.', ''),
        $row['description']
    ]);
}

fclose($output);
exit;
?>
