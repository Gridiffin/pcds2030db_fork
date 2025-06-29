<?php
/**
 * Fix Outcome Data Structure Migration
 * 
 * This script properly aligns the data_json with the new row_config and column_config
 * that were created during the initial migration to custom structure.
 */

require_once __DIR__ . '/../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';

class OutcomeDataStructureFixer {
    
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function run() {
        echo "=== Outcome Data Structure Fix ===\n";
        echo "Fixing data alignment with new row/column configs...\n\n";
        
        try {
            // Get all outcomes that need data structure fixes
            $outcomes = $this->getAllOutcomes();
            
            echo "Found " . count($outcomes) . " outcomes to process.\n\n";
            
            foreach ($outcomes as $outcome) {
                $this->fixOutcomeDataStructure($outcome);
            }
            
            echo "\n=== Data Structure Fix Complete ===\n";
            echo "All outcomes have been updated with properly aligned data structures.\n";
            
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    private function getAllOutcomes() {
        $query = "SELECT id, table_name, data_json, row_config, column_config 
                  FROM sector_outcomes_data 
                  WHERE table_structure_type = 'custom'
                  ORDER BY id";
        
        $result = $this->conn->query($query);
        $outcomes = [];
        
        while ($row = $result->fetch_assoc()) {
            $outcomes[] = $row;
        }
        
        return $outcomes;
    }
    
    private function fixOutcomeDataStructure($outcome) {
        $id = $outcome['id'];
        $table_name = $outcome['table_name'];
        
        echo "Processing: {$table_name} (ID: {$id})\n";
        
        // Parse the current configs
        $data_json = json_decode($outcome['data_json'], true);
        $row_config = json_decode($outcome['row_config'], true);
        $column_config = json_decode($outcome['column_config'], true);
        
        if (!$data_json || !$row_config || !$column_config) {
            echo "  ⚠️ Skipping - Invalid JSON data\n";
            return;
        }
        
        // Fix the data structure
        $fixed_data = $this->alignDataWithConfigs($data_json, $row_config, $column_config);
        $fixed_column_config = $this->fixColumnConfig($data_json, $column_config);
        
        // Update the database
        $this->updateOutcome($id, $fixed_data, $fixed_column_config);
        
        echo "  ✅ Updated data structure\n";
    }
    
    private function alignDataWithConfigs($data_json, $row_config, $column_config) {
        $fixed_data = [];
        
        // Get original columns from data_json
        $original_columns = $data_json['columns'] ?? [];
        
        // Get original data
        $original_data = $data_json['data'] ?? [];
        
        // Map data using row IDs and column indices
        foreach ($row_config['rows'] as $row) {
            $row_id = $row['id'];
            $row_data = [];
            
            // For each column in the original data
            for ($col_index = 0; $col_index < count($original_columns); $col_index++) {
                $original_col_name = $original_columns[$col_index];
                
                // Get the value from original data structure
                if (isset($original_data[$row_id][$original_col_name])) {
                    $row_data[$col_index] = $original_data[$row_id][$original_col_name];
                } else {
                    $row_data[$col_index] = null;
                }
            }
            
            $fixed_data[$row_id] = $row_data;
        }
        
        return $fixed_data;
    }
    
    private function fixColumnConfig($data_json, $column_config) {
        $original_columns = $data_json['columns'] ?? [];
        $original_units = $data_json['units'] ?? [];
        
        $fixed_columns = [];
        
        foreach ($original_columns as $index => $column_name) {
            $fixed_columns[] = [
                'id' => $index,
                'type' => 'number',
                'unit' => $original_units[$column_name] ?? '',
                'label' => $column_name
            ];
        }
        
        return ['columns' => $fixed_columns];
    }
    
    private function updateOutcome($id, $fixed_data, $fixed_column_config) {
        $data_json = json_encode($fixed_data);
        $column_config_json = json_encode($fixed_column_config);
        
        $query = "UPDATE sector_outcomes_data 
                  SET data_json = ?, column_config = ? 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssi", $data_json, $column_config_json, $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update outcome ID {$id}: " . $stmt->error);
        }
    }
}

// Run the migration
try {
    $fixer = new OutcomeDataStructureFixer($conn);
    $fixer->run();
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
