<?php
include 'auth_check.php';
include 'config.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>Activity ID is missing.</div>";
    include 'includes/footer.php';
    exit;
}

$activity_id = intval($_GET['id']);
$ps = $conn->prepare('SELECT * FROM activities WHERE id = ?');
$ps->bind_param('i', $activity_id);
$ps->execute();
$activity = $ps->get_result()->fetch_assoc();

if (!$activity) {
    echo "<div class='alert alert-danger'>Activity not found.</div>";
    include 'includes/footer.php';
    exit;
}

if (!eams_can_view_activity($activity)) {
    echo "<div class='alert alert-warning'>This private activity is only visible to the principal.</div>";
    include 'includes/footer.php';
    exit;
}

$ps2 = $conn->prepare('SELECT student_id, COALESCE(name_en,name) AS name_en, name_cn, class, gender FROM students WHERE activity_id = ?');
$ps2->bind_param('i', $activity_id);
$ps2->execute();
$students = $ps2->get_result();

$statusToShow = 'Pending';
if (!empty($activity['approved_status'])) {
    $statusToShow = $activity['approved_status'];
} elseif (!empty($activity['status'])) {
    $statusToShow = $activity['status'];
}

if ($activity['created_by_role'] === 'principal') {
    $statusToShow = 'Approved';
}

if (!in_array($statusToShow, ['Approved', 'Pending', 'Rejected'], true)) {
  $statusToShow = 'Pending';
}

$badgeClass = ($statusToShow === 'Approved') ? 'success' : (($statusToShow === 'Rejected') ? 'danger' : 'secondary');
?>

<a href="export_activity_pdf.php?id=<?= $activity_id ?>" class="btn btn-danger mb-3">Export PDF</a>
<a href="export_activity_excel.php?id=<?= $activity_id ?>" class="btn btn-success mb-3 ms-2">Export Excel</a>

<h2 class="mb-4">Activity Details</h2>

<div class="mb-3">
  <h5><span class="badge bg-<?= $badgeClass ?>"><?= htmlspecialchars($statusToShow) ?></span></h5>
</div>

<div class="table-responsive shadow rounded border">
  <table class="table mb-0">
    <tbody>
      <tr><th class="bg-primary text-white">Activity Name</th><td><?= htmlspecialchars($activity['title']) ?></td></tr>
      <tr><th class="bg-primary text-white">Teacher in Charge</th><td><?= htmlspecialchars($activity['teacher']) ?></td></tr>
      <tr><th class="bg-primary text-white">Activity Type</th><td><?= htmlspecialchars($activity['activity_type']) ?></td></tr>
      <tr><th class="bg-primary text-white">Date</th><td><?= htmlspecialchars($activity['date']) ?></td></tr>
      <tr><th class="bg-primary text-white">Objective</th><td><?= nl2br(htmlspecialchars($activity['objective'])) ?></td></tr>
      <tr><th class="bg-primary text-white">Content</th><td><?= nl2br(htmlspecialchars($activity['content'])) ?></td></tr>
      <tr><th class="bg-primary text-white">Follow Up</th><td><?= nl2br(htmlspecialchars($activity['follow_up'])) ?></td></tr>
      <tr><th class="bg-primary text-white">Location</th><td><?= htmlspecialchars($activity['location']) ?></td></tr>
    </tbody>
  </table>
</div>

<h4 class="mt-4">Student List</h4>
<div class="table-responsive shadow rounded border">
  <table class="table mb-0">
    <thead class="bg-primary text-white">
      <tr>
        <th>#</th>
        <th>Student ID</th>
        <th>English Name</th>
        <th>Chinese Name</th>
        <th>Class</th>
        <th>Gender</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; while ($row = $students->fetch_assoc()): ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($row['student_id']) ?></td>
        <td><?= htmlspecialchars($row['name_en']) ?></td>
        <td><?= htmlspecialchars($row['name_cn']) ?></td>
        <td><?= htmlspecialchars($row['class']) ?></td>
        <td><?= htmlspecialchars($row['gender']) ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<h4 class="mt-5">Upload Certificates and Photos</h4>
<form action="upload_files.php" method="POST" enctype="multipart/form-data" class="mb-4">
  <input type="hidden" name="activity_id" value="<?= $activity_id ?>">

  <div class="mb-3">
    <label class="form-label">Activity Photos</label><br>
    <label class="btn btn-outline-primary">
      Choose Files
      <input type="file" name="gambar_files[]" hidden multiple accept="image/*" onchange="updateFileLabel(this, 'gambar_label')">
    </label>
    <span id="gambar_label" class="ms-2 text-muted">No file chosen</span>
  </div>

  <div class="mb-3">
    <label class="form-label">Certificates / Awards</label><br>
    <label class="btn btn-outline-primary">
      Choose Files
      <input type="file" name="sijil_files[]" hidden multiple accept=".pdf,.jpg,.png" onchange="updateFileLabel(this, 'sijil_label')">
    </label>
    <span id="sijil_label" class="ms-2 text-muted">No file chosen</span>
  </div>

  <button type="submit" class="btn btn-success">Upload Files</button>
</form>

<h4 class="mt-5">Uploaded Activity Photos</h4>
<div class="row">
<?php
$g = $conn->prepare("SELECT file_path FROM files WHERE activity_id = ? AND file_type='gambar'");
$g->bind_param('i', $activity_id);
$g->execute();
$rG = $g->get_result();
if ($rG->num_rows === 0) {
    echo "<p class='text-muted'>No activity photos uploaded yet.</p>";
} else {
    while ($f = $rG->fetch_assoc()) {
        echo "<div class='col-md-3 mb-3'><img src='" . htmlspecialchars($f['file_path']) . "' class='img-thumbnail' style='height:150px;object-fit:cover;'></div>";
    }
}
?>
</div>

<h4 class="mt-4">Uploaded Certificates / Awards</h4>
<ul class="list-group mb-5">
<?php
$s = $conn->prepare("SELECT file_path FROM files WHERE activity_id = ? AND file_type='sijil'");
$s->bind_param('i', $activity_id);
$s->execute();
$rS = $s->get_result();
if ($rS->num_rows === 0) {
    echo "<li class='list-group-item text-muted'>No certificates uploaded yet.</li>";
} else {
    while ($f = $rS->fetch_assoc()) {
        $fn = basename($f['file_path']);
        echo "<li class='list-group-item'><a href='" . htmlspecialchars($f['file_path']) . "' target='_blank'>" . htmlspecialchars($fn) . "</a></li>";
    }
}
?>
</ul>

<?php
$role = $_SESSION['role'] ?? '';
$back_url = ($role === 'principal') ? 'dashboard_principal.php' : 'dashboard_admin.php';
?>
<a href="<?= $back_url ?>" class="btn btn-secondary mb-5">Back</a>

<script>
function updateFileLabel(input, labelId) {
  const label = document.getElementById(labelId);
  label.textContent = input.files.length === 0 ? 'No file chosen' : (input.files.length + ' file(s) selected');
}
</script>

<?php include 'includes/footer.php'; ?>