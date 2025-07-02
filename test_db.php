<?php
require_once 'app/config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "=== Available Initiatives ===\n";
$result = $conn->query('SELECT initiative_id, initiative_name, start_date, end_date FROM initiatives LIMIT 5');
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['initiative_id']}, Name: {$row['initiative_name']}, Start: {$row['start_date']}, End: {$row['end_date']}\n";
}

echo "\n=== Available Programs ===\n";
$result = $conn->query('SELECT program_id, program_name, initiative_id FROM programs LIMIT 5');
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['program_id']}, Name: {$row['program_name']}, Initiative: {$row['initiative_id']}\n";
}

$conn->close();
?>
