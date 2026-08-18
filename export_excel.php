<?php
require 'auth_check.php';
require 'config.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Activity List');

$sheet->fromArray(['No.', 'Activity Name', 'Type', 'Teacher', 'Date'], null, 'A1');

$sql = 'SELECT * FROM activities a WHERE ' . eams_visibility_sql('a') . ' ORDER BY a.created_at DESC';
$result = mysqli_query($conn, $sql);

$rowIndex = 2;
$i = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $rowIndex, $i++);
    $sheet->setCellValue('B' . $rowIndex, $row['title']);
    $sheet->setCellValue('C' . $rowIndex, $row['activity_type']);
    $sheet->setCellValue('D' . $rowIndex, $row['teacher']);
    $sheet->setCellValue('E' . $rowIndex, $row['date']);
    $rowIndex++;
}

$filename = 'activity_report_' . date('Ymd_His') . '.xlsx';

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
