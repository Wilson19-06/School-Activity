<?php
include 'auth_check.php';
include 'config.php';

if (!eams_is_staff()) {
    die('Access denied');
}

$id = intval($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$teacher = trim($_POST['teacher'] ?? '');
$type = trim($_POST['activity_type'] ?? '');
$location = trim($_POST['location'] ?? '');
$date = $_POST['date'] ?? '';
$objective = $_POST['objective'] ?? '';
$content = $_POST['content'] ?? '';
$follow_up = $_POST['follow_up'] ?? '';

if ($id === 0 || $title === '') {
    header('Location: edit_activity.php?id=' . $id . '&err=missing');
    exit;
}

$sql = "UPDATE activities SET
          title = ?, teacher = ?, activity_type = ?, location = ?, date = ?,
          objective = ?, content = ?, follow_up = ?
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'ssssssssi',
    $title,
    $teacher,
    $type,
    $location,
    $date,
    $objective,
    $content,
    $follow_up,
    $id
);
$ok = $stmt->execute();

$selected = $_POST['selected_students'] ?? [];
$conn->query('UPDATE students SET activity_id = NULL WHERE activity_id = ' . $id);

if (!empty($selected)) {
    $escaped = array_map([$conn, 'real_escape_string'], $selected);
    $in = "'" . implode("','", $escaped) . "'";
    $conn->query("UPDATE students SET activity_id = $id WHERE student_id IN ($in)");
}

if ($ok) {
    header('Location: view_activity.php?id=' . $id . '&updated=1');
} else {
    header('Location: edit_activity.php?id=' . $id . '&err=db');
}
exit;
?>