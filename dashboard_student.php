<?php
include 'auth_check.php';
include 'config.php';
include 'includes/header.php';

$student_name = $_SESSION['username'];

$sql = "SELECT a.title, a.date, a.teacher, a.activity_type
        FROM students s
        JOIN activities a ON s.activity_id = a.id
        WHERE s.name = ?
          AND (a.visibility IS NULL OR a.visibility <> 'Private')";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $student_name);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>My Activities</h2>
<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle mobile-card-table">
        <thead class="table-light">
            <tr>
                <th>Activity Name</th>
                <th>Type</th>
                <th>Date</th>
                <th>Teacher in Charge</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['activity_type']); ?></td>
                <td><?php echo htmlspecialchars($row['date']); ?></td>
                <td><?php echo htmlspecialchars($row['teacher']); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<a href="logout.php" class="btn btn-danger">Logout</a>
<?php include 'includes/footer.php'; ?>