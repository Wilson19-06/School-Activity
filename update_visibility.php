<?php
include 'auth_check.php';
include 'config.php';

if ($_SESSION['role'] !== 'principal') die('Access denied');

if (!empty($_POST['vis'])) {
    $upd = $conn->prepare('UPDATE activities SET visibility = ? WHERE id = ?');
    foreach ($_POST['vis'] as $id => $v) {
        $v = ($v === 'Private') ? 'Private' : 'Public';
        $id = intval($id);
        $upd->bind_param('si', $v, $id);
        $upd->execute();
    }
}

header('Location: manage_activity_visibility.php?saved=1');
exit;