<?php
session_start();
include 'config.php';

$title = trim($_POST['title'] ?? '');
$teacher = trim($_POST['teacher'] ?? '');
$type = trim($_POST['activity_type'] ?? '');
$location = trim($_POST['location'] ?? '');
$date = $_POST['date'] ?? '';
$objective = $_POST['objective'] ?? '';
$content = $_POST['content'] ?? '';
$follow_up = $_POST['follow_up'] ?? '';
$visibility = $_POST['visibility'] ?? 'Public';
$user_id = intval($_SESSION['user_id'] ?? 0);
$role = $_SESSION['role'] ?? 'unknown';
$created_by_role = $role;

if (!in_array($visibility, ['Public', 'Private'], true)) $visibility = 'Public';

$approved_status = $_POST['approved_status'] ?? 'Pending';
if (!in_array($approved_status, ['Approved', 'Pending', 'Rejected'], true)) $approved_status = 'Pending';

$sql = "INSERT INTO activities
        (title, teacher, activity_type, location, date, objective, content, follow_up, visibility, created_by, created_by_role, approved_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'sssssssssiss',
    $title,
    $teacher,
    $type,
    $location,
    $date,
    $objective,
    $content,
    $follow_up,
    $visibility,
    $user_id,
    $created_by_role,
    $approved_status
);
$stmt->execute();

$activity_id = $stmt->insert_id;
$stmt->close();

if (!empty($_POST['selected_students'])) {
    $upd = $conn->prepare('UPDATE students SET activity_id = ? WHERE id = ?');
    foreach ($_POST['selected_students'] as $sid) {
        $sid = intval($sid);
        $upd->bind_param('ii', $activity_id, $sid);
        $upd->execute();
    }
    $upd->close();
}

header('Location: view_activity.php?id=' . $activity_id);
exit;
?>