<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$pdo = getDB();

$search = trim($_GET['search'] ?? '');
$page   = max(1, intval($_GET['page'] ?? 1));
$limit  = 15;
$offset = ($page - 1) * $limit;

if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $pdo->prepare('SELECT * FROM left_members WHERE name LIKE ? OR idnum LIKE ? ORDER BY leave_date DESC LIMIT ? OFFSET ?');
    $stmt->execute([$like, $like, $limit, $offset]);
    $left_members = $stmt->fetchAll();

    $cnt = $pdo->prepare('SELECT COUNT(*) FROM left_members WHERE name LIKE ? OR idnum LIKE ?');
    $cnt->execute([$like, $like]);
} else {
    $stmt = $pdo->prepare('SELECT * FROM left_members ORDER BY leave_date DESC LIMIT ? OFFSET ?');
    $stmt->execute([$limit, $offset]);
    $left_members = $stmt->fetchAll();

    $cnt = $pdo->query('SELECT COUNT(*) FROM left_members');
}

$total = $cnt->fetchColumn();
$totalPages = max(1, ceil($total / $limit));

// Get total amount refunded overall
$refundStmt = $pdo->query('SELECT SUM(total_refunded) FROM left_members');
$totalRefunded = $refundStmt->fetchColumn() ?: 0.00;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Closed Agreements – අපේ සවිය Admin</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="admin-wrapper">
    <aside class="sidebar">
      <div class="sidebar-header">
        <img src="../logo.png" alt="Logo" class="sidebar-logo">
        <h2>Admin Panel</h2>
      </div>
      <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item">Dashboard</a>
        <a href="payments.php" class="nav-item">Payments</a>
        <a href="members.php" class="nav-item">Manage Members</a>
        <a href="closed_agreements.php" class="nav-item active">Closed Agreements</a>
      </nav>
      <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-outline btn-block">Logout</a>
      </div>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <h1>Closed Agreements (Left Members)</h1>
      </header>

      <section class="content">
        
        <div class="card" style="margin-bottom:24px; border-left: 4px solid var(--danger);">
          <h3>Total Lifetime Refunds Issued</h3>
          <div class="stat-value text-danger" style="font-size:24px;">LKR <?= number_format($totalRefunded, 2) ?></div>
        </div>

        <div class="card">
          <div class="card-header flex-between">
            <form method="GET" action="closed_agreements.php" class="search-form">
              <input type="text" name="search" placeholder="Search by name or NIC..." value="<?= htmlspecialchars($search) ?>" style="width:240px;">
              <button type="submit" class="btn btn-secondary">Search</button>
              <?php if($search): ?>
                <a href="closed_agreements.php" class="btn btn-outline">Clear</a>
              <?php endif; ?>
            </form>
            <div class="total-badge">Total Records: <?= $total ?></div>
          </div>
          
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Old Member ID</th>
                  <th>Name</th>
                  <th>NIC / ID</th>
                  <th>Leave Date</th>
                  <th class="text-right">Total Refunded</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($left_members): ?>
                  <?php foreach ($left_members as $m): ?>
                    <tr>
                      <td class="text-muted">#<?= $m['original_member_id'] ?></td>
                      <td><strong><?= htmlspecialchars($m['name']) ?></strong></td>
                      <td><?= htmlspecialchars($m['idnum'] ?? '—') ?></td>
                      <td><?= date('d M Y', strtotime($m['leave_date'])) ?></td>
                      <td class="text-right text-danger" style="font-weight:600;">LKR <?= number_format($m['total_refunded'], 2) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="5" class="text-center text-muted py-4">No closed agreements found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
          <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-outline' ?>">
                <?= $i ?>
              </a>
            <?php endfor; ?>
          </div>
          <?php endif; ?>

        </div>
      </section>
    </main>
  </div>
</body>
</html>
