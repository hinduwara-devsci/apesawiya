<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$pdo  = getDB();
$page    = max(1, intval($_GET['page']   ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;
$search  = trim($_GET['search'] ?? '');

if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $pdo->prepare('SELECT * FROM members WHERE name LIKE ? ORDER BY id LIMIT ? OFFSET ?');
    $stmt->execute([$like, $perPage, $offset]);
    $members = $stmt->fetchAll();

    $cnt = $pdo->prepare('SELECT COUNT(*) FROM members WHERE name LIKE ?');
    $cnt->execute([$like]);
} else {
    $stmt = $pdo->prepare('SELECT * FROM members ORDER BY id LIMIT ? OFFSET ?');
    $stmt->execute([$perPage, $offset]);
    $members = $stmt->fetchAll();

    $cnt = $pdo->prepare('SELECT COUNT(*) FROM members');
    $cnt->execute();
}

$total      = (int) $cnt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));

echo json_encode([
    'members'     => $members,
    'totalPages'  => $totalPages,
    'currentPage' => $page,
]);
?>
