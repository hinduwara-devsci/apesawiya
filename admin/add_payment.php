<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$pdo = getDB();
$error = '';

/** Fetch all members for the dropdown */
$memberStmt = $pdo->query('SELECT id, name, indexnum FROM members ORDER BY name ASC');
$members = $memberStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = intval($_POST['member_id'] ?? 0);
    $amount   = floatval($_POST['amount'] ?? 0);
    $month    = intval($_POST['month'] ?? date('n'));
    $year     = intval($_POST['year'] ?? date('Y'));

    if (!$memberId || $amount <= 0 || !$month || !$year) {
        $error = 'All fields are required and amount must be positive.';
    } else {
        try {
            // DB Transaction to ensure both Payments and Funds update together
            $pdo->beginTransaction();

            $insert = $pdo->prepare('INSERT INTO payments (member_id, amount, month, year) VALUES (?, ?, ?, ?)');
            $insert->execute([$memberId, $amount, $month, $year]);

            $fundUpdate = $pdo->prepare('UPDATE funds SET total_balance = total_balance + ? WHERE id = 1');
            $fundUpdate->execute([$amount]);

            $pdo->commit();
            header('Location: payments.php?msg=Payment+Logged+Successfully');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to log payment processing transaction.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Log Payment – අපේ සවිය Admin</title>
  <link rel="stylesheet" href="style.css">
  <style>
      .form-select { width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 6px; font-family: inherit; font-size: 14px; background: var(--surface); cursor:pointer; }
      .form-row { display: flex; gap: 16px; }
      .form-row .form-group { flex: 1; }
  </style>
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
        <a href="members.php" class="nav-item">Manage Members</a><a href="closed_agreements.php" class="nav-item">Closed Agreements</a>
      </nav>
      <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-outline btn-block">Logout</a>
      </div>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <h1>Log New Payment</h1>
        <a href="payments.php" class="btn btn-outline">← Back to Payments</a>
      </header>

      <section class="content">
        <div class="card max-w-lg">
          <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="POST" action="add_payment.php">
            <div class="form-group">
              <label for="member_id">Select Member <span class="text-danger">*</span></label>
              <select name="member_id" id="member_id" class="form-select" required autofocus>
                  <option value="">-- Choose Member --</option>
                  <?php foreach($members as $m): ?>
                      <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['indexnum']) ?>)</option>
                  <?php endforeach; ?>
              </select>
            </div>
            
            <div class="form-group">
              <label for="amount">Payment Amount (LKR) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" id="amount" name="amount" required placeholder="500.00">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="month">For Month <span class="text-danger">*</span></label>
                    <select name="month" id="month" class="form-select" required>
                        <?php 
                        $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
                        $curM = date('n');
                        foreach ($months as $num => $name): ?>
                            <option value="<?= $num ?>" <?= $num == $curM ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="year">For Year <span class="text-danger">*</span></label>
                    <input type="number" name="year" id="year" value="<?= date('Y') ?>" required>
                </div>
            </div>

            <div class="form-actions mt-4">
              <button type="submit" class="btn btn-primary" style="background:#10b981;">Log Direct Payment</button>
            </div>
          </form>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
