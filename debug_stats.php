<?php
include 'config.php';

echo '<h2>Debug Statistics</h2>';

echo '<h3>Activities Table Data:</h3>';
$activities = $conn->query('SELECT * FROM activities LIMIT 10');
if ($activities) {
    echo '<p>Total returned rows: ' . $activities->num_rows . '</p>';
    while ($row = $activities->fetch_assoc()) {
        echo '<p>ID: ' . $row['id'] . ', Title: ' . $row['title'] . ', Role: ' . $row['created_by_role'] . ', Date: ' . $row['date'] . '</p>';
    }
} else {
    echo '<p>Query failed: ' . $conn->error . '</p>';
}

echo '<h3>Grouped Stats Query:</h3>';
$stats = $conn->query('SELECT created_by_role, COUNT(*) as count FROM activities WHERE date >= CURDATE() GROUP BY created_by_role');
if ($stats) {
    while ($row = $stats->fetch_assoc()) {
        echo '<p>' . $row['created_by_role'] . ': ' . $row['count'] . '</p>';
    }
} else {
    echo '<p>Stats query failed: ' . $conn->error . '</p>';
}

echo '<h3>Activities Table Structure:</h3>';
$result = $conn->query('DESCRIBE activities');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo '<p>' . $row['Field'] . ' - ' . $row['Type'] . '</p>';
    }
}
?>