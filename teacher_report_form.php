<?php
include 'auth_check.php';
include 'config.php';
$page_title = 'Fill in Activity Report';
include 'includes/header.php';
?>

<h2 class="mb-4">Fill in Activity Report</h2>
<form method="POST" action="save_teacher_report.php">
  <div class="mb-3">
    <label class="form-label">Teacher Name</label>
    <input type="text" name="teacher_name" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Activity Name</label>
    <input type="text" name="title" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Activity Type</label>
    <input type="text" name="activity_type" class="form-control">
  </div>
  <div class="mb-3">
    <label class="form-label">Activity Date</label>
    <input type="date" name="date" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Location</label>
    <input type="text" name="location" class="form-control">
  </div>
  <div class="mb-3">
    <label class="form-label">Objective</label>
    <textarea name="objective" class="form-control"></textarea>
  </div>
  <div class="mb-3">
    <label class="form-label">Content</label>
    <textarea name="content" class="form-control"></textarea>
  </div>
  <div class="mb-3">
    <label class="form-label">Follow Up</label>
    <textarea name="follow_up" class="form-control"></textarea>
  </div>
  <button class="btn btn-primary" type="submit">Submit Report</button>
</form>

<?php include 'includes/footer.php'; ?>
