<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';

switch ($role) {
  case 'admin':
  case 'teacher':   $home_link = 'dashboard_admin.php';     break;
  case 'principal': $home_link = 'dashboard_principal.php'; break;
  case 'student':   $home_link = 'dashboard_student.php';   break;
  default:          $home_link = '#';
}

$page_title = $page_title ?? 'Educational Activity Management System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($page_title) ?> | Educational Activity Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/theme.css" rel="stylesheet">
</head>
<body>

<?php if (!in_array($current_page,['login.php','admin_login.php','principal_login.php','register.php'])): ?>
<nav class="navbar navbar-dark navbar-eams fixed-top px-3">
  <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#sideMenu">
    <span class="navbar-toggler-icon"></span>
  </button>
  <span class="navbar-brand ms-2">Educational Activity Management System</span>
</nav>

<div class="offcanvas offcanvas-top offcanvas-eams" tabindex="-1" id="sideMenu">
  <div class="offcanvas-header">
    <h4 class="offcanvas-title">Navigation Menu</h4>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">
    <div class="list-group list-group-flush">
      <a href="<?= $home_link ?>"              class="list-group-item">Home</a>
      <?php if ($role==='principal'): ?>
        <a href="manage_users.php"             class="list-group-item">Manage Users</a>
        <a href="view_all_activities.php"      class="list-group-item">All Activities</a>
        <a href="create_group.php"             class="list-group-item">Create Group</a>
        <a href="groups.php"                   class="list-group-item">Group List</a>
      <?php endif; ?>
      <?php if (in_array($role,['admin','teacher','principal'])): ?>
        <a href="manage_students.php"          class="list-group-item">Manage Students</a>
        <a href="create_activity.php"          class="list-group-item">Create Activity</a>
        <a href="my_reports.php"               class="list-group-item">My Reports</a>
        <a href="groups_teacher.php"           class="list-group-item">My Groups</a>
        <a href="activity_history.php"         class="list-group-item">Activity History</a>
        <a href="facebook_template.php"        class="list-group-item">Facebook Template</a>
      <?php endif; ?>
      <a href="logout.php"                     class="list-group-item">Logout</a>
    </div>
  </div>
</div>
<main class="app-main">
  <div class="app-shell">
<?php endif; ?>
