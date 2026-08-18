<?php
include 'auth_check.php';
include 'config.php';

$teacherReports = $conn->query("SELECT 'teacher' AS source, id, teacher_name AS creator, title, date, status, content
                                FROM teacher_reports WHERE date >= CURDATE()");

$adminActivities = $conn->query("SELECT 'admin' AS source, id, teacher AS creator, title, date, approved_status AS status, content
                                 FROM activities
                                 WHERE created_by_role != 'teacher' AND date >= CURDATE()");

$allReports = [];
if ($teacherReports) {
    while ($row = $teacherReports->fetch_assoc()) $allReports[] = $row;
}
if ($adminActivities) {
    while ($row = $adminActivities->fetch_assoc()) $allReports[] = $row;
}

usort($allReports, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

$teacherCount = 0;
$adminCount = 0;
foreach ($allReports as $r) {
    if ($r['source'] === 'teacher') $teacherCount++;
    else $adminCount++;
}

$page_title = 'All Reports';
include 'includes/header.php';
?>

<h2 class="mb-4">All Reports</h2>

<div class="mb-4 text-end">
  <a href="teacher_report_form.php" class="btn btn-success">Submit New Report</a>
</div>

<div class="card mb-4 p-4 text-center">
  <h5 class="mb-3">Source Statistics</h5>
  <canvas id="reportChart" style="max-width:280px;max-height:280px;margin:0 auto;"></canvas>
</div>

<?php if (empty($allReports)): ?>
  <div class="alert alert-info">No report records.</div>
<?php else: ?>
  <div class="card p-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 mobile-card-table">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Source</th>
            <th>Teacher</th>
            <th>Activity Name</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; foreach ($allReports as $row): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td>
              <span class="badge bg-<?= $row['source'] === 'teacher' ? 'success' : 'primary' ?>">
                <?= $row['source'] === 'teacher' ? 'Teacher Submitted' : 'System Activity' ?>
              </span>
            </td>
            <td><?= htmlspecialchars($row['creator']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
              <a href="view_teacher_report.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  new Chart(document.getElementById('reportChart'), {
    type: 'doughnut',
    data: {
      labels: ['System Activity', 'Teacher Submitted'],
      datasets: [{
        data: [<?= $adminCount ?>, <?= $teacherCount ?>],
        backgroundColor: ['#087e8b', '#1f7a8c'],
        borderColor: ['#fff', '#fff'],
        borderWidth: 2,
        hoverOffset: 12
      }]
    },
    options: {
      animation: { animateRotate: true, duration: 1000 },
      plugins: {
        legend: {
          position: 'bottom',
          labels: { font: { size: 14 } }
        },
        datalabels: {
          color: '#fff',
          font: { weight: 'bold', size: 12 },
          display: function (ctx) {
            return Number(ctx.dataset.data[ctx.dataIndex]) > 0;
          },
          formatter: function (value, ctx) {
            const label = ctx.chart.data.labels[ctx.dataIndex];
            return label + '\n' + value;
          }
        },
        tooltip: {
          callbacks: {
            label: function (ctx) { return ctx.label + ': ' + ctx.formattedValue; }
          }
        }
      }
    },
    plugins: [ChartDataLabels]
  });
});
</script>

<?php include 'includes/footer.php'; ?>
