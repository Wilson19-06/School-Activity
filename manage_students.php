<?php
include 'auth_check.php';
include 'config.php';
include 'includes/header.php';

if (!eams_is_staff()) {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    include 'includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['sid'] ?? 0);
    $student_id = trim($_POST['student_id'] ?? '');
    $name_en = trim($_POST['name_en'] ?? '');
    $name_cn = trim($_POST['name_cn'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $race = trim($_POST['race'] ?? '');

    if ($id === 0) {
        $stmt = $conn->prepare('INSERT INTO students (student_id, name_en, name_cn, class, gender, race) VALUES (?, ?, ?, ?, ?, ?)');
        if ($stmt) {
            $stmt->bind_param('ssssss', $student_id, $name_en, $name_cn, $class, $gender, $race);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        $stmt = $conn->prepare('UPDATE students SET student_id = ?, name_en = ?, name_cn = ?, class = ?, gender = ?, race = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('ssssssi', $student_id, $name_en, $name_cn, $class, $gender, $race, $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    header('Location: manage_students.php');
    exit;
}

if (isset($_GET['del'])) {
    $d = intval($_GET['del']);
    $stmt = $conn->prepare('DELETE FROM students WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $d);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: manage_students.php');
    exit;
}

$rows = $conn->query('SELECT * FROM students ORDER BY class, student_id');
?>

<h2 class="mb-4">Student Management</h2>

<form class="row g-3 mb-4 border rounded p-3 shadow-sm" method="POST">
  <input type="hidden" name="sid" value="0">
  <div class="col-md-3"><label class="form-label">Student ID</label><input name="student_id" class="form-control" required></div>
  <div class="col-md-3"><label class="form-label">English Name</label><input name="name_en" class="form-control" required></div>
  <div class="col-md-3"><label class="form-label">Chinese Name</label><input name="name_cn" class="form-control"></div>
  <div class="col-md-3"><label class="form-label">Class</label><input name="class" class="form-control" required></div>
  <div class="col-md-3"><label class="form-label">Gender</label>
    <select name="gender" class="form-select">
      <option value="Male">Male</option>
      <option value="Female">Female</option>
    </select>
  </div>
  <div class="col-md-3"><label class="form-label">Race</label><input name="race" class="form-control"></div>
  <div class="col-12"><button class="btn btn-primary">Save</button></div>
</form>

<form action="import_students.php" method="POST" enctype="multipart/form-data" class="mb-4">
  <div class="mb-2">
    <label class="form-label">Import Excel</label><br>
    <label class="btn btn-outline-primary">
      Choose File
      <input type="file" name="excel_file" hidden accept=".xlsx" onchange="updateFileLabel(this, 'excel_label')" required>
    </label>
    <span id="excel_label" class="ms-2 text-muted">No file selected</span>
  </div>
  <button class="btn btn-success" type="submit">Import Excel</button>
  <small class="text-muted d-block mt-2">Supports .xlsx. Required columns: student_id, name_en, name_cn, class, gender.</small>
</form>

<div class="table-responsive shadow rounded border">
<table class="table mb-0 mobile-card-table">
  <thead class="table-primary text-white">
    <tr>
      <th>#</th><th>Student ID</th><th>English Name</th><th>Chinese Name</th><th>Class</th><th>Gender</th><th>Race</th><th>Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php $i=1; while($s=$rows->fetch_assoc()): ?>
    <tr>
      <td><?= $i++ ?></td>
      <td><?= htmlspecialchars($s['student_id']) ?></td>
      <td><?= htmlspecialchars($s['name_en']) ?></td>
      <td><?= htmlspecialchars($s['name_cn']) ?></td>
      <td><?= htmlspecialchars($s['class']) ?></td>
      <td><?= htmlspecialchars($s['gender']) ?></td>
      <td><?= htmlspecialchars($s['race']) ?></td>
      <td>
        <div class="mobile-actions">
          <button class="btn btn-sm btn-warning" onclick='fillForm(<?= json_encode($s) ?>)'>Edit</button>
          <a href="?del=<?= (int)$s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Confirm delete?')">Delete</a>
        </div>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
</div>

<script>
function fillForm(stu) {
  document.querySelector('[name=sid]').value = stu.id;
  document.querySelector('[name=student_id]').value = stu.student_id;
  document.querySelector('[name=name_en]').value = stu.name_en;
  document.querySelector('[name=name_cn]').value = stu.name_cn;
  document.querySelector('[name=class]').value = stu.class;
  document.querySelector('[name=gender]').value = stu.gender;
  document.querySelector('[name=race]').value = stu.race;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateFileLabel(input, labelId) {
  const label = document.getElementById(labelId);
  if (input.files.length > 0) {
    label.textContent = input.files.length === 1 ? input.files[0].name : (input.files.length + ' files selected');
  } else {
    label.textContent = 'No file selected';
  }
}
</script>

<a href="<?= ($_SESSION['role'] === 'principal') ? 'dashboard_principal.php' : 'dashboard_admin.php' ?>" class="btn btn-secondary mt-4">Back</a>

<?php include 'includes/footer.php'; ?>