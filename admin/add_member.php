<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $indexnum = trim($_POST['indexnum'] ?? '');
    $idnum    = trim($_POST['idnum']    ?? '');
    $addr     = trim($_POST['addr']     ?? '');

    if ($name === '') {
        $error = 'Name is required.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare('INSERT INTO members (name, indexnum, idnum, addr) VALUES (?, ?, ?, ?)');
        if ($stmt->execute([$name, $indexnum, $idnum, $addr])) {
            header('Location: members.php?msg=Member+added+successfully');
            exit;
        } else {
            $error = 'Failed to add member.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Member – අපේ සවිය Admin</title>
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
        <a href="members.php" class="nav-item">Manage Members</a><a href="closed_agreements.php" class="nav-item">Closed Agreements</a>
        <a href="add_member.php" class="nav-item active">Add Member</a>
      </nav>
      <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-outline btn-block">Logout</a>
      </div>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <h1>Add New Member</h1>
        <a href="members.php" class="btn btn-outline">← Back to List</a>
      </header>

      <section class="content">
        <div class="card max-w-lg">
          <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="POST" action="add_member.php">
            <div class="form-group">
              <label for="name">Full Name <span class="text-danger">*</span></label>
              <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" autofocus>
            </div>
            
            <div class="form-group">
              <label for="indexnum">Index Number</label>
              <input type="text" id="indexnum" name="indexnum" value="<?= htmlspecialchars($_POST['indexnum'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label for="idnum">NIC / ID Number</label>
              <input type="text" id="idnum" name="idnum" value="<?= htmlspecialchars($_POST['idnum'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label for="addr">Address</label>
              <input type="text" id="addr" name="addr" value="<?= htmlspecialchars($_POST['addr'] ?? '') ?>">
            </div>

            <div class="form-actions mt-4">
              <button type="submit" class="btn btn-primary">Save Member</button>
              <a href="members.php" class="btn btn-outline">Cancel</a>
            </div>
          </form>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
