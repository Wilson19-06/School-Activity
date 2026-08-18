<?php
include 'config.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$activity_id = intval($_POST['activity_id'] ?? 0);
$filename = $_FILES['student_file']['tmp_name'] ?? '';

if (!empty($_FILES['student_file']['size'])) {
    try {
        $spreadsheet = IOFactory::load($filename);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $counter = 1;
        $inserted = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $name = trim($row[0] ?? '');
            if ($name !== '') {
                $student_id = 'A' . $activity_id . '_STU' . str_pad($counter, 3, '0', STR_PAD_LEFT);

                $check_sql = 'SELECT id FROM students WHERE student_id = ?';
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param('s', $student_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows === 0) {
                    $sql = 'INSERT INTO students (student_id, name, activity_id) VALUES (?, ?, ?)';
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ssi', $student_id, $name, $activity_id);
                    $stmt->execute();
                    $inserted++;
                } else {
                    $skipped++;
                }

                $counter++;
            }
        }

        echo "<script>alert('Import completed: inserted $inserted students, skipped $skipped existing students.');window.location.href='view_activity.php?id=$activity_id';</script>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error reading file: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<script>alert('File is empty.'); window.history.back();</script>";
}
?>