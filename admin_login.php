<?php
session_start();
include 'config.php';

$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if ($password === 'admin123') {
        session_regenerate_id(true);
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        header('Location: dashboard_admin.php');
        exit;
    } else {
        $error_message = 'Incorrect password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Educational Activity Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/auth.css" rel="stylesheet">
</head>
<body>
    <div class="login-shell wide">
        <div class="row g-0">
            <div class="col-lg-5">
                <div class="left-panel">
                    <span class="hero-badge">EAMS</span>
                    <h1>Admin Login</h1>
                    <p>Access the teacher control panel to manage activities, students, and reports.</p>
                    <a href="login.php" class="back-link">← Back to Login Page</a>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="right-panel">
                    <h2 class="section-title lg">Welcome Back</h2>
                    <p class="section-sub">Enter the admin password to continue.</p>
                    <?php if ($error_message !== ''): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="password" class="form-label">Admin Password</label>
                            <input id="password" type="password" name="password"
                                   class="form-control" placeholder="Enter admin password" value="admin123" required>
                        </div>
                        <button type="submit" class="btn btn-login">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
