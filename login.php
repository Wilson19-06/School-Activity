<?php
session_start();
include 'config.php';

$error_message = '';
$show_form = false;

if (!empty($_SESSION['flash_msg'])) {
    $flash_msg = $_SESSION['flash_msg'];
    $flash_class = $_SESSION['flash_class'] ?? 'success';
    unset($_SESSION['flash_msg'], $_SESSION['flash_class']);
} else {
    $flash_msg = '';
    $flash_class = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $show_form = true;

    if ($username === '' || $password === '') {
        $error_message = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare('SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'principal') {
                    header('Location: dashboard_principal.php');
                } elseif ($user['role'] === 'student') {
                    header('Location: dashboard_student.php');
                } else {
                    header('Location: dashboard_admin.php');
                }
                exit;
            }

            $error_message = 'Invalid username or password.';
            $stmt->close();
        } else {
            $error_message = 'Unable to process login right now.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Login | Educational Activity Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/auth.css" rel="stylesheet">
</head>
<body>
    <div class="login-shell <?= $show_form ? 'expanded' : '' ?>" id="loginShell">
        <div class="row-wrap">
            <div class="left-panel">
                <span class="hero-badge">EAMS</span>
                <h1>Educational Activity Management System</h1>
                <a href="admin_login.php" class="quick-btn">Admin Login</a>
                <a href="principal_login.php" class="quick-btn">Principal Login</a>
                <button type="button" class="quick-btn" onclick="showAccountForm()">Account Login (Database)</button>
                <a href="register.php" class="quick-btn">Register New Account</a>
            </div>
            <div class="right-panel" id="rightPanel">
                <div class="right-inner">
                    <a href="#" class="back-link" onclick="hideForm(); return false;">&larr; Back</a>
                    <h2 class="section-title">Account Login</h2>
                    <p class="section-sub">For teacher, student, or principal accounts in the database.</p>
                    <?php if ($flash_msg !== ''): ?>
                        <div class="alert alert-<?= htmlspecialchars($flash_class) ?>"><?= htmlspecialchars($flash_msg) ?></div>
                    <?php endif; ?>
                    <?php if ($error_message !== ''): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input id="username" type="text" name="username" class="form-control" value="ws" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" type="password" name="password" class="form-control" value="123" required>
                        </div>
                        <button type="submit" class="btn btn-login">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const shell = document.getElementById('loginShell');
        function showAccountForm() { shell.classList.add('expanded'); }
        function hideForm() { shell.classList.remove('expanded'); }
        <?php if ($show_form): ?>
        document.addEventListener('DOMContentLoaded', function () { shell.classList.add('expanded'); });
        <?php endif; ?>
    </script>
</body>
</html>
