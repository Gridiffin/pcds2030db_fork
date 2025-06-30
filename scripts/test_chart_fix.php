<?php
require_once '../app/config/config.php';
require_once '../app/lib/db_connect.php';

// Test chart data preparation for Timber Export Value outcome
$outcome_id = 16;

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Get the outcome data
    $stmt = $conn->prepare("SELECT outcome_data FROM outcomes WHERE id = ?");
    $stmt->bind_param("i", $outcome_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $outcome = $result->fetch_assoc();
    
    if ($outcome && $outcome['outcome_data']) {
        $decoded_data = json_decode($outcome['outcome_data'], true);
        
        echo "=== Chart Data Test for Timber Export Value ===\n\n";
        
        // Extract data like the view_outcome.php does
        $columns = $decoded_data['columns'] ?? [];
        $data = $decoded_data['data'] ?? [];
        $row_labels = array_keys($data);
        
        echo "COLUMNS: " . implode(', ', $columns) . "\n";
        echo "ROWS: " . implode(', ', $row_labels) . "\n\n";
        
        echo "JAVASCRIPT DATA STRUCTURE:\n";
        echo "window.tableData = " . json_encode($data, JSON_PRETTY_PRINT) . ";\n";
        echo "window.tableColumns = " . json_encode($columns, JSON_PRETTY_PRINT) . ";\n";
        echo "window.tableRows = " . json_encode($row_labels, JSON_PRETTY_PRINT) . ";\n\n";
        
        echo "CHART DATA PREPARATION SIMULATION:\n";
        echo "Labels (rows): " . json_encode($row_labels) . "\n\n";
        
        foreach ($columns as $index => $column) {
            echo "Dataset for column '$column':\n";
            $dataset_data = [];
            foreach ($row_labels as $row) {
                $cellValue = isset($data[$row]) ? ($data[$row][$column] ?? null) : null;
                $numericValue = 0;
                if ($cellValue !== null && $cellValue !== '') {
                    $numericValue = floatval($cellValue);
                }
                $dataset_data[] = $numericValue;
                echo "  $row: $cellValue -> $numericValue\n";
            }
            echo "  Final data array: " . json_encode($dataset_data) . "\n\n";
        }
        
    } else {
        echo "No outcome found or no data available.";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
