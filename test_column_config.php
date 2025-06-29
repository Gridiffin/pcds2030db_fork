<?php
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';

// Check if there are column definitions in other fields
$query = 'SELECT metric_id, table_name, column_config, row_config, display_config FROM sector_outcomes_data WHERE metric_id = 7';
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo 'Column config: ' . ($row['column_config'] ?? 'null') . "\n";
    echo 'Row config: ' . ($row['row_config'] ?? 'null') . "\n";
    echo 'Display config: ' . ($row['display_config'] ?? 'null') . "\n";
}
?>
