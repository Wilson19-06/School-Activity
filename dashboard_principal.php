<?php
include 'auth_check.php';
include 'config.php';
include 'includes/header.php';

if ($_SESSION['role'] !== 'principal') {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    include 'includes/footer.php';
    exit;
}
?>

<style>
.card-link {
  text-decoration: none;
}
.card-func {
  transition: transform .2s, box-shadow .2s;
  border-radius: 1rem;
  min-height: 160px;
  max-width: 720px;
  margin: 0 auto;
}
.card-func:hover {
  transform: translateY(-8px);
  box-shadow: 0 .5rem 1.5rem rgba(11,57,84,.28);
}
.card-func .card-body {
  padding: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 2rem;
}
.card-title {
  font-size: 3rem;
  margin: 0;
}
.card-text {
  font-size: 1.8rem;
  font-weight: 600;
  margin: 0;
}
@media (max-width: 768px) {
  .card-func {
    min-height: 120px;
  }
  .card-func .card-body {
    padding: 1.2rem;
    gap: .8rem;
    flex-direction: column;
    align-items: flex-start;
  }
  .card-title {
    font-size: 1.6rem;
  }
  .card-text {
    font-size: 1rem;
  }
}
</style>

<h2 class="mb-4 text-center">Principal Dashboard</h2>
<p class="text-muted text-center">Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> (Principal)</p>

<div class="row g-4">
  <div class="col-12">
    <a href="manage_users.php" class="card-link">
      <div class="card text-white bg-primary card-func shadow">
        <div class="card-body">
          <div class="card-title">Users</div>
          <div class="card-text">Manage Users</div>
        </div>
      </div>
    </a>
  </div>

  <div class="col-12">
    <a href="view_all_activities.php" class="card-link">
      <div class="card text-white bg-primary card-func shadow">
        <div class="card-body">
          <div class="card-title">Activities</div>
          <div class="card-text">View All Activities</div>
        </div>
      </div>
    </a>
  </div>

  <div class="col-12">
    <a href="create_group.php" class="card-link">
      <div class="card text-white bg-primary card-func shadow">
        <div class="card-body">
          <div class="card-title">Groups</div>
          <div class="card-text">Create Group</div>
        </div>
      </div>
    </a>
  </div>

  <div class="col-12">
    <a href="register.php" class="card-link">
      <div class="card text-white bg-primary card-func shadow">
        <div class="card-body">
          <div class="card-title">Account</div>
          <div class="card-text">Create New User Account</div>
        </div>
      </div>
    </a>
  </div>
</div>

<div class="text-center mt-5">
  <a href="logout.php" class="btn btn-danger">Logout</a>
</div>

<?php include 'includes/footer.php'; ?>