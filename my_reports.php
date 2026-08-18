<?php
include 'auth_check.php';
include 'config.php';
include 'includes/header.php';

$uid = $_SESSION['user_id'];
$role = $_SESSION['role'];

$stmt = $conn->prepare('SELECT * FROM activities WHERE created_by = ? ORDER BY date DESC');
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
?>

<h2>My Reports</h2>

<div class="table-responsive">
  <table class="table table-bordered mobile-card-table">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Type</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; while ($row = $res->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($row['title']) ?></td>
          <td><?= htmlspecialchars($row['activity_type']) ?></td>
          <td><?= htmlspecialchars($row['date']) ?></td>
          <td>
            <div class="mobile-actions">
              <a href="view_activity.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-info">View</a>
              <a href="export_activity_pdf.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-danger">Export PDF</a>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<a href="<?= $role === 'principal' ? 'dashboard_principal.php' : 'dashboard_admin.php' ?>" class="btn btn-secondary mt-3">Back</a>

<?php include 'includes/footer.php'; ?>