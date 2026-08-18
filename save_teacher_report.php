<?php
include 'config.php';

$name = $_POST['teacher_name'];
$title = $_POST['title'];
$type = $_POST['activity_type'];
$date = $_POST['date'];
$location = $_POST['location'];
$objective = $_POST['objective'];
$content = $_POST['content'];
$follow_up = $_POST['follow_up'];

$stmt1 = $conn->prepare("INSERT INTO teacher_reports
  (teacher_name, title, activity_type, date, location, objective, content, follow_up, status)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
$stmt1->bind_param('ssssssss', $name, $title, $type, $date, $location, $objective, $content, $follow_up);
$stmt1->execute();

$stmt2 = $conn->prepare("INSERT INTO activities
  (title, teacher, activity_type, location, date, objective, content, follow_up, visibility, created_by_role, status)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Public', 'teacher', 'Pending')");
$stmt2->bind_param('ssssssss', $title, $name, $type, $location, $date, $objective, $content, $follow_up);
$stmt2->execute();

header('Location: teacher_reports_list.php?success=1');
exit;
?>