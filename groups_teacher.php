<?php
include 'auth_check.php';
include 'config.php';
include 'includes/header.php';

if (!eams_is_staff()) {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    include 'includes/footer.php';
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT g.id, g.group_name, a.title
        FROM groups g
        JOIN group_members gm ON gm.group_id = g.id
        LEFT JOIN activities a ON a.id = g.activity_id
        WHERE gm.user_id = ?
        ORDER BY g.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2 class="mb-4">My Groups</h2>

<?php if ($result->num_rows === 0): ?>
  <div class="alert alert-info">You have not been added to any groups yet.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-bordered shadow-sm mobile-card-table">
      <thead class="table-primary text-white">
        <tr>
          <th>#</th>
          <th>Group Name</th>
          <th>Associated Activity</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($row['group_name']) ?></td>
          <td><?= htmlspecialchars($row['title']) ?></td>
          <td>
            <a href="group_chat.php?group_id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-primary">Enter Group Chat</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<a href="<?= ($_SESSION['role'] === 'principal' ? 'dashboard_principal.php' : 'dashboard_admin.php') ?>" class="btn btn-secondary">Back</a>

<?php include 'includes/footer.php'; ?>