<?php
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';

echo "=== CURRENT PROGRAMS TABLE DATA ===\n";
$result = $conn->query('SELECT program_id, program_name, description, extended_data FROM programs ORDER BY program_id DESC LIMIT 3');
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['program_id'] . "\n";
    echo "Name: " . $row['program_name'] . "\n";
    echo "Description: " . ($row['description'] ?: 'NULL') . "\n";
    echo "Extended Data: " . ($row['extended_data'] ?: 'NULL') . "\n";
    echo "---\n";
}

echo "\n=== CURRENT PROGRAM_SUBMISSIONS TABLE DATA ===\n";
$result = $conn->query('SELECT submission_id, program_id, content_json FROM program_submissions ORDER BY submission_id DESC LIMIT 3');
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['submission_id'] . "\n";
        echo "Program ID: " . $row['program_id'] . "\n";
        echo "Content JSON: " . ($row['content_json'] ?: 'NULL') . "\n";
        echo "---\n";
    }
} else {
    echo "No records found\n";
}
?>
