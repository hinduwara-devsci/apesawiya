<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$pdo = getDB();

// CSV Export for ALL completed payments
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=ape_sawiya_all_payments_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Transfer ID', 'Member ID', 'Member Name', 'Index Num', 'NIC', 'Amount (LKR)', 'For Month', 'For Year', 'Recorded Date']);

$stmt = $pdo->query('
    SELECT p.id as pid, p.member_id, m.name, m.indexnum, m.idnum, p.amount, p.month, p.year, p.payment_date 
    FROM payments p 
    JOIN members m ON p.member_id = m.id 
    ORDER BY p.payment_date DESC
');

$months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

while ($row = $stmt->fetch()) {
    fputcsv($output, [
        $row['pid'],
        $row['member_id'],
        $row['name'],
        $row['indexnum'],
        $row['idnum'],
        $row['amount'],
        $months[$row['month']],
        $row['year'],
        $row['payment_date']
    ]);
}

fclose($output);
exit;
?>
