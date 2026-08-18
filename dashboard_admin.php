<?php
include 'auth_check.php';
include 'config.php';
include 'includes/header.php';

$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';
$visibilitySql = eams_visibility_sql('a');
$isTeacherView = eams_is_teacher();
$dashTitle = $isTeacherView ? 'Teacher Dashboard' : 'Admin Dashboard';
$roleLabel = $isTeacherView ? 'Teacher' : htmlspecialchars($_SESSION['role'] ?? '');

$stats_sql = "SELECT a.created_by_role, COUNT(*) AS count 
              FROM activities a
              WHERE a.date >= CURDATE()
                AND $visibilitySql
              GROUP BY a.created_by_role";
$stats_result = $conn->query($stats_sql);
$stat_admin = $stat_teacher = $stat_principal = 0;

if ($stats_result) {
    while ($stat = $stats_result->fetch_assoc()) {
        switch ($stat['created_by_role']) {
            case 'admin': $stat_admin = (int)$stat['count']; break;
            case 'teacher': $stat_teacher = (int)$stat['count']; break;
            case 'principal': $stat_principal = (int)$stat['count']; break;
        }
    }
}

$sql = "SELECT a.*, u.username AS creator_name
        FROM activities a
        LEFT JOIN users u ON u.id = a.created_by
        WHERE a.date >= CURDATE()
          AND $visibilitySql
        AND (a.title LIKE '%$keyword%' OR a.teacher LIKE '%$keyword%' OR a.activity_type LIKE '%$keyword%')
        ORDER BY a.created_at DESC";
$activities = mysqli_query($conn, $sql);
$allActivities = [];
while ($row = mysqli_fetch_assoc($activities)) $allActivities[] = $row;
?>

<h2>
  <?= htmlspecialchars($dashTitle) ?> <span class="badge bg-primary ms-2"><?= count($allActivities) ?> activities</span>
</h2>
<p class="text-muted">Welcome, <strong><?= htmlspecialchars($_SESSION['username']); ?></strong> (<?= htmlspecialchars($roleLabel) ?>)</p>

<!-- Search + Export -->
<div class="row g-3 mb-3 align-items-center">
  <div class="col-md-4">
    <form class="d-flex flex-column flex-sm-row" method="GET">
      <input type="text" name="keyword" class="form-control me-2"
                  placeholder="Search activity / teacher / type"
                  value="<?= htmlspecialchars($keyword) ?>">
                <button class="btn btn-outline-primary mt-2 mt-sm-0">Search</button>
    </form>
  </div>
  <div class="col-md-4">
    <a href="dashboard_admin.php" class="btn btn-outline-secondary">Reset</a>
  </div>
  <div class="col-md-4 text-start text-md-end">
    <a href="export_excel.php" class="btn btn-success mb-2 mb-md-0">Export Excel</a>
    <a href="export_pdf.php"   class="btn btn-danger">Export PDF</a>
  </div>
</div>

<a href="create_activity.php" class="btn btn-primary mb-3">New Activity</a>
<a href="manage_students.php" class="btn btn-outline-dark mb-3">Manage Students</a>

<!-- Activity Stats Chart -->
<div class="text-center mb-4">
  <canvas id="roleChart" height="220" style="max-width:400px;margin:auto;cursor:pointer;"></canvas>
</div>

<!-- Activity Cards -->
<div class="row" id="activityCards">
<?php foreach($allActivities as $row): ?>
  <div class="col-md-4 mb-4 activity-card" data-role="<?= $row['created_by_role'] ?>">
    <div class="card text-white bg-primary h-100 shadow card-hover position-relative">
      <a href="view_activity.php?id=<?= $row['id'] ?>" class="stretched-link text-decoration-none text-white"></a>
      <div class="card-body">
        <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
        <p class="card-text mb-1"><strong>Teacher</strong>: <?= htmlspecialchars($row['teacher']) ?></p>
        <p class="card-text mb-1"><strong>Type</strong>: <?= htmlspecialchars($row['activity_type']) ?></p>
        <p class="card-text mb-1"><strong>Date</strong>: <?= htmlspecialchars($row['date']) ?></p>
        <p class="card-text">
            <strong>Created By</strong>:
            <?= $row['created_by_role'] === 'principal' ? 'Principal' :
              ($row['creator_name'] ? htmlspecialchars($row['creator_name']) : 'Teacher') ?>
        </p>
        <span class="badge bg-light text-dark"><?= $row['created_by_role'] ?></span>
      </div>
      <div class="card-footer bg-transparent border-top-0 text-end position-relative" style="z-index:2;">
          <div class="mobile-actions justify-content-end">
            <a href="edit_activity.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning position-relative">Edit</a>
            <a href="delete_activity.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger position-relative"
              onclick="return confirm('Confirm delete?');">Delete</a>
          </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<a href="logout.php" class="btn btn-danger">Logout</a>

<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var ctx = document.getElementById('roleChart').getContext('2d');
  var labels = ["Admin", "Teacher", "Principal"];
  var roleChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: [<?= $stat_admin ?>, <?= $stat_teacher ?>, <?= $stat_principal ?>],
        backgroundColor: ['#087e8b', '#1f7a8c', '#0b3954'],
        borderColor: '#fff',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      legend: { position: 'bottom' },
      plugins: {
        datalabels: {
          color: '#fff',
          font: { weight: 'bold', size: 12 },
          display: function (ctx) {
            return Number(ctx.dataset.data[ctx.dataIndex]) > 0;
          },
          formatter: function (value, ctx) {
            return ctx.chart.data.labels[ctx.dataIndex] + '\n' + value;
          }
        }
      },
      onClick: function (e, elements) {
        if (elements.length > 0) {
          var index = elements[0]._index;
          var roleKeys = ['admin', 'teacher', 'principal'];
          var role = roleKeys[index];
          document.querySelectorAll('.activity-card').forEach(function(card) {
            card.style.display = card.dataset.role === role ? 'block' : 'none';
          });
        }
      }
    },
    plugins: [ChartDataLabels]
  });
});
</script>

<?php include 'includes/footer.php'; ?>