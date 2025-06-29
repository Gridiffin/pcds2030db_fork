<?php
/**
 * Migration: Convert all outcomes to flexible custom structure
 * 
 * This migration:
 * 1. Converts all classic outcomes to flexible outcomes
 * 2. Changes all structure types to 'custom'
 * 3. Preserves all existing data
 * 4. Generates appropriate row_config and column_config for existing data
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/db_connect.php';

class OutcomesMigration {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Execute the migration
     */
    public function migrate() {
        echo "Starting outcomes migration to unified custom structure...\n";
        
        // Start transaction
        $this->conn->begin_transaction();
        
        try {
            // Step 1: Backup current data
            $this->createBackup();
            
            // Step 2: Get all outcomes that need migration
            $outcomes = $this->getAllOutcomes();
            
            // Step 3: Process each outcome
            foreach ($outcomes as $outcome) {
                $this->migrateOutcome($outcome);
            }
            
            // Step 4: Clean up unused classic outcome creation files (handled separately)
            
            // Commit transaction
            $this->conn->commit();
            
            echo "Migration completed successfully!\n";
            echo "Total outcomes migrated: " . count($outcomes) . "\n";
            
        } catch (Exception $e) {
            // Rollback on error
            $this->conn->rollback();
            echo "Migration failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    /**
     * Create backup of current data
     */
    private function createBackup() {
        echo "Creating backup...\n";
        
        $backup_table = "sector_outcomes_data_backup_" . date('Y_m_d_H_i_s');
        
        $sql = "CREATE TABLE {$backup_table} AS SELECT * FROM sector_outcomes_data";
        
        if (!$this->conn->query($sql)) {
            throw new Exception("Failed to create backup table: " . $this->conn->error);
        }
        
        echo "Backup created: {$backup_table}\n";
    }
    
    /**
     * Get all outcomes that need migration
     */
    private function getAllOutcomes() {
        $sql = "SELECT * FROM sector_outcomes_data ORDER BY id";
        $result = $this->conn->query($sql);
        
        if (!$result) {
            throw new Exception("Failed to fetch outcomes: " . $this->conn->error);
        }
        
        $outcomes = [];
        while ($row = $result->fetch_assoc()) {
            $outcomes[] = $row;
        }
        
        return $outcomes;
    }
    
    /**
     * Migrate a single outcome to custom structure
     */
    private function migrateOutcome($outcome) {
        echo "Migrating outcome ID {$outcome['id']}: {$outcome['table_name']}\n";
        
        $new_row_config = null;
        $new_column_config = null;
        
        // Check if already has configurations
        if (!empty($outcome['row_config']) && !empty($outcome['column_config'])) {
            // Already has flexible structure, just change type to custom
            $new_row_config = $outcome['row_config'];
            $new_column_config = $outcome['column_config'];
        } else {
            // Generate configurations based on structure type and data
            $configs = $this->generateConfigurations($outcome);
            $new_row_config = $configs['row_config'];
            $new_column_config = $configs['column_config'];
        }
        
        // Update the outcome record
        $sql = "UPDATE sector_outcomes_data 
                SET table_structure_type = 'custom',
                    row_config = ?,
                    column_config = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $new_row_config, $new_column_config, $outcome['id']);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update outcome {$outcome['id']}: " . $stmt->error);
        }
        
        echo "  - Updated structure type to 'custom'\n";
        echo "  - Generated row_config and column_config\n";
    }
    
    /**
     * Generate row and column configurations based on existing data
     */
    private function generateConfigurations($outcome) {
        $structure_type = $outcome['table_structure_type'];
        $data_json = $outcome['data_json'];
        
        // Parse existing data to understand structure
        $data = json_decode($data_json, true);
        if (!$data) {
            $data = [];
        }
        
        // Generate row configuration based on structure type
        $row_config = $this->generateRowConfig($structure_type, $data);
        
        // Generate column configuration based on existing data
        $column_config = $this->generateColumnConfig($data);
        
        return [
            'row_config' => json_encode($row_config),
            'column_config' => json_encode($column_config)
        ];
    }
    
    /**
     * Generate row configuration
     */
    private function generateRowConfig($structure_type, $data) {
        $rows = [];
        
        switch ($structure_type) {
            case 'monthly':
                $months = [
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ];
                foreach ($months as $month) {
                    $rows[] = [
                        'id' => $month,
                        'type' => 'data',
                        'label' => $month
                    ];
                }
                break;
                
            case 'quarterly':
                for ($q = 1; $q <= 4; $q++) {
                    $rows[] = [
                        'id' => "Q{$q}",
                        'type' => 'data',
                        'label' => "Quarter {$q}"
                    ];
                }
                break;
                
            case 'yearly':
                // Extract years from data or use default range
                $current_year = date('Y');
                for ($y = 0; $y < 5; $y++) {
                    $year = $current_year + $y;
                    $rows[] = [
                        'id' => (string)$year,
                        'type' => 'data',
                        'label' => (string)$year
                    ];
                }
                break;
                
            case 'custom':
            default:
                // Try to extract row structure from existing data
                if (!empty($data)) {
                    $row_keys = array_keys($data);
                    foreach ($row_keys as $key) {
                        $rows[] = [
                            'id' => $key,
                            'type' => 'data',
                            'label' => ucfirst(str_replace('_', ' ', $key))
                        ];
                    }
                } else {
                    // Default to monthly if no data
                    $months = [
                        'January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'
                    ];
                    foreach ($months as $month) {
                        $rows[] = [
                            'id' => $month,
                            'type' => 'data',
                            'label' => $month
                        ];
                    }
                }
                break;
        }
        
        return ['rows' => $rows];
    }
    
    /**
     * Generate column configuration
     */
    private function generateColumnConfig($data) {
        $columns = [];
        
        if (!empty($data)) {
            // Extract column structure from first row of data
            $first_row = reset($data);
            if (is_array($first_row)) {
                foreach ($first_row as $col_key => $value) {
                    $columns[] = [
                        'id' => $col_key,
                        'type' => $this->detectColumnType($value),
                        'unit' => $this->detectColumnUnit($col_key, $value),
                        'label' => ucfirst(str_replace('_', ' ', $col_key))
                    ];
                }
            }
        }
        
        // If no columns detected, create a default numeric column
        if (empty($columns)) {
            $columns[] = [
                'id' => 'value',
                'type' => 'number',
                'unit' => '',
                'label' => 'Value'
            ];
        }
        
        return ['columns' => $columns];
    }
    
    /**
     * Detect column data type based on value
     */
    private function detectColumnType($value) {
        if (is_numeric($value)) {
            return 'number';
        }
        return 'text';
    }
    
    /**
     * Detect column unit based on key and value
     */
    private function detectColumnUnit($key, $value) {
        $key_lower = strtolower($key);
        
        if (strpos($key_lower, 'rm') !== false || strpos($key_lower, 'ringgit') !== false || strpos($key_lower, 'price') !== false || strpos($key_lower, 'cost') !== false) {
            return 'RM';
        }
        
        if (strpos($key_lower, 'percent') !== false || strpos($key_lower, '%') !== false) {
            return '%';
        }
        
        if (strpos($key_lower, 'hectare') !== false || strpos($key_lower, 'ha') !== false) {
            return 'ha';
        }
        
        return '';
    }
    
    /**
     * Rollback migration
     */
    public function rollback($backup_table_name) {
        echo "Rolling back migration...\n";
        
        // Restore from backup
        $sql = "DELETE FROM sector_outcomes_data";
        $this->conn->query($sql);
        
        $sql = "INSERT INTO sector_outcomes_data SELECT * FROM {$backup_table_name}";
        if ($this->conn->query($sql)) {
            echo "Rollback completed successfully.\n";
        } else {
            echo "Rollback failed: " . $this->conn->error . "\n";
        }
    }
}

// Execute migration if run directly
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    try {
        $migration = new OutcomesMigration($conn);
        $migration->migrate();
        echo "\nMigration completed successfully!\n";
    } catch (Exception $e) {
        echo "\nMigration failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}
