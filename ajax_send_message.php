<?php
include 'auth_check.php';
include 'config.php';

$group_id = intval($_POST['group_id']);
$content  = trim($_POST['content'] ?? '');
$path = '';
$type = '';

if (!empty($_FILES['file']['name'])) {
    $filename = basename($_FILES['file']['name']);
    $target = "uploads/messages/" . time() . "_" . $filename;
    move_uploaded_file($_FILES['file']['tmp_name'], $target);
    $path = $target;
    $type = mime_content_type($target);
}

$stmt = $conn->prepare("INSERT INTO group_messages (group_id, sender_id, content, file_path, file_type)
                        VALUES (?,?,?,?,?)");
$stmt->bind_param("iisss", $group_id, $_SESSION['user_id'], $content, $path, $type);
$stmt->execute();
