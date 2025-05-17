<?php
/**
 * Migration Validation Script
 * 
 * Checks if the metrics to outcomes migration was successful
 * by comparing data in both sets of tables.
 * 
 * Usage: php validate_migration.php
 */

// Include database connection
require_once dirname(__DIR__) . '/includes/db_connect.php';

echo "=== PCDS 2030 Dashboard: Metrics to Outcomes Migration Validation ===\n\n";

// Check if the new tables exist
function checkTablesExist($conn) {
    echo "Checking if new tables exist...\n";
    
    $tables = ['outcomes_details', 'sector_outcomes_data'];
    $allExist = true;
    
    foreach ($tables as $table) {
        $query = "SHOW TABLES LIKE '$table'";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            echo "✓ Table $table exists\n";
        } else {
            echo "✗ Table $table does not exist\n";
            $allExist = false;
        }
    }
    
    return $allExist;
}

// Compare record counts between old and new tables
function compareRecordCounts($conn) {
    echo "\nComparing record counts...\n";
    
    $tablePairs = [
        ['metrics_details', 'outcomes_details'],
        ['sector_metrics_data', 'sector_outcomes_data']
    ];
    
    $allMatch = true;
    
    foreach ($tablePairs as $pair) {
        $oldTable = $pair[0];
        $newTable = $pair[1];
        
        $oldQuery = "SELECT COUNT(*) as count FROM $oldTable";
        $newQuery = "SELECT COUNT(*) as count FROM $newTable";
        
        $oldResult = $conn->query($oldQuery);
        $newResult = $conn->query($newQuery);
        
        if ($oldResult && $newResult) {
            $oldCount = $oldResult->fetch_assoc()['count'];
            $newCount = $newResult->fetch_assoc()['count'];
            
            if ($oldCount == $newCount) {
                echo "✓ $oldTable and $newTable both have $oldCount records\n";
            } else {
                echo "✗ Record count mismatch: $oldTable has $oldCount records, but $newTable has $newCount records\n";
                $allMatch = false;
            }
        } else {
            echo "✗ Error querying tables: " . $conn->error . "\n";
            $allMatch = false;
        }
    }
    
    return $allMatch;
}

// Compare data samples between old and new tables
function compareDataSamples($conn) {
    echo "\nComparing data samples...\n";
    
    $tablePairs = [
        ['metrics_details', 'outcomes_details', 'detail_id', 'detail_json'],
        ['sector_metrics_data', 'sector_outcomes_data', 'id', 'data_json']
    ];
    
    $allMatch = true;
    
    foreach ($tablePairs as $pair) {
        $oldTable = $pair[0];
        $newTable = $pair[1];
        $idCol = $pair[2];
        $dataCol = $pair[3];
        
        // Get sample of records to compare
        $query = "SELECT $idCol FROM $oldTable ORDER BY $idCol LIMIT 5";
        $result = $conn->query($query);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $id = $row[$idCol];
                
                $oldQuery = "SELECT $dataCol FROM $oldTable WHERE $idCol = $id";
                $newQuery = "SELECT $dataCol FROM $newTable WHERE $idCol = $id";
                
                $oldResult = $conn->query($oldQuery);
                $newResult = $conn->query($newQuery);
                
                if ($oldResult && $newResult) {
                    $oldData = $oldResult->fetch_assoc()[$dataCol];
                    $newData = $newResult->fetch_assoc()[$dataCol];
                    
                    if ($oldData === $newData) {
                        echo "✓ Record ID $id data matches between $oldTable and $newTable\n";
                    } else {
                        echo "✗ Record ID $id data mismatch between $oldTable and $newTable\n";
                        $allMatch = false;
                    }
                } else {
                    echo "✗ Error querying record ID $id: " . $conn->error . "\n";
                    $allMatch = false;
                }
            }
        } else {
            echo "✗ Error querying sample records: " . $conn->error . "\n";
            $allMatch = false;
        }
    }
    
    return $allMatch;
}

// Run validation checks
$tablesExist = checkTablesExist($conn);

if ($tablesExist) {
    $countsMatch = compareRecordCounts($conn);
    $dataMatch = compareDataSamples($conn);
    
    echo "\n=== Validation Summary ===\n";
    echo "Tables exist: " . ($tablesExist ? "✓ Yes" : "✗ No") . "\n";
    echo "Record counts match: " . ($countsMatch ? "✓ Yes" : "✗ No") . "\n";
    echo "Data samples match: " . ($dataMatch ? "✓ Yes" : "✗ No") . "\n";
    
    if ($tablesExist && $countsMatch && $dataMatch) {
        echo "\n✓✓✓ Migration validation PASSED. The data appears to have been migrated successfully.\n";
    } else {
        echo "\n✗✗✗ Migration validation FAILED. Please check the issues reported above.\n";
    }
} else {
    echo "\n✗✗✗ Migration validation FAILED. New tables do not exist.\n";
}

// Close connection
$conn->close();
?>
