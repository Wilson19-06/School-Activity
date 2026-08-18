<?php
include 'auth_check.php';
include 'config.php';
include 'includes/header.php';

if ($_SESSION['role'] !== 'principal') {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    include 'includes/footer.php';
    exit;
}

if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE teacher_reports SET status = 'Approved' WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: principal_review_reports.php');
    exit;
}

if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $stmt = $conn->prepare("UPDATE teacher_reports SET status = 'Rejected' WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: principal_review_reports.php');
    exit;
}

$rows = $conn->query('SELECT * FROM teacher_reports ORDER BY date DESC');
?>

<h2>Teacher Report Review</h2>

<div class="table-responsive">
  <table class="table table-bordered shadow-sm mobile-card-table">
    <thead class="table-primary text-white">
      <tr>
        <th>#</th>
        <th>Teacher</th>
        <th>Activity Name</th>
        <th>Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; while ($r = $rows->fetch_assoc()): ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($r['teacher_name']) ?></td>
        <td><?= htmlspecialchars($r['title']) ?></td>
        <td><?= htmlspecialchars($r['date']) ?></td>
        <td><?= htmlspecialchars($r['status']) ?></td>
        <td>
          <div class="mobile-actions">
            <a href="?approve=<?= (int)$r['id'] ?>" class="btn btn-success btn-sm">Approve</a>
            <a href="?reject=<?= (int)$r['id'] ?>" class="btn btn-danger btn-sm">Reject</a>
          </div>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include 'includes/footer.php'; ?>