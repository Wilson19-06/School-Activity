<?php
include 'includes/header.php';

if (!isset($_GET['activity_id'])) {
    echo "<div class='alert alert-danger'>Error: Activity ID is missing.</div>";
    include 'includes/footer.php';
    exit;
}

$activity_id = $_GET['activity_id'];
?>
<h2 class="mb-4">Upload Student List (CSV)</h2>
<form action="save_students.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="activity_id" value="<?php echo $activity_id; ?>">
    <div class="mb-3">
        <label class="form-label">CSV File (student names only)</label>
        <input type="file" name="student_file" class="form-control" accept=".csv" required>
    </div>
    <button type="submit" class="btn btn-success">Upload and Save</button>
</form>
<?php include 'includes/footer.php'; ?>