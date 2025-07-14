<?php
/**
 * Database Names Helper
 * 
 * Provides easy access to table and column names from the centralized db_names.php configuration
 */

/**
 * Load database configuration
 * @return array The database configuration
 */
function load_db_config() {
    static $db_names = null;
    
    if ($db_names === null) {
        // Handle case where ROOT_PATH might not be defined yet
        if (!defined('ROOT_PATH')) {
            // Try to determine the path relative to this file
            $config_path = dirname(__DIR__) . '/config/db_names.php';
        } else {
            $config_path = ROOT_PATH . 'app/config/db_names.php';
        }
        
        error_log("DEBUG: Loading db_names from: " . $config_path);
        error_log("DEBUG: ROOT_PATH defined: " . (defined('ROOT_PATH') ? 'Yes' : 'No'));
        error_log("DEBUG: File exists: " . (file_exists($config_path) ? 'Yes' : 'No'));
        
        if (file_exists($config_path)) {
            $db_names = require_once $config_path;
            error_log("DEBUG: Loaded db_names successfully");
        } else {
            // Fallback to hardcoded values if config file not found
            error_log("DEBUG: Using fallback db_names configuration");
            $db_names = [
                'tables' => [
                    'reporting_periods' => 'reporting_periods',
                    'program_submissions' => 'program_submissions',
                    'sectors' => 'sectors',
                    'users' => 'users'
                ],
                'columns' => [
                    'reporting_periods' => [
                        'id' => 'period_id',
                        'year' => 'year',
                        'period_type' => 'period_type',
                        'period_number' => 'period_number',
                        'start_date' => 'start_date',
                        'end_date' => 'end_date',
                        'status' => 'status',
                        'created_at' => 'created_at',
                        'updated_at' => 'updated_at',
                    ],
                    'program_submissions' => [
                        'period_id' => 'period_id'
                    ],
                    'sectors' => [
                        'id' => 'sector_id'
                    ],
                    'users' => [
                        'id' => 'user_id',
                        'agency_name' => 'agency_name'
                    ]
                ]
            ];
        }
    }
    
    return $db_names;
}

/**
 * Get table name from the configuration
 * @param string $table_key The key for the table (e.g., 'reporting_periods')
 * @return string The actual table name
 */
function get_table_name($table_key) {
    $db_names = load_db_config();
    return $db_names['tables'][$table_key] ?? $table_key;
}

/**
 * Get column name from the configuration
 * @param string $table_key The key for the table (e.g., 'reporting_periods')
 * @param string $column_key The key for the column (e.g., 'id')
 * @return string The actual column name
 */
function get_column_name($table_key, $column_key) {
    $db_names = load_db_config();
    return $db_names['columns'][$table_key][$column_key] ?? $column_key;
}

/**
 * Get all column names for a table
 * @param string $table_key The key for the table
 * @return array Array of column mappings
 */
function get_table_columns($table_key) {
    $db_names = load_db_config();
    return $db_names['columns'][$table_key] ?? [];
}

/**
 * Build a SELECT query with proper table and column names
 * @param string $table_key The key for the table
 * @param array $columns Array of column keys to select (empty for all)
 * @param string $alias Optional table alias
 * @return string The SELECT query
 */
function build_select_query($table_key, $columns = [], $alias = '') {
    $table_name = get_table_name($table_key);
    $table_alias = $alias ?: $table_name;
    
    if (empty($columns)) {
        // Select all columns
        $column_list = '*';
    } else {
        $column_list = [];
        foreach ($columns as $column_key) {
            $column_name = get_column_name($table_key, $column_key);
            // Only add table alias if it's different from table name or explicitly provided
            if ($table_alias !== $table_name || $alias !== '') {
                $column_list[] = "{$table_alias}.{$column_name}";
            } else {
                $column_list[] = $column_name;
            }
        }
        $column_list = implode(', ', $column_list);
    }
    
    $query = "SELECT {$column_list} FROM {$table_name}";
    if ($table_alias !== $table_name || $alias !== '') {
        $query .= " {$table_alias}";
    }
    
    return $query;
}

/**
 * Build a WHERE clause with proper column names
 * @param string $table_key The key for the table
 * @param array $conditions Array of conditions [column_key => value]
 * @param string $alias Optional table alias
 * @return array [where_clause, params] The WHERE clause and parameters
 */
function build_where_clause($table_key, $conditions, $alias = '') {
    $table_alias = $alias ?: get_table_name($table_key);
    $where_parts = [];
    $params = [];
    
    foreach ($conditions as $column_key => $value) {
        $column_name = get_column_name($table_key, $column_key);
        $where_parts[] = "{$table_alias}.{$column_name} = ?";
        $params[] = $value;
    }
    
    $where_clause = implode(' AND ', $where_parts);
    return [$where_clause, $params];
}
?> 