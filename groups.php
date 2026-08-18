<?php
include 'auth_check.php';
include 'config.php';
include 'includes/header.php';

if ($_SESSION['role'] !== 'principal') {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    include 'includes/footer.php';
    exit;
}

$q = "SELECT g.id, g.group_name, a.title,
      (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.id) AS members
      FROM groups g
      LEFT JOIN activities a ON a.id = g.activity_id
      ORDER BY g.created_at DESC";
$res = $conn->query($q);
?>

<style>
  .group-card {
    background-color: #f4fafb;
    border: 1px solid #d7eef1;
    border-radius: 18px;
    box-shadow: 0 10px 28px rgba(11, 57, 84, 0.08);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }
  .group-card h5 { color: #0b3954; }
  .member-list { font-size: 0.95rem; color: #0b3954; }
</style>

<h2 class="mb-4">Group List</h2>
<a href="create_group.php" class="btn btn-primary mb-3">New Group</a>

<div class="row">
<?php while ($g = $res->fetch_assoc()): ?>
  <?php
    $gid = (int)$g['id'];
    $teachers_sql = "SELECT u.username FROM group_members gm JOIN users u ON u.id = gm.user_id WHERE gm.group_id = $gid LIMIT 3";
    $teachers_res = $conn->query($teachers_sql);
    $teacher_names = [];
    while ($t = $teachers_res->fetch_assoc()) {
        $teacher_names[] = htmlspecialchars($t['username']);
    }
  ?>
  <div class="col-md-4">
    <div class="group-card">
      <h5><?= htmlspecialchars($g['group_name']) ?></h5>
      <p class="mb-1">Linked Activity</p>
      <p><strong><?= htmlspecialchars($g['title'] ?? '-') ?></strong></p>
      <p class="mb-1">Member Count</p>
      <p><strong><?= (int)$g['members'] ?></strong></p>
      <p class="mb-2">Teacher List</p>
      <div class="member-list">
        <?= implode(', ', $teacher_names) ?: '<span class="text-muted">No teachers</span>' ?>
      </div>
      <div class="text-end mt-3">
        <a href="group_chat.php?group_id=<?= (int)$g['id'] ?>" class="btn btn-success btn-sm">Enter Group Chat</a>
      </div>
    </div>
  </div>
<?php endwhile; ?>
</div>

<a href="dashboard_principal.php" class="btn btn-secondary mt-4">Back</a>

<?php include 'includes/footer.php'; ?>