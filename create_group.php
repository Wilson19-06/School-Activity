<?php
include 'auth_check.php';
include 'config.php';
include 'includes/header.php';

if ($_SESSION['role'] !== 'principal') {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    include 'includes/footer.php';
    exit;
}

$acts = $conn->query('SELECT id, title FROM activities ORDER BY date DESC');
$teachers = $conn->query("SELECT id, username FROM users WHERE role IN ('admin','teacher') ORDER BY username")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = trim($_POST['group_name'] ?? '');
    $activity_id = intval($_POST['activity_id'] ?? 0);

    if ($group_name !== '' && $activity_id > 0) {
        $stmt = $conn->prepare('INSERT INTO groups (activity_id, group_name) VALUES (?, ?)');
        $stmt->bind_param('is', $activity_id, $group_name);
        $stmt->execute();
        $group_id = $stmt->insert_id;
        $stmt->close();

        if (!empty($_POST['teacher_ids'])) {
            $ins = $conn->prepare("INSERT INTO group_members (group_id, user_id, role_in_group) VALUES (?, ?, 'teacher')");
            foreach ($_POST['teacher_ids'] as $uid) {
                $uid = intval($uid);
                $ins->bind_param('ii', $group_id, $uid);
                $ins->execute();
            }
            $ins->close();
        }

        $principal_id = $_SESSION['user_id'];
        $insPrincipal = $conn->prepare("INSERT INTO group_members (group_id, user_id, role_in_group) VALUES (?, ?, 'principal')");
        $insPrincipal->bind_param('ii', $group_id, $principal_id);
        $insPrincipal->execute();
        $insPrincipal->close();

        echo "<script>alert('Group created successfully.');location.href='groups.php';</script>";
        exit;
    }
}
?>

<h2 class="mb-4">Create Activity Group</h2>

<form method="POST" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Group Name</label>
    <input type="text" name="group_name" class="form-control" required>
  </div>

  <div class="col-md-6">
    <label class="form-label">Linked Activity</label>
    <select name="activity_id" class="form-select" required>
      <option value="">-- Select Activity --</option>
      <?php while ($a = $acts->fetch_assoc()): ?>
        <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['title']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="col-12">
    <label class="form-label">Add Teachers (multi-select)</label>

    <div class="input-group mb-2" style="max-width:400px;">
      <span class="input-group-text">Search</span>
      <input type="text" id="teacherSearch" class="form-control" placeholder="Search teacher username...">
    </div>

    <div class="table-responsive rounded p-2" style="max-height:300px;overflow:auto;border:1px solid #dee2e6;">
      <table class="table table-sm align-middle mb-0" id="teacherTable">
        <tbody>
          <?php foreach ($teachers as $t): ?>
            <tr>
              <td style="width:45px;">
                <input class="form-check-input" type="checkbox" name="teacher_ids[]" value="<?= (int)$t['id'] ?>">
              </td>
              <td><?= htmlspecialchars($t['username']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-12">
    <button class="btn btn-success">Create Group</button>
    <a href="dashboard_principal.php" class="btn btn-secondary">Back</a>
  </div>
</form>

<script>
const rows = Array.from(document.querySelectorAll('#teacherTable tbody tr'));
const input = document.getElementById('teacherSearch');
input.addEventListener('keyup', function () {
  const kw = input.value.trim().toLowerCase();
  rows.forEach(function (r) {
    const user = r.children[1].textContent.toLowerCase();
    r.style.display = user.includes(kw) ? '' : 'none';
  });
});
</script>

<?php include 'includes/footer.php'; ?>