<?php
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';

echo "Current schema for sector_outcomes_data:\n";
$result = $conn->query('DESCRIBE sector_outcomes_data');
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . ($row['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . "\n";
}

echo "\nSample of current data structure:\n";
$sample = $conn->query('SELECT id, metric_id, table_name, data_json, row_config, column_config, table_structure_type FROM sector_outcomes_data LIMIT 2');
while ($row = $sample->fetch_assoc()) {
    echo "ID: " . $row['id'] . "\n";
    echo "  Table: " . $row['table_name'] . "\n";
    echo "  Structure Type: " . $row['table_structure_type'] . "\n";
    echo "  Row Config: " . (!empty($row['row_config']) ? 'Present' : 'Empty') . "\n";
    echo "  Column Config: " . (!empty($row['column_config']) ? 'Present' : 'Empty') . "\n";
    echo "  Data JSON (first 100 chars): " . substr($row['data_json'], 0, 100) . "...\n\n";
}
