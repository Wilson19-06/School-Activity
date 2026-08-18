<?php
include 'auth_check.php';
include 'config.php';

$group_id = intval($_GET['group_id'] ?? 0);
$res = $conn->query("SELECT gm.*, u.username FROM group_messages gm 
                     JOIN users u ON u.id=gm.sender_id
                     WHERE gm.group_id=$group_id 
                     ORDER BY gm.created_at ASC");

while ($row = $res->fetch_assoc()) {
    echo "<p><strong>{$row['username']}</strong>："
       . htmlspecialchars($row['content']);

    if ($row['file_path']) {
        $name = basename($row['file_path']);
        echo " <a href='{$row['file_path']}' target='_blank'>📎 $name</a>";
    }

    echo " <br><small class='text-muted'>{$row['created_at']}</small></p>";
}
