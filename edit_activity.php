<?php
include 'auth_check.php';
include 'config.php';
include 'includes/header.php';

if (!eams_is_staff()) {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    include 'includes/footer.php';
    exit;
}

if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>Activity ID is missing.</div>";
    include 'includes/footer.php';
    exit;
}

$activity_id = intval($_GET['id']);

$stmt = $conn->prepare('SELECT * FROM activities WHERE id = ?');
$stmt->bind_param('i', $activity_id);
$stmt->execute();
$activity = $stmt->get_result()->fetch_assoc();
if (!$activity) {
    echo "<div class='alert alert-danger'>Activity not found.</div>";
    include 'includes/footer.php';
    exit;
}

$selected = [];
$res = $conn->query('SELECT student_id FROM students WHERE activity_id = ' . $activity_id);
while ($row = $res->fetch_assoc()) {
    $selected[] = $row['student_id'];
}
?>

<h2 class="mb-4">Edit Activity</h2>

<form action="update_activity.php" method="POST">
  <input type="hidden" name="id" value="<?= $activity_id ?>">

  <div class="mb-3">
    <label class="form-label">Activity Name</label>
    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($activity['title']) ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Teacher in Charge</label>
    <input type="text" name="teacher" class="form-control" value="<?= htmlspecialchars($activity['teacher']) ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Activity Type</label>
    <input type="text" name="activity_type" class="form-control" value="<?= htmlspecialchars($activity['activity_type']) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Location</label>
    <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($activity['location']) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Activity Date</label>
    <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($activity['date']) ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Objective</label>
    <textarea name="objective" class="form-control" rows="2"><?= htmlspecialchars($activity['objective']) ?></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label">Content</label>
    <textarea name="content" class="form-control" rows="3"><?= htmlspecialchars($activity['content']) ?></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label">Follow Up</label>
    <textarea name="follow_up" class="form-control" rows="2"><?= htmlspecialchars($activity['follow_up']) ?></textarea>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Add / Remove Participating Students</h5>

      <div class="input-group mb-3">
        <input type="text" id="stuSearch" class="form-control" placeholder="Enter student ID or name keyword...">
        <button class="btn btn-outline-primary" type="button" id="btnSearch">Search</button>
      </div>

      <div class="table-responsive" style="max-height:320px; overflow:auto;">
        <table class="table table-sm table-bordered align-middle" id="resultTable">
          <thead class="table-light">
            <tr>
              <th style="width:45px;"></th>
              <th>Student ID</th>
              <th>Name</th>
              <th>Class</th>
              <th>Gender</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <button type="submit" class="btn btn-success">Save Changes</button>
  <a href="<?= ($_SESSION['role'] === 'principal') ? 'dashboard_principal.php' : 'dashboard_admin.php' ?>" class="btn btn-secondary ms-2">Back</a>
</form>

<script>
const preselected = <?= json_encode($selected) ?>;
const searchBox = document.getElementById('stuSearch');
const btnSearch = document.getElementById('btnSearch');
const tbody = document.querySelector('#resultTable tbody');

function render(rows) {
  tbody.innerHTML = rows.map(function (r) {
    const checked = preselected.includes(r.student_id) ? 'checked' : '';
    const fullName = (r.name_en ?? '') + ((r.name_cn ?? '') ? ' / ' + r.name_cn : '');
    return `
      <tr>
        <td><input type="checkbox" name="selected_students[]" value="${r.student_id}" ${checked}></td>
        <td>${r.student_id}</td>
        <td>${fullName}</td>
        <td>${r.class ?? ''}</td>
        <td>${r.gender ?? ''}</td>
      </tr>
    `;
  }).join('');
}

function doSearch() {
  const q = searchBox.value.trim();
  if (!q) {
    tbody.innerHTML = '';
    return;
  }

  fetch('search_students.php?q=' + encodeURIComponent(q))
    .then(function (r) { return r.json(); })
    .then(render)
    .catch(function (err) { console.error(err); });
}

btnSearch.addEventListener('click', doSearch);
searchBox.addEventListener('keyup', function (e) {
  if (e.key === 'Enter') {
    doSearch();
  } else if (e.target.value.length === 1) {
    doSearch();
  }
});
</script>

<?php include 'includes/footer.php'; ?>