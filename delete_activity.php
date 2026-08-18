<?php
include 'auth_check.php';
include 'config.php';

if (!isset($_GET['id'])) {
    die('Error: Activity ID is missing.');
}

$activity_id = intval($_GET['id']);

$check = $conn->prepare('SELECT * FROM activities WHERE id = ?');
$check->bind_param('i', $activity_id);
$check->execute();
$activity = $check->get_result()->fetch_assoc();
if (!$activity || !eams_can_view_activity($activity)) {
    die('You do not have permission to delete this activity.');
}

$sql = 'DELETE FROM activities WHERE id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $activity_id);
if ($stmt->execute()) {
    header('Location: dashboard_admin.php?deleted=1');
    exit;
}

echo 'Delete failed: ' . $stmt->error;
?>