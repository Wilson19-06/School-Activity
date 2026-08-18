<?php
include 'auth_check.php';
include 'config.php';
include 'includes/header.php';

if (!eams_is_principal()) {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    include 'includes/footer.php';
    exit;
}

$filter_role = $_GET['role'] ?? '';

$stat_admin = 0;
$stat_teacher = 0;
$stat_principal = 0;
$stats = $conn->query("SELECT created_by_role, COUNT(*) as count FROM activities WHERE date >= CURDATE() GROUP BY created_by_role");
if ($stats) {
    while ($row = $stats->fetch_assoc()) {
        switch ($row['created_by_role']) {
            case 'admin': $stat_admin = (int)$row['count']; break;
            case 'teacher': $stat_teacher = (int)$row['count']; break;
            case 'principal': $stat_principal = (int)$row['count']; break;
        }
    }
}
$total_all = $stat_admin + $stat_teacher + $stat_principal;

$role_filter_sql = $filter_role ? "AND a.created_by_role = '" . mysqli_real_escape_string($conn, $filter_role) . "'" : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if (isset($_POST['delete_activity']) && $id > 0) {
        $stmt = $conn->prepare('DELETE FROM activities WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    if (isset($_POST['toggle_visibility']) && $id > 0) {
        $current = $_POST['current_visibility'] ?? 'Public';
        $next = ($current === 'Public') ? 'Private' : 'Public';
        $stmt = $conn->prepare('UPDATE activities SET visibility = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('si', $next, $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    if (isset($_POST['approve_activity']) && $id > 0) {
        $status = $_POST['approve_activity'];
        if (in_array($status, ['Approved', 'Rejected'], true)) {
            $stmt = $conn->prepare('UPDATE activities SET approved_status = ? WHERE id = ?');
            if ($stmt) {
                $stmt->bind_param('si', $status, $id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    $redirect = 'view_all_activities.php';
    if ($filter_role !== '') {
        $redirect .= '?role=' . urlencode($filter_role);
    }
    header('Location: ' . $redirect);
    exit;
}

$res = $conn->query("SELECT a.*
                    FROM activities a
                    WHERE a.date >= CURDATE() $role_filter_sql
                    ORDER BY a.date DESC");
?>

<h2 class="mb-4">All Activities</h2>

<canvas id="roleChart" height="220" style="max-width:400px;margin:auto;cursor:pointer;" class="mb-4"></canvas>

<div class="alert alert-info d-flex flex-wrap gap-3">
  <div><strong>Total Activities</strong>: <?= $total_all ?></div>
  <div><strong>Admin</strong>: <?= $stat_admin ?></div>
  <div><strong>Teacher</strong>: <?= $stat_teacher ?></div>
  <div><strong>Principal</strong>: <?= $stat_principal ?></div>
</div>

<div class="table-responsive shadow-sm border rounded">
  <table class="table align-middle table-hover mb-0 mobile-card-table">
    <thead class="table-dark text-white">
      <tr>
        <th>#</th>
        <th>Activity Name</th>
        <th>Type</th>
        <th>Teacher</th>
        <th>Date</th>
        <th>Visibility</th>
        <th>Review Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $i = 1;
    if ($res):
    while ($row = $res->fetch_assoc()):
      if (($row['created_by_role'] ?? '') === 'principal') {
        $final_status = 'Approved';
      } elseif (!empty($row['approved_status'])) {
        $final_status = $row['approved_status'];
      } else {
        $final_status = $row['status'] ?? 'Pending';
      }

      $visibility = in_array($row['visibility'] ?? '', ['Public', 'Private'], true) ? $row['visibility'] : 'Public';
      $statusForBadge = in_array($final_status, ['Approved', 'Pending', 'Rejected'], true) ? $final_status : 'Pending';
    ?>
    <tr>
      <td><?= $i++ ?></td>
      <td><?= htmlspecialchars($row['title']) ?></td>
      <td><?= htmlspecialchars($row['activity_type']) ?></td>
      <td><?= htmlspecialchars($row['teacher']) ?></td>
      <td><?= htmlspecialchars($row['date']) ?></td>
      <td>
        <span class="badge bg-<?= $visibility === 'Public' ? 'success' : 'secondary' ?>">
          <?= htmlspecialchars($visibility) ?>
        </span>
      </td>
      <td>
        <span class="badge bg-<?= $statusForBadge === 'Approved' ? 'success' : ($statusForBadge === 'Rejected' ? 'danger' : 'warning') ?>">
          <?= htmlspecialchars($statusForBadge) ?>
        </span>
      </td>
      <td>
        <div class="mobile-actions">
          <?php if ($row['created_by_role'] === 'principal'): ?>
            <form method="POST" class="d-inline-flex gap-1 m-0">
              <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
              <button name="approve_activity" value="Approved" class="btn btn-sm btn-outline-success">Approve</button>
              <button name="approve_activity" value="Rejected" class="btn btn-sm btn-outline-danger">Reject</button>
            </form>
          <?php endif; ?>

          <form method="POST" class="m-0">
            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
            <input type="hidden" name="current_visibility" value="<?= htmlspecialchars($visibility) ?>">
            <input type="hidden" name="toggle_visibility" value="1">
            <button type="submit" class="btn btn-sm btn-outline-<?= $visibility === 'Public' ? 'warning' : 'success' ?>">
              Toggle to <?= $visibility === 'Public' ? 'Private' : 'Public' ?>
            </button>
          </form>

          <a href="view_activity.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-info text-white">View</a>

          <form method="POST" class="m-0" onsubmit="return confirm('Confirm delete this activity?')">
            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
            <button type="submit" name="delete_activity" class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </div>
      </td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>

<a href="view_all_activities.php" class="btn btn-outline-secondary mt-4">Reset</a>
<a href="dashboard_principal.php" class="btn btn-secondary mt-4">Back</a>

<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const chartElement = document.getElementById('roleChart');
  if (!chartElement) return;

  const ctx = chartElement.getContext('2d');
  const labels = ['Admin', 'Teacher', 'Principal'];

  new Chart(ctx, {
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
      onClick: function (e, elements) {
        if (elements.length > 0) {
          const index = elements[0]._index;
          const roles = ['admin', 'teacher', 'principal'];
          window.location.href = 'view_all_activities.php?role=' + roles[index];
        }
      },
      plugins: {
        datalabels: {
          color: '#fff',
          font: { weight: 'bold', size: 12 },
          display: function (ctx) {
            return Number(ctx.dataset.data[ctx.dataIndex]) > 0;
          },
          formatter: function (value, context) {
            return context.chart.data.labels[context.dataIndex] + '\n' + value;
          }
        }
      }
    },
    plugins: [ChartDataLabels]
  });
});
</script>

<?php include 'includes/footer.php'; ?>