<?php
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';

echo "Available programs:" . PHP_EOL;
$result = $conn->query('SELECT program_id, program_name FROM programs ORDER BY program_id');
while ($row = $result->fetch_assoc()) {
    echo "- ID: {$row['program_id']}, Name: {$row['program_name']}" . PHP_EOL;
}
?>
