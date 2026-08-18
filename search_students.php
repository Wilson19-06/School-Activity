<?php
include 'config.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') { echo json_encode([]); exit; }

$sql = "SELECT id, student_id, name_en, name_cn, class, gender
        FROM students
        WHERE student_id   LIKE CONCAT('%', ?, '%')
           OR name_en      LIKE CONCAT('%', ?, '%')
           OR name_cn      LIKE CONCAT('%', ?, '%')
        ORDER BY class, student_id
        LIMIT 50";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $q, $q, $q);
$stmt->execute();
$res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo json_encode($res);
