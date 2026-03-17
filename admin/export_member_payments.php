<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    die("Invalid member ID");
}

$pdo = getDB();

$memStmt = $pdo->prepare('SELECT name, idnum FROM members WHERE id = ?');
$memStmt->execute([$id]);
$member = $memStmt->fetch();

if (!$member) {
    die("Member not found");
}

$safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $member['name']);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=payment_record_' . $safeName . '_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Member Name', 'NIC', 'Amount (LKR)', 'For Month', 'For Year', 'Payment Date']);

$stmt = $pdo->prepare('SELECT amount, month, year, payment_date FROM payments WHERE member_id = ? ORDER BY year DESC, month DESC');
$stmt->execute([$id]);

$months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

while ($row = $stmt->fetch()) {
    fputcsv($output, [
        $member['name'],
        $member['idnum'],
        $row['amount'],
        $months[$row['month']],
        $row['year'],
        $row['payment_date']
    ]);
}

fclose($output);
exit;
?>
