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
        // Always use absolute path relative to this file
        $config_path = __DIR__ . '/../config/db_names.php';
        if (file_exists($config_path)) {
            $db_names = require $config_path;
            error_log("[db_names_helper] Loaded db_names from: $config_path");
        } else {
            error_log("[db_names_helper] ERROR: db_names.php not found at $config_path");
            $db_names = [];
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
    if (isset($db_names['columns'][$table_key][$column_key])) {
        return $db_names['columns'][$table_key][$column_key];
    } else {
        error_log("[db_names_helper] Missing mapping for $table_key.$column_key, returning column_key as fallback");
        return $column_key;
    }
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