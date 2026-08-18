<?php
include 'auth_check.php';
include 'config.php';
include 'includes/header.php';

if ($_SESSION['role'] !== 'principal') {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    include 'includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = trim($_POST['new_username'] ?? '');
    $passwordPlain = trim($_POST['new_password'] ?? '');
    $role = ($_POST['new_role'] ?? 'teacher') === 'principal' ? 'principal' : 'teacher';

    if ($username !== '' && $passwordPlain !== '') {
        $password = password_hash($passwordPlain, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        if ($stmt) {
            $stmt->bind_param('sss', $username, $password, $role);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: manage_users.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $uid = intval($_POST['uid'] ?? 0);
    $new = ($_POST['new_role'] ?? 'admin') === 'principal' ? 'principal' : 'admin';
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ? AND role <> 'principal'");
    if ($stmt) {
        $stmt->bind_param('si', $new, $uid);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: manage_users.php');
    exit;
}

if (isset($_GET['delete'])) {
    $d = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role <> 'principal'");
    if ($stmt) {
        $stmt->bind_param('i', $d);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: manage_users.php');
    exit;
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$stmt = $conn->prepare("SELECT id, username, role FROM users
                        WHERE role IN ('admin','teacher','principal')
                        AND (username LIKE CONCAT('%', ?, '%') OR role LIKE CONCAT('%', ?, '%'))
                        ORDER BY role DESC, username");
$stmt->bind_param('ss', $q, $q);
$stmt->execute();
$res = $stmt->get_result();

$principals = [];
$teachers = [];
while ($u = $res->fetch_assoc()) {
    if ($u['role'] === 'principal') {
        $principals[] = $u;
    } else {
        $teachers[] = $u;
    }
}
?>

<h2 class="mb-4">User Management (Teachers & Principals only)</h2>

<div class="card border shadow-sm mb-4">
  <div class="card-header bg-success text-white">Create New Account</div>
  <div class="card-body">
    <form method="POST" class="row g-3">
      <input type="hidden" name="create_user" value="1">
      <div class="col-md-4">
        <label class="form-label">Username</label>
        <input type="text" name="new_username" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Password</label>
        <input type="password" name="new_password" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Role</label>
        <select name="new_role" class="form-select">
          <option value="teacher">Teacher</option>
          <option value="principal">Principal</option>
        </select>
      </div>
      <div class="col-md-1 d-grid align-items-end">
        <button class="btn btn-success">Create</button>
      </div>
    </form>
  </div>
</div>

<form class="row g-3 mb-3" method="GET">
  <div class="col-md-6">
    <input name="q" class="form-control" placeholder="Search username / role" value="<?= htmlspecialchars($q) ?>">
  </div>
  <div class="col-md-2">
    <button class="btn btn-outline-primary">Search</button>
    <a href="manage_users.php" class="btn btn-outline-secondary">Reset</a>
  </div>
</form>

<h4 class="mt-4">Principals (<?= count($principals) ?>)</h4>
<div class="table-responsive shadow rounded border mb-4">
<table class="table mb-0 mobile-card-table">
 <thead class="table-primary text-white">
   <tr>
     <th>#</th>
     <th>Username</th>
     <th>Role</th>
     <th>Actions</th>
   </tr>
 </thead>
 <tbody>
 <?php $i=1; foreach($principals as $p): ?>
   <tr>
     <td><?= $i++ ?></td>
     <td><?= htmlspecialchars($p['username']) ?></td>
     <td>Principal</td>
     <td><span class="text-muted">Cannot modify / delete</span></td>
   </tr>
 <?php endforeach; ?>
 </tbody>
</table>
</div>

<h4 class="mt-4">Teachers (<?= count($teachers) ?>)</h4>
<div class="table-responsive shadow rounded border">
<table class="table mb-0 mobile-card-table">
 <thead class="table-primary text-white">
   <tr>
     <th>#</th>
     <th>Username</th>
     <th>Change Role</th>
     <th>Actions</th>
   </tr>
 </thead>
 <tbody>
 <?php $i=1; foreach($teachers as $t): ?>
   <tr>
     <td><?= $i++ ?></td>
     <td><?= htmlspecialchars($t['username']) ?></td>
     <td>
       <div class="mobile-actions">
         <form method="POST" class="m-0">
           <input type="hidden" name="uid" value="<?= (int)$t['id'] ?>">
           <input type="hidden" name="new_role" value="principal">
           <button name="change_role" class="btn btn-sm btn-info">Set as Principal</button>
         </form>
       </div>
     </td>
     <td>
       <div class="mobile-actions">
         <a href="?delete=<?= (int)$t['id'] ?>" class="btn btn-sm btn-danger"
            onclick="return confirm('Confirm delete?')">Delete</a>
       </div>
     </td>
   </tr>
 <?php endforeach; ?>
 </tbody>
</table>
</div>

<a href="dashboard_principal.php" class="btn btn-secondary mt-4">Back</a>

<?php include 'includes/footer.php'; ?>