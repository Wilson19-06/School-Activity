<?php
$id = intval($_GET['id']);
// Query database and return activity details
echo json_encode([
  'id' => $id,
  'title' => 'Sports Day',
  'date' => '2024-06-01',
  'location' => 'School Field',
  'teacher' => 'Mr. Lee',
  'objectives' => 'Promote teamwork and fitness.',
  'student_count' => 120,
  'photos' => [
    'uploads/gambar/photo1.jpg',
    'uploads/gambar/photo2.jpg'
  ]
]);