<?php
/**
 * Agency-specific functions
 * 
 * Functions used by agency users to manage programs and submit data.
 */

/**
 * Check if current user is an agency
 * @return boolean
 */
function is_agency() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return false;
    }
    return $_SESSION['role'] === 'agency';
}

/**
 * Get programs owned by current agency
 * @return array List of programs
 */
function get_agency_programs() {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    // Logic to get agency's programs would go here
    
    return []; // Placeholder
}

/**
 * Submit program data for current reporting period
 * @param array $data Program submission data
 * @return array Result of submission
 */
function submit_program_data($data) {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    // Logic to submit program data would go here
    
    return ['success' => true]; // Placeholder
}

/**
 * Create new metric for agency's sector
 * @param array $metric_data Metric definition
 * @return array Result of creation
 */
function create_sector_metric($metric_data) {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    // Logic to create new sector metric would go here
    
    return ['success' => true]; // Placeholder
}
?>
