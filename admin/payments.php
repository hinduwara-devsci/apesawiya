<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$pdo = getDB();

$search = trim($_GET['search'] ?? '');
$page   = max(1, intval($_GET['page'] ?? 1));
$limit  = 15;
$offset = ($page - 1) * $limit;

// Payment List Query with Join
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $pdo->prepare('
        SELECT p.*, m.name, m.indexnum 
        FROM payments p 
        JOIN members m ON p.member_id = m.id 
        WHERE m.name LIKE ? OR m.indexnum LIKE ?
        ORDER BY p.payment_date DESC LIMIT ? OFFSET ?
    ');
    $stmt->execute([$like, $like, $limit, $offset]);
    $payments = $stmt->fetchAll();

    $cnt = $pdo->prepare('SELECT COUNT(*) FROM payments p JOIN members m ON p.member_id = m.id WHERE m.name LIKE ? OR m.indexnum LIKE ?');
    $cnt->execute([$like, $like]);
} else {
    $stmt = $pdo->prepare('
        SELECT p.*, m.name, m.indexnum 
        FROM payments p 
        JOIN members m ON p.member_id = m.id 
        ORDER BY p.payment_date DESC LIMIT ? OFFSET ?
    ');
    $stmt->execute([$limit, $offset]);
    $payments = $stmt->fetchAll();

    $cnt = $pdo->query('SELECT COUNT(*) FROM payments');
}

$total = $cnt->fetchColumn();
$totalPages = max(1, ceil($total / $limit));

$months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment History – අපේ සවිය Admin</title>
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
        <a href="payments.php" class="nav-item active">Payments</a>
        <a href="members.php" class="nav-item">Manage Members</a><a href="closed_agreements.php" class="nav-item">Closed Agreements</a>
      </nav>
      <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-outline btn-block">Logout</a>
      </div>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <h1>Payment History</h1>
        <div style="display:flex; gap:10px;">
            <a href="export_all_payments.php" class="btn btn-outline" title="Export All">📥 Export All CSV</a>
            <a href="add_payment.php" class="btn btn-primary" style="background:#10b981;">+ Log Payment</a>
        </div>
      </header>

      <section class="content">
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <div class="card">
          <div class="card-header flex-between">
            <form method="GET" action="payments.php" class="search-form">
              <input type="text" name="search" placeholder="Search member name..." value="<?= htmlspecialchars($search) ?>" style="width:240px;">
              <button type="submit" class="btn btn-secondary">Filter</button>
              <?php if($search): ?>
                <a href="payments.php" class="btn btn-outline">Clear</a>
              <?php endif; ?>
            </form>
            <div class="total-badge">Total Records: <?= $total ?></div>
          </div>
          
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Log ID</th>
                  <th>Member Name</th>
                  <th>For Month</th>
                  <th>Date Paid</th>
                  <th class="text-right">Amount</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($payments): ?>
                  <?php foreach ($payments as $p): ?>
                    <tr>
                      <td>#<?= $p['id'] ?></td>
                      <td><strong><?= htmlspecialchars($p['name']) ?></strong> <span class="text-muted">(<?= htmlspecialchars($p['indexnum'] ?? '') ?>)</span></td>
                      <td><?= $months[$p['month']] ?> <?= $p['year'] ?></td>
                      <td><?= date('d M Y, g:ia', strtotime($p['payment_date'])) ?></td>
                      <td class="text-right text-success" style="font-weight:600;">LKR <?= number_format($p['amount'], 2) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="5" class="text-center text-muted py-4">No payments recorded.</td></tr>
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
