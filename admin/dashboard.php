<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$pdo = getDB();

// Get total members
$stmt = $pdo->query('SELECT COUNT(*) FROM members');
$totalMembers = $stmt->fetchColumn();

// Get net funds
$fundStmt = $pdo->query('SELECT total_balance FROM funds WHERE id = 1');
$netFunds = $fundStmt->fetchColumn() ?: 0.00;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard – අපේ සවිය</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  
  <div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <img src="../logo.png" alt="Logo" class="sidebar-logo">
        <h2>Admin Panel</h2>
      </div>
      <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item active">Dashboard</a>
        <a href="members.php" class="nav-item">Manage Members</a><a href="closed_agreements.php" class="nav-item">Closed Agreements</a>
        <a href="add_member.php" class="nav-item">Add Member</a>
      </nav>
      <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-outline btn-block">Logout</a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="topbar">
        <h1>Dashboard</h1>
        <div class="user-info">Logged in as <strong><?= htmlspecialchars($_SESSION['admin_user']) ?></strong></div>
      </header>

      <section class="content">
        <div class="stats-grid">
          <div class="stat-card">
            <h3>Total Members</h3>
            <div class="stat-value"><?= number_format($totalMembers) ?></div>
          </div>
          <div class="stat-card" style="border-left-color: #10b981;">
            <h3>Net Organization Funds</h3>
            <div class="stat-value text-success">LKR <?= number_format($netFunds, 2) ?></div>
          </div>
        </div>

        <div class="quick-actions">
          <h2>Quick Actions</h2>
          <div class="action-buttons">
            <a href="add_payment.php" class="btn btn-primary" style="background:#10b981;">+ Log Payment</a>
            <a href="payments.php" class="btn btn-secondary">View Payments</a>
            <a href="add_member.php" class="btn btn-primary">Add New Member</a>
            <a href="members.php" class="btn btn-secondary">View All Members</a>
            <a href="../index.html" class="btn btn-outline" target="_blank">View Public Site ↗</a>
          </div>
        </div>
      </section>
    </main>
  </div>

</body>
</html>
