<?php
/**
 * Code X AI - Intelligent Financial Assistant Core Engine
 * Context-Aware Autonomous Financial Reasoning & Education Service
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

function generateAIResponse($userId, $userPrompt, $conversationId = null) {
    $db = getDBConnection();

    // 1. Gather Rich Financial Context from User Database Records
    // Overall totals
    $totStmt = $db->prepare("
        SELECT 
            SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) AS total_income,
            SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) AS total_expenses
        FROM transactions WHERE user_id = :uid
    ");
    $totStmt->execute([':uid' => $userId]);
    $totals = $totStmt->fetch();

    $income = (float)($totals['total_income'] ?? 0);
    $expenses = (float)($totals['total_expenses'] ?? 0);
    $netBalance = $income - $expenses;
    $totalSavings = max(0, $netBalance);
    $savingsRate = ($income > 0) ? round(($totalSavings / $income) * 100, 1) : 0;

    // Top Expense Categories
    $catStmt = $db->prepare("
        SELECT c.name, SUM(t.amount) as amt
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = :uid AND t.transaction_type = 'expense'
        GROUP BY c.id, c.name
        ORDER BY amt DESC LIMIT 3
    ");
    $catStmt->execute([':uid' => $userId]);
    $topCategories = $catStmt->fetchAll();

    // Budgets Status
    $currentMonth = (int)date('m');
    $currentYear = (int)date('Y');
    $bStmt = $db->prepare("
        SELECT b.*, c.name as category_name,
            COALESCE((
                SELECT SUM(t.amount) 
                FROM transactions t 
                WHERE t.user_id = b.user_id 
                  AND t.category_id = b.category_id 
                  AND t.transaction_type = 'expense'
                  AND MONTH(t.transaction_date) = b.month 
                  AND YEAR(t.transaction_date) = b.year
            ), 0) as spent
        FROM budgets b
        JOIN categories c ON b.category_id = c.id
        WHERE b.user_id = :uid AND b.month = :m AND b.year = :y
    ");
    $bStmt->execute([':uid' => $userId, ':m' => $currentMonth, ':y' => $currentYear]);
    $budgets = $bStmt->fetchAll();

    // Active Goals
    $gStmt = $db->prepare("SELECT * FROM financial_goals WHERE user_id = :uid AND status = 'active' ORDER BY target_date ASC");
    $gStmt->execute([':uid' => $userId]);
    $goals = $gStmt->fetchAll();

    // 2. Synthesize Intelligence Data Summary
    $topCatText = [];
    foreach ($topCategories as $tc) {
        $topCatText[] = $tc['name'] . ' (' . formatCurrency($tc['amt']) . ')';
    }
    $topCatSummary = !empty($topCatText) ? implode(', ', $topCatText) : 'No expense categories logged yet';

    $budgetOverruns = [];
    $nearLimits = [];
    foreach ($budgets as $b) {
        $pct = ($b['amount'] > 0) ? ($b['spent'] / $b['amount']) * 100 : 0;
        if ($pct >= 100) {
            $budgetOverruns[] = $b['category_name'] . ' (' . round($pct) . '% used, exceeded by ' . formatCurrency($b['spent'] - $b['amount']) . ')';
        } elseif ($pct >= 80) {
            $nearLimits[] = $b['category_name'] . ' (' . round($pct) . '% used)';
        }
    }

    $goalsSummary = [];
    foreach ($goals as $g) {
        $gPct = ($g['target_amount'] > 0) ? round(($g['current_amount'] / $g['target_amount']) * 100, 1) : 0;
        $goalsSummary[] = $g['title'] . ': ' . formatCurrency($g['current_amount']) . ' / ' . formatCurrency($g['target_amount']) . ' (' . $gPct . '% saved)';
    }

    // 3. Structure Autonomous Intelligent 5-Part Response
    $response = "### 1. Financial Summary\n";
    $response .= "- **Total Registered Income:** " . formatCurrency($income) . "\n";
    $response .= "- **Total Registered Expenses:** " . formatCurrency($expenses) . "\n";
    $response .= "- **Net Balance:** " . formatCurrency($netBalance) . "\n";
    $response .= "- **Calculated Savings Rate:** " . $savingsRate . "%\n\n";

    $response .= "### 2. Important Observations\n";
    if ($income == 0 && $expenses == 0) {
        $response .= "- You currently have no income or expense transactions recorded in Code X.\n";
        $response .= "- To receive complete tailored analytics, please add your recent income and spending entries.\n\n";
    } else {
        $response .= "- **Top Spending Categories:** " . $topCatSummary . ".\n";
        if (!empty($budgetOverruns)) {
            $response .= "- **Over-Budget Warning:** You have exceeded limits in " . implode(', ', $budgetOverruns) . ".\n";
        } elseif (!empty($nearLimits)) {
            $response .= "- **Budget Alert:** You are approaching budget limits in " . implode(', ', $nearLimits) . ".\n";
        } else {
            $response .= "- **Budget Health:** All category budgets are currently operating within established limits.\n";
        }
        if (!empty($goalsSummary)) {
            $response .= "- **Savings Goals Progress:** " . implode('; ', $goalsSummary) . ".\n";
        }
        $response .= "\n";
    }

    $response .= "### 3. Areas to Improve\n";
    if ($savingsRate < 20) {
        $response .= "- **Savings Rate Enhancement:** Your savings rate is currently " . $savingsRate . "%. Financial best practices recommend targeting a 20%+ savings rate (`Total Savings / Total Income * 100`).\n";
    } else {
        $response .= "- **Great Discipline:** Your savings rate of " . $savingsRate . "% is strong. Focus on maintaining consistency.\n";
    }
    if (!empty($topCategories)) {
        $topCatName = $topCategories[0]['name'];
        $response .= "- **Category Optimization:** Evaluate non-essential spending in **" . $topCatName . "** to free up extra cash flow.\n";
    }
    $response .= "\n";

    $response .= "### 4. Practical Next Steps\n";
    $response .= "1. Review monthly category budget limits under the Budgets tab to ensure allocations match your financial priorities.\n";
    $response .= "2. Allocate surplus monthly savings directly towards your active financial goals.\n";
    $response .= "3. Record daily transactions consistently to keep Code X AI recommendations accurate.\n\n";

    $response .= "### 5. Financial Disclaimer\n";
    $response .= "*" . FINANCIAL_DISCLAIMER . "*";

    // 4. Store Conversation & Messages in Database
    try {
        if (!$conversationId) {
            $cStmt = $db->prepare("INSERT INTO ai_conversations (user_id, title) VALUES (:uid, :title)");
            $cStmt->execute([':uid' => $userId, ':title' => mb_substr($userPrompt, 0, 50)]);
            $conversationId = $db->lastInsertId();
        }

        // Save User Message
        $mStmt1 = $db->prepare("INSERT INTO ai_messages (conversation_id, user_id, role, message) VALUES (:cid, :uid, 'user', :msg)");
        $mStmt1->execute([':cid' => $conversationId, ':uid' => $userId, ':msg' => $userPrompt]);

        // Save Assistant Message
        $mStmt2 = $db->prepare("INSERT INTO ai_messages (conversation_id, user_id, role, message) VALUES (:cid, :uid, 'assistant', :msg)");
        $mStmt2->execute([':cid' => $conversationId, ':uid' => $userId, ':msg' => $response]);

    } catch (Exception $e) {
        // Silently handle DB logging errors if any
    }

    return [
        'conversation_id' => $conversationId,
        'response' => $response
    ];
}
?>
