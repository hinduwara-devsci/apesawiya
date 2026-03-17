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
    $stmt = $pdo->prepare('SELECT * FROM members WHERE name LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?');
    $stmt->execute([$like, $limit, $offset]);
    $members = $stmt->fetchAll();

    $cnt = $pdo->prepare('SELECT COUNT(*) FROM members WHERE name LIKE ?');
    $cnt->execute([$like]);
} else {
    $stmt = $pdo->prepare('SELECT * FROM members ORDER BY id DESC LIMIT ? OFFSET ?');
    $stmt->execute([$limit, $offset]);
    $members = $stmt->fetchAll();

    $cnt = $pdo->query('SELECT COUNT(*) FROM members');
}

$total = $cnt->fetchColumn();
$totalPages = max(1, ceil($total / $limit));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Members – අපේ සවිය Admin</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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
        <a href="members.php" class="nav-item active">Manage Members</a><a href="closed_agreements.php" class="nav-item">Closed Agreements</a>
        <a href="add_member.php" class="nav-item">Add Member</a>
      </nav>
      <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-outline btn-block">Logout</a>
      </div>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <h1>Manage Members</h1>
        <a href="add_member.php" class="btn btn-primary">+ Add Member</a>
      </header>

      <section class="content">
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <div class="card">
          <div class="card-header flex-between">
            <form method="GET" action="members.php" class="search-form">
              <input type="text" name="search" placeholder="Search by name..." value="<?= htmlspecialchars($search) ?>">
              <button type="submit" class="btn btn-secondary">Search</button>
              <?php if($search): ?>
                <a href="members.php" class="btn btn-outline">Clear</a>
              <?php endif; ?>
            </form>
            <div class="total-badge">Total: <?= $total ?></div>
          </div>
          
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Index No.</th>
                  <th>NIC / ID</th>
                  <th class="text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($members): ?>
                  <?php foreach ($members as $m): ?>
                    <tr>
                      <td><?= $m['id'] ?></td>
                      <td><strong><?= htmlspecialchars($m['name']) ?></strong></td>
                      <td><?= htmlspecialchars($m['indexnum'] ?? '—') ?></td>
                      <td><?= htmlspecialchars($m['idnum'] ?? '—') ?></td>
                      <td class="text-right flex-end gap-2" style="flex-wrap: wrap;">
                        <a href="add_payment.php" class="btn btn-sm" style="background:#10b981; color:white;" title="Log Payment">Pay</a>
                        <a href="export_member_payments.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline" title="Export CSV Report">📥</a>
                        <a href="edit_member.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                        <a href="leave_member.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-danger" title="Close Agreement">Leave</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="5" class="text-center text-muted py-4">No members found.</td></tr>
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
