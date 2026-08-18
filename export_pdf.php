<?php
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

require 'auth_check.php';
require 'config.php';
require_once 'vendor/autoload.php';
require_once __DIR__ . '/includes/pdf.php';

$mpdf = eams_mpdf([
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 28,
    'margin_bottom' => 15,
]);

$mpdf->SetTitle('Educational Activity Management System');

$sql = 'SELECT * FROM activities a WHERE a.title IS NOT NULL AND ' . eams_visibility_sql('a') . ' ORDER BY a.created_at DESC';
$result = mysqli_query($conn, $sql);

$html = '
<html>
<head>
<style>
body { font-family: notosanssc, notosanstc, sans-serif; color: #0b3954; }
h2 { text-align: center; color: #0b3954; }
table { border-collapse: collapse; width: 100%; font-size: 11pt; }
th, td { border: 1px solid #9bb8bc; padding: 6px 8px; }
th { background: #e8f4f6; font-weight: bold; }
</style>
</head>
<body>
<h2>Educational Activity Management System</h2>
<table autosize="1">
  <thead>
    <tr>
      <th width="40">#</th>
      <th width="170">Activity Name</th>
      <th width="110">Type</th>
      <th width="110">Teacher</th>
      <th width="80">Date</th>
    </tr>
  </thead>
  <tbody>';

$i = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $html .= '<tr>
        <td>' . $i++ . '</td>
        <td>' . htmlspecialchars($row['title']) . '</td>
        <td>' . htmlspecialchars($row['activity_type']) . '</td>
        <td>' . htmlspecialchars($row['teacher']) . '</td>
        <td>' . htmlspecialchars($row['date']) . '</td>
      </tr>';
}

$html .= '</tbody></table></body></html>';

$mpdf->WriteHTML($html);
$mpdf->Output('activity_report.pdf', \Mpdf\Output\Destination::INLINE);
exit;
