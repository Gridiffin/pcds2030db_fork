<?php
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';

try {
    echo "=== PROGRAMS TABLE STRUCTURE ===\n";
    $result = $conn->query('DESCRIBE programs');
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . $row['Key'] . ' | ' . $row['Default'] . "\n";
    }
    
    echo "\n=== PROGRAM_SUBMISSIONS TABLE STRUCTURE ===\n";
    $result = $conn->query('DESCRIBE program_submissions');
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . $row['Key'] . ' | ' . $row['Default'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
