<?php
include 'auth_check.php';
include 'config.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['excel_file'])) {
    header('Location: manage_students.php');
    exit;
}

$file = $_FILES['excel_file'];
if ($file['error'] !== UPLOAD_ERR_OK) die('Upload failed.');
if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'xlsx') die('Only .xlsx files are supported.');

$tmp = $file['tmp_name'];
$spreadsheet = IOFactory::load($tmp);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);

$inserted = 0;
$stmt = $conn->prepare(
  'INSERT INTO students (student_id, name_en, name_cn, class, gender)
   VALUES (?, ?, ?, ?, ?)
   ON DUPLICATE KEY UPDATE
     name_en = VALUES(name_en), name_cn = VALUES(name_cn),
     class = VALUES(class), gender = VALUES(gender)'
);

foreach ($rows as $idx => $row) {
    if ($idx === 1) continue;
    [$id, $en, $cn, $class, $gender] = array_values($row);
    if (!$id) continue;

    $stmt->bind_param('sssss', $id, $en, $cn, $class, $gender);
    if ($stmt->execute()) $inserted++;
}

header('Location: manage_students.php?imported=' . $inserted);
exit;
?>