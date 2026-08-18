<?php
require 'auth_check.php';
require 'config.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_GET['id'])) {
    die('Activity ID is required');
}
$id = intval($_GET['id']);

$sql = 'SELECT * FROM activities WHERE id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$activity = $stmt->get_result()->fetch_assoc();
if (!$activity) {
    die('Activity not found');
}
if (!eams_can_view_activity($activity)) {
    die('This private activity is only visible to the principal.');
}

$students = [];
$student_sql = 'SELECT student_id, COALESCE(name_en, name) AS name FROM students WHERE activity_id = ?';
$stmt2 = $conn->prepare($student_sql);
$stmt2->bind_param('i', $id);
$stmt2->execute();
$result2 = $stmt2->get_result();
while ($row = $result2->fetch_assoc()) {
    $students[] = $row;
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Activity Report');

$sheet->setCellValue('A1', 'Activity Name')->setCellValue('B1', $activity['title']);
$sheet->setCellValue('A2', 'Type')->setCellValue('B2', $activity['activity_type']);
$sheet->setCellValue('A3', 'Teacher')->setCellValue('B3', $activity['teacher']);
$sheet->setCellValue('A4', 'Date')->setCellValue('B4', $activity['date']);
$sheet->setCellValue('A6', 'Objective')->setCellValue('B6', $activity['objective']);
$sheet->setCellValue('A7', 'Content')->setCellValue('B7', $activity['content']);
$sheet->setCellValue('A8', 'Follow Up')->setCellValue('B8', $activity['follow_up']);

$sheet->setCellValue('A10', 'Participants');
$sheet->setCellValue('A11', 'No.')->setCellValue('B11', 'Student ID')->setCellValue('C11', 'Name');

$row = 12;
$i = 1;
foreach ($students as $s) {
    $sheet->setCellValue('A' . $row, $i++);
    $sheet->setCellValue('B' . $row, $s['student_id']);
    $sheet->setCellValue('C' . $row, $s['name']);
    $row++;
}

$filename = 'activity_' . $id . '_report.xlsx';

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
