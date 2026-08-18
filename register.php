<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $role     = $_POST['role'] ?? 'admin';

  if ($username === '' || $password === '') {
    $_SESSION['flash_msg'] = 'Username and password cannot be empty.';
    $_SESSION['flash_class'] = 'danger';
  } else {
    $chk = $conn->prepare('SELECT id FROM users WHERE username = ?');
    $chk->bind_param('s', $username);
    $chk->execute();
    $chk->store_result();

    if ($chk->num_rows > 0) {
      $_SESSION['flash_msg'] = 'Username already exists. Please choose a different one.';
      $_SESSION['flash_class'] = 'danger';
    } else {
      $hashPwd = password_hash($password, PASSWORD_DEFAULT);
      $ins = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
      $ins->bind_param('sss', $username, $hashPwd, $role);
      if ($ins->execute()) {
        $_SESSION['flash_msg'] = 'Account registered successfully! You may now log in.';
        $_SESSION['flash_class'] = 'success';
      } else {
        $_SESSION['flash_msg'] = 'Registration failed. Please try again.';
        $_SESSION['flash_class'] = 'danger';
      }
      $ins->close();
    }
    $chk->close();
  }

  header('Location: register.php');
  exit;
}

$msg = $_SESSION['flash_msg'] ?? '';
$msgClass = $_SESSION['flash_class'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_class']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | Educational Activity Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/auth.css" rel="stylesheet">
</head>
<body>
  <div class="login-shell wide">
    <div class="row g-0">
      <div class="col-lg-5">
        <div class="left-panel">
          <span class="hero-badge">EAMS</span>
          <h1>Create Account</h1>
          <p>Register a new teacher, student, or principal account to access the Educational Activity Management System.</p>
          <a href="login.php" class="back-link">← Back to Login Page</a>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="right-panel">
          <a href="login.php" class="back-link">&larr; Back to Login</a>
          <h2 class="section-title lg">Register</h2>
          <p class="section-sub">Fill in the details below to create a new account.</p>
          <?php if ($msg !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($msgClass) ?> alert-dismissible fade show" role="alert">
              <?= htmlspecialchars($msg) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>
          <form method="POST" action="register.php">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-4">
              <label class="form-label">Role</label>
              <select name="role" class="form-select" required>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
                <option value="principal">Principal</option>
              </select>
            </div>
            <button type="submit" class="btn btn-register">Create Account</button>
            <a href="login.php" class="btn btn-outline-secondary w-100 mt-3" style="border-radius:13px;padding:13px 18px;">Back to Login</a>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
