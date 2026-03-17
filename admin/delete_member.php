<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

// Only accept POST for deletion to prevent CSRF via GET links
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
        $pdo = getDB();
        $stmt = $pdo->prepare('DELETE FROM members WHERE id = ?');
        $stmt->execute([$id]);
    }
}
header('Location: members.php?msg=Member+deleted');
exit;
?>
