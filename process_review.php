<?php
session_start();
include 'config.php';

if (!in_array($_SESSION['role'], ['principal', 'admin'])) {
    die('No permission to review');
}

$activity_id = intval($_POST['activity_id'] ?? 0);
$action = $_POST['action'] ?? '';
$status = ($action === 'approve') ? 'Approved' : 'Rejected';

$stmt = $conn->prepare('UPDATE activities SET status = ? WHERE id = ?');
$stmt->bind_param('si', $status, $activity_id);
$stmt->execute();

header('Location: view_activity.php?id=' . $activity_id);
exit;
?>