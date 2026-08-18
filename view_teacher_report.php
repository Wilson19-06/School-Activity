<?php
include 'auth_check.php';
include 'config.php';

if (!isset($_GET['id'])) {
  echo "<div class='alert alert-danger'>Activity not found.</div>";
  exit;
}

$id = intval($_GET['id']);
$data = null;
$source = '';

$stmt1 = $conn->prepare('SELECT * FROM teacher_reports WHERE id = ?');
$stmt1->bind_param('i', $id);
$stmt1->execute();
$res1 = $stmt1->get_result();
if ($res1->num_rows > 0) {
  $data = $res1->fetch_assoc();
  $source = 'teacher';
} else {
  $stmt2 = $conn->prepare('SELECT * FROM activities WHERE id = ?');
  $stmt2->bind_param('i', $id);
  $stmt2->execute();
  $res2 = $stmt2->get_result();
  if ($res2->num_rows > 0) {
    $row = $res2->fetch_assoc();
    if (!eams_can_view_activity($row)) {
      $page_title = 'Activity Details';
      include 'includes/header.php';
      echo "<div class='alert alert-warning'>This private activity is only visible to the principal.</div>";
      include 'includes/footer.php';
      exit;
    }
    $data = $row;
    $source = 'admin';
  }
}

if (!$data) {
  $page_title = 'Activity Details';
  include 'includes/header.php';
  echo "<div class='alert alert-danger'>Activity not found.</div>";
  include 'includes/footer.php';
  exit;
}

$status = $data['status'] ?? ($data['approved_status'] ?? 'Pending');
if (!in_array($status, ['Approved', 'Pending', 'Rejected'], true)) $status = 'Pending';

$page_title = 'Activity Details';
include 'includes/header.php';
?>

<h2 class="mb-4">Activity Details</h2>

<div class="table-responsive shadow border rounded bg-white">
  <table class="table table-bordered mb-0">
    <tbody>
      <tr><th class="bg-primary text-white">Activity Name</th><td><?= htmlspecialchars($data['title']) ?></td></tr>
      <tr><th class="bg-primary text-white">Teacher</th><td><?= htmlspecialchars($source === 'teacher' ? ($data['teacher_name'] ?? '') : ($data['teacher'] ?? '')) ?></td></tr>
      <tr><th class="bg-primary text-white">Type</th><td><?= htmlspecialchars($data['activity_type'] ?? '') ?></td></tr>
      <tr><th class="bg-primary text-white">Date</th><td><?= htmlspecialchars($data['date'] ?? '') ?></td></tr>

      <?php if (isset($data['objective'])): ?>
      <tr><th class="bg-primary text-white">Objective</th><td><?= nl2br(htmlspecialchars($data['objective'])) ?></td></tr>
      <?php endif; ?>

      <tr><th class="bg-primary text-white">Content</th><td><?= nl2br(htmlspecialchars($data['content'] ?? '')) ?></td></tr>

      <?php if (isset($data['follow_up'])): ?>
      <tr><th class="bg-primary text-white">Follow Up</th><td><?= nl2br(htmlspecialchars($data['follow_up'])) ?></td></tr>
      <?php endif; ?>

      <?php if ($source !== 'teacher' && isset($data['location'])): ?>
      <tr><th class="bg-primary text-white">Location</th><td><?= htmlspecialchars($data['location']) ?></td></tr>
      <?php endif; ?>

      <tr><th class="bg-primary text-white">Status</th><td><?= htmlspecialchars($status) ?></td></tr>
    </tbody>
  </table>
</div>

<a href="teacher_reports_list.php" class="btn btn-secondary mt-4">Back</a>

<?php include 'includes/footer.php'; ?>
