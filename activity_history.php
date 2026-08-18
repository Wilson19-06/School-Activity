<?php
include 'auth_check.php';
include 'config.php';
include 'includes/header.php';

date_default_timezone_set('Asia/Kuala_Lumpur');
$today = date('Y-m-d');
$keyword = trim($_GET['keyword'] ?? '');
$visibilitySql = eams_visibility_sql('a');

$sql = "
  SELECT a.*, u.username AS creator_name
  FROM activities a
  LEFT JOIN users u ON u.id = a.created_by
  WHERE a.date < ?
    AND $visibilitySql
    AND (? = '' OR a.title LIKE ? OR a.teacher LIKE ? OR a.activity_type LIKE ?
         OR a.location LIKE ? OR u.username LIKE ?)
  ORDER BY a.date DESC
";
$stmt = $conn->prepare($sql);
$like = '%' . $keyword . '%';
$stmt->bind_param('sssssss', $today, $keyword, $like, $like, $like, $like, $like);
$stmt->execute();
$result = $stmt->get_result();
$count = $result ? $result->num_rows : 0;
?>

<h2 class="mb-4">
  Activity History
  <span class="badge bg-primary ms-2"><?= (int)$count ?></span>
</h2>

<div class="row g-3 mb-4 align-items-center">
  <div class="col-md-6">
    <form class="d-flex flex-column flex-sm-row" method="GET" action="activity_history.php">
      <input type="text" name="keyword" class="form-control me-2"
             placeholder="Search activity / teacher / type / location"
             value="<?= htmlspecialchars($keyword) ?>">
      <button class="btn btn-outline-primary mt-2 mt-sm-0" type="submit">Search</button>
    </form>
  </div>
  <div class="col-md-6">
    <a href="activity_history.php" class="btn btn-outline-secondary">Reset</a>
  </div>
</div>

<?php if (!$result || $count === 0): ?>
  <div class="alert alert-info"><?= $keyword !== '' ? 'No matching historical activities.' : 'No historical activities.' ?></div>
<?php else: ?>
  <div class="row">
    <?php while ($row = $result->fetch_assoc()): ?>
    <div class="col-md-4 mb-4">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
          <p class="card-text mb-1"><strong>Teacher</strong>: <?= htmlspecialchars($row['teacher']) ?></p>
          <p class="card-text mb-1"><strong>Type</strong>: <?= htmlspecialchars($row['activity_type']) ?></p>
          <p class="card-text mb-1"><strong>Date</strong>: <?= htmlspecialchars($row['date']) ?></p>
          <p class="card-text"><strong>Created By</strong>: <?= $row['creator_name'] ? htmlspecialchars($row['creator_name']) : 'Teacher' ?></p>
          <span class="badge bg-secondary"><?= htmlspecialchars($row['created_by_role'] ?? 'admin') ?></span>
        </div>
        <div class="card-footer bg-transparent text-end">
          <div class="mobile-actions justify-content-end">
            <a href="view_activity.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
          </div>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
