<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // GET request just fetches basic name info to show on the Auth screen
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['error' => 'Invalid ID']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, name, indexnum FROM members WHERE id = ?');
    $stmt->execute([$id]);
    $member = $stmt->fetch();

    if ($member) {
        echo json_encode(['member' => $member]);
    } else {
        echo json_encode(['error' => 'Member not found']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST request verifies ID and returns full payment history
    $id = intval($_POST['id'] ?? 0);
    $idnum = trim($_POST['idnum'] ?? '');

    if (!$id || !$idnum) {
        echo json_encode(['error' => 'Missing ID or ID Number']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, name, indexnum, idnum FROM members WHERE id = ?');
    $stmt->execute([$id]);
    $member = $stmt->fetch();

    if (!$member || $member['idnum'] !== $idnum) {
        // We use a generic error message for security so as not to confirm existence of mismatched ID
        echo json_encode(['error' => 'Invalid ID Number. Please try again.']);
        exit;
    }

    // Auth successful, fetch payments
    $payStmt = $pdo->prepare('SELECT amount, month, year, payment_date FROM payments WHERE member_id = ? ORDER BY year DESC, month DESC');
    $payStmt->execute([$id]);
    $payments = $payStmt->fetchAll();

    // Check if current month is paid
    $currentMonth = (int)date('n'); // 1-12
    $currentYear = (int)date('Y');
    
    $currentPaid = false;
    $totalPaid = 0;
    
    foreach ($payments as $p) {
        $totalPaid += (float)$p['amount'];
        if ((int)$p['month'] === $currentMonth && (int)$p['year'] === $currentYear) {
            $currentPaid = true;
        }
    }

    echo json_encode([
        'member' => $member,
        'payments' => $payments,
        'current_month_paid' => $currentPaid,
        'total_paid' => $totalPaid
    ]);
    exit;
}
?>
