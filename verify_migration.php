<?php
require_once 'app/lib/db_connect.php';

echo "=== Outcome Migration Verification ===\n\n";

// Check structure types
$query = "SELECT table_structure_type, COUNT(*) as count FROM sector_outcomes_data GROUP BY table_structure_type";
$result = $conn->query($query);
if ($result) {
    echo "Outcome structure types:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['table_structure_type']}: {$row['count']} records\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n";

// Check config status
$query = "SELECT id, table_name, 
    CASE WHEN row_config IS NOT NULL AND row_config != '' THEN 'Yes' ELSE 'No' END as has_row_config,
    CASE WHEN column_config IS NOT NULL AND column_config != '' THEN 'Yes' ELSE 'No' END as has_column_config
    FROM sector_outcomes_data ORDER BY id";
$result = $conn->query($query);
if ($result) {
    echo "Configuration status:\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID {$row['id']} ({$row['table_name']}): row_config={$row['has_row_config']}, column_config={$row['has_column_config']}\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n";

// Check data_json validity
$query = "SELECT id, table_name, JSON_VALID(data_json) as valid_json FROM sector_outcomes_data ORDER BY id";
$result = $conn->query($query);
if ($result) {
    echo "Data JSON validity:\n";
    while ($row = $result->fetch_assoc()) {
        $status = $row['valid_json'] ? 'Valid' : 'Invalid';
        echo "ID {$row['id']} ({$row['table_name']}): {$status}\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n=== Verification Complete ===\n";
?>
