<?php
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

require 'auth_check.php';
require 'config.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/pdf.php';

if (!isset($_GET['id'])) {
    die('Activity ID is required');
}
$id = intval($_GET['id']);

$res = $conn->query("SELECT a.*, u.username FROM activities a LEFT JOIN users u ON a.created_by=u.id WHERE a.id = $id");
$act = $res->fetch_assoc();
if (!$act) {
    die('Activity not found');
}
if (!eams_can_view_activity($act)) {
    die('This private activity is only visible to the principal.');
}

$stu_sql = "SELECT s.student_id, s.name_en, s.name_cn
            FROM activity_students ast
            JOIN students s ON ast.student_id = s.id
            WHERE ast.activity_id = $id";
$students = $conn->query($stu_sql);

$mpdf = eams_mpdf([
    'margin_top' => 40,
    'margin_bottom' => 30,
]);

$mpdf->SetTitle('Educational Activity Management System');

$date = date('Y-m-d');
$visibility = in_array(($act['visibility'] ?? ''), ['Public', 'Private'], true) ? $act['visibility'] : 'Public';

$status = $act['approved_status'] ?: 'Pending';
if (!in_array($status, ['Approved', 'Pending', 'Rejected'], true)) {
    $status = 'Pending';
}

$html = '
<html>
<head>
<style>
body { font-family: notosanssc, notosanstc, sans-serif; color: #0b3954; font-size: 12pt; }
h2 { text-align: center; }
</style>
</head>
<body>
<div style="text-align:center;">
    <span style="font-size:11pt;letter-spacing:0.08em;color:#087e8b;">EAMS</span><br>
    <span style="font-size:16pt;font-weight:bold;color:#0b3954;">Educational Activity Management System</span><br>
</div>
<hr style="margin:10px 0;">

<h2>' . htmlspecialchars($act['title']) . '</h2>

<table style="width:100%;margin-bottom:10px;">
  <tr><td><b>Type:</b> ' . htmlspecialchars($act['activity_type']) . '</td></tr>
  <tr><td><b>Date:</b> ' . htmlspecialchars($act['date']) . '</td></tr>
  <tr><td><b>Location:</b> ' . htmlspecialchars($act['location']) . '</td></tr>
  <tr><td><b>Teacher-in-charge:</b> ' . htmlspecialchars($act['teacher']) . '</td></tr>
  <tr><td><b>Visibility:</b> ' . htmlspecialchars($visibility) . '</td></tr>
  <tr><td><b>Status:</b> ' . htmlspecialchars($status) . '</td></tr>
</table>

<b>Objective</b><br><div>' . nl2br(htmlspecialchars($act['objective'])) . '</div><br>
<b>Content</b><br><div>' . nl2br(htmlspecialchars($act['content'])) . '</div><br>
<b>Follow-up Action</b><br><div>' . nl2br(htmlspecialchars($act['follow_up'])) . '</div><br>

<b>Participants</b><ul>';

if ($students && $students->num_rows > 0) {
    while ($s = $students->fetch_assoc()) {
        $html .= '<li>' . htmlspecialchars($s['student_id']) . ' - ' . htmlspecialchars($s['name_en']) . ' / ' . htmlspecialchars($s['name_cn']) . '</li>';
    }
} else {
    $html .= '<li>No students listed.</li>';
}

$html .= '</ul><br><br>

<div style="text-align:right;">
    <span style="font-size:12pt;">Principal</span><br>
    <span style="font-size:10pt;">Date: ' . $date . '</span>
</div>
</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output('activity_report_' . $id . '.pdf', \Mpdf\Output\Destination::INLINE);
exit;
