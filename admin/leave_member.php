<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$pdo = getDB();
$error = '';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: members.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM members WHERE id = ?');
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    header('Location: members.php');
    exit;
}

// Calculate total they have paid so far for reference
$payStmt = $pdo->prepare('SELECT SUM(amount) FROM payments WHERE member_id = ?');
$payStmt->execute([$id]);
$totalPaid = $payStmt->fetchColumn() ?: 0.00;

// Current Net Funds to ensure we have enough to refund
$fundStmt = $pdo->query('SELECT total_balance FROM funds WHERE id = 1');
$netFunds = $fundStmt->fetchColumn() ?: 0.00;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $refund_amount = floatval($_POST['refund_amount'] ?? 0);

    if ($refund_amount < 0 || $refund_amount > $netFunds) {
        $error = 'Invalid refund amount or insufficient Organization Net Funds.';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Deduct from Net Funds
            $fundUpdate = $pdo->prepare('UPDATE funds SET total_balance = total_balance - ? WHERE id = 1');
            $fundUpdate->execute([$refund_amount]);

            // 2. Add to left_members archive table
            $archive = $pdo->prepare('INSERT INTO left_members (original_member_id, name, idnum, total_refunded) VALUES (?, ?, ?, ?)');
            $archive->execute([$member['id'], $member['name'], $member['idnum'], $refund_amount]);

            // 3. Delete from active members (cascades payments deletion automatically based on schema, but we'll assume audit trail stays in CSV exports previously if needed)
            $delete = $pdo->prepare('DELETE FROM members WHERE id = ?');
            $delete->execute([$id]);

            $pdo->commit();
            header('Location: members.php?msg=Member+Agreement+Closed+and+Refunded');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to process termination.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Close Agreement – අපේ සවිය Admin</title>
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
        <a href="members.php" class="nav-item active">Manage Members</a><a href="closed_agreements.php" class="nav-item">Closed Agreements</a>
      </nav>
      <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-outline btn-block">Logout</a>
      </div>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <h1>Close Agreement: <?= htmlspecialchars($member['name']) ?></h1>
        <a href="members.php" class="btn btn-outline">← Back to List</a>
      </header>

      <section class="content">
        <div class="card max-w-lg" style="border-top: 4px solid var(--danger);">
          <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <div class="alert alert-error" style="background:#fee2e2; border:1px solid #fecaca; color:#b91c1c;">
              <strong>Warning:</strong> Closing an agreement will remove this member from the active database and deduct their refunded deposit from the Organization's Net Funds.
          </div>

          <table class="admin-table" style="margin-bottom:20px;">
              <tr><th>Lifetime Total Paid by Member</th><td>LKR <?= number_format($totalPaid, 2) ?></td></tr>
              <tr><th>Current Organization Net Funds</th><td class="text-success">LKR <?= number_format($netFunds, 2) ?></td></tr>
          </table>

          <form method="POST" action="leave_member.php?id=<?= $id ?>" onsubmit="return confirm('Are you completely sure you want to close this agreement?');">
            
            <div class="form-group">
              <label for="refund_amount">Amount to Refund (LKR) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" max="<?= $netFunds ?>" id="refund_amount" name="refund_amount" required placeholder="0.00">
              <p class="text-muted" style="font-size:12px; margin-top:4px;">This amount will be deducted from Net Funds.</p>
            </div>

            <div class="form-actions mt-4">
              <button type="submit" class="btn btn-danger">Confirm Final Refund & Leave</button>
              <a href="members.php" class="btn btn-outline">Cancel</a>
            </div>
          </form>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
