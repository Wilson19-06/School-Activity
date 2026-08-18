<?php
include 'auth_check.php';
include 'config.php';

$activity_id = intval($_POST['activity_id']);

// Upload activity photos
if (!empty($_FILES['gambar_files']['name'][0])) {
    foreach ($_FILES['gambar_files']['tmp_name'] as $index => $tmpPath) {
        $name = $_FILES['gambar_files']['name'][$index];
        $target = 'uploads/gambar/' . uniqid() . '_' . basename($name);
        if (move_uploaded_file($tmpPath, $target)) {
            $stmt = $conn->prepare("INSERT INTO files (activity_id, file_type, file_path) VALUES (?, 'gambar', ?)");
            $stmt->bind_param("is", $activity_id, $target);
            $stmt->execute();
        }
    }
}

// Upload certificates / awards
if (!empty($_FILES['sijil_files']['name'][0])) {
    foreach ($_FILES['sijil_files']['tmp_name'] as $index => $tmpPath) {
        $name = $_FILES['sijil_files']['name'][$index];
        $target = 'uploads/sijil/' . uniqid() . '_' . basename($name);
        if (move_uploaded_file($tmpPath, $target)) {
            $stmt = $conn->prepare("INSERT INTO files (activity_id, file_type, file_path) VALUES (?, 'sijil', ?)");
            $stmt->bind_param("is", $activity_id, $target);
            $stmt->execute();
        }
    }
}

header("Location: view_activity.php?id=$activity_id");
exit;
