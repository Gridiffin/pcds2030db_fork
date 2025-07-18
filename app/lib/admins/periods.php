<?php
/**
 * Reporting Period Management Functions
 * 
 * Contains functions for managing reporting periods (add, update, delete, status changes)
 */

require_once dirname(__DIR__) . '/utilities.php';
require_once 'core.php';
require_once dirname(__DIR__) . '/db_names_helper.php';

/**
 * Manage reporting periods (open/close)
 * @param int $period_id The reporting period to update
 * @param string $status New status ('open' or 'closed')
 * @return array Result of the status update operation
 */
function update_reporting_period_status($period_id, $status) {
    global $conn;
    
    // Only admin can update period status
    if (!is_admin()) {
        return ['error' => 'Permission denied'];
    }
    
    if (!in_array($status, ['open', 'closed'])) {
        return ['error' => 'Invalid status value'];
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // If setting to open, first close all other periods
        if ($status == 'open') {
            $close_query = "UPDATE reporting_periods SET status = 'closed' WHERE period_id != ?";
            $stmt = $conn->prepare($close_query);
            $stmt->bind_param("i", $period_id);
            $stmt->execute();
        }
        
        // Now update the specific period
        $query = "UPDATE reporting_periods SET status = ? WHERE period_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $period_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Get the period details for the response
        $period_query = "SELECT year, period_type, period_number FROM reporting_periods WHERE period_id = ?";
        $stmt = $conn->prepare($period_query);
        $stmt->bind_param("i", $period_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $period = $result->fetch_assoc();
        
        $period_name = "{$period['period_type']} {$period['period_number']} {$period['year']}";
        
        return [
            'success' => true,
            'message' => "Period {$period_name} has been " . 
                        ($status == 'open' ? "opened" : "closed") . " for submissions.",
            'period_id' => $period_id,
            'new_status' => $status
        ];
    } catch (Exception $e) {
        // Roll back transaction on error
        $conn->rollback();
        return ['error' => 'Failed to update period status: ' . $e->getMessage()];
    }
}

/**
 * Add a new reporting period
 * @param int $year Year
 * @param string $period_type Period type (quarter, half, yearly)
 * @param int $period_number Period number
 * @param string $start_date Start date (YYYY-MM-DD)
 * @param string $end_date End date (YYYY-MM-DD)
 * @param string $status Status (open/closed)
 * @return array Result of operation
 */
function add_reporting_period($year, $period_type, $period_number, $start_date, $end_date, $status = 'open') {
    global $conn;
      // Validate inputs
    if (!$year || !$period_type || !$period_number || !$start_date || !$end_date) {
        return ['error' => 'All fields are required'];
    }
    
    if (!in_array($period_type, ['quarter', 'half', 'yearly'])) {
        return ['error' => 'Period type must be quarter, half, or yearly'];
    }
    
    if ($period_type == 'quarter' && ($period_number < 1 || $period_number > 4)) {
        return ['error' => 'Quarter period number must be between 1 and 4'];
    }
    
    if ($period_type == 'half' && ($period_number < 1 || $period_number > 2)) {
        return ['error' => 'Half yearly period number must be between 1 and 2'];
    }
    
    if (strtotime($start_date) > strtotime($end_date)) {
        return ['error' => 'End date cannot be before start date'];
    }
    
    // Check if period already exists
    $check_query = "SELECT * FROM reporting_periods WHERE year = ? AND period_type = ? AND period_number = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("isi", $year, $period_type, $period_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['error' => "Period {$period_type} {$period_number} {$year} already exists"];
    }
    
    // Check for date range overlap with other periods
    $overlap_query = "SELECT * FROM reporting_periods WHERE 
                      (? BETWEEN start_date AND end_date) OR 
                      (? BETWEEN start_date AND end_date) OR 
                      (start_date BETWEEN ? AND ?) OR 
                      (end_date BETWEEN ? AND ?)";
                      
    $stmt = $conn->prepare($overlap_query);
    $stmt->bind_param("ssssss", $start_date, $end_date, $start_date, $end_date, $start_date, $end_date);
    $stmt->execute();
    $overlap_result = $stmt->get_result();
    
    if ($overlap_result->num_rows > 0) {
        return ['error' => 'Date range overlaps with existing period(s)'];
    }
    
    // Insert new period
    $insert_query = "INSERT INTO reporting_periods (year, period_type, period_number, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("isisss", $year, $period_type, $period_number, $start_date, $end_date, $status);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => "Reporting period {$period_type} {$period_number} {$year} added successfully"
        ];
    } else {
        return ['error' => 'Failed to add reporting period: ' . $stmt->error];
    }
}

/**
 * Update an existing reporting period
 * @param int $period_id Period ID
 * @param int $year Year
 * @param string $period_type Period type (quarter, half, yearly)
 * @param int $period_number Period number
 * @param string $start_date Start date (YYYY-MM-DD)
 * @param string $end_date End date (YYYY-MM-DD)
 * @param string $status Status (open/closed)
 * @return array Result of operation
 */
function update_reporting_period($period_id, $year, $period_type, $period_number, $start_date, $end_date, $status) {
    global $conn;
      // Validate inputs
    if (!$period_id || !$year || !$period_type || !$period_number || !$start_date || !$end_date) {
        return ['error' => 'All fields are required'];
    }
    
    if (!in_array($period_type, ['quarter', 'half', 'yearly'])) {
        return ['error' => 'Period type must be quarter, half, or yearly'];
    }
    
    if ($period_type == 'quarter' && ($period_number < 1 || $period_number > 4)) {
        return ['error' => 'Quarter period number must be between 1 and 4'];
    }
    
    if ($period_type == 'half' && ($period_number < 1 || $period_number > 2)) {
        return ['error' => 'Half yearly period number must be between 1 and 2'];
    }
    
    if (strtotime($start_date) > strtotime($end_date)) {
        return ['error' => 'End date cannot be before start date'];
    }
    
    // Check if another period already exists with same year/period_type/period_number
    $check_query = "SELECT * FROM reporting_periods WHERE year = ? AND period_type = ? AND period_number = ? AND period_id != ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("isii", $year, $period_type, $period_number, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['error' => "Another period with {$period_type} {$period_number} {$year} already exists"];
    }
    
    // Check for date range overlap with other periods
    $overlap_query = "SELECT * FROM reporting_periods WHERE 
                      ((? BETWEEN start_date AND end_date) OR 
                      (? BETWEEN start_date AND end_date) OR 
                      (start_date BETWEEN ? AND ?) OR 
                      (end_date BETWEEN ? AND ?)) AND 
                      period_id != ?";
                      
    $stmt = $conn->prepare($overlap_query);
    $stmt->bind_param("ssssssi", $start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $period_id);
    $stmt->execute();
    $overlap_result = $stmt->get_result();
    
    if ($overlap_result->num_rows > 0) {
        return ['error' => 'Date range overlaps with existing period(s)'];
    }
    
    // Update period
    $update_query = "UPDATE reporting_periods 
                     SET year = ?, period_type = ?, period_number = ?, start_date = ?, end_date = ?, status = ? 
                     WHERE period_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("isisssi", $year, $period_type, $period_number, $start_date, $end_date, $status, $period_id);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => "Reporting period {$period_type} {$period_number} {$year} updated successfully"
        ];
    } else {
        return ['error' => 'Failed to update reporting period: ' . $stmt->error];
    }
}

/**
 * Delete a reporting period
 * @param int $period_id Period ID to delete
 * @return array Result of the operation
 */
function delete_reporting_period($period_id) {
    global $conn;
    
    // Only admin can delete periods
    if (!is_admin()) {
        return ['error' => 'Permission denied'];
    }
    
    // Validate period ID
    $period_id = intval($period_id);
    if (!$period_id) {
        return ['error' => 'Invalid period ID'];
    }
    
    // Check if period exists and get its details for the response message
    $check_query = "SELECT year, period_type, period_number FROM reporting_periods WHERE period_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['error' => 'Reporting period not found'];
    }
    
    $period = $result->fetch_assoc();
    $period_name = "{$period['period_type']} {$period['period_number']} {$period['year']}";
    
    // Check if this period has any associated submissions
    $submission_check = "SELECT COUNT(*) as count FROM program_submissions WHERE period_id = ? AND is_deleted = 0";
    $stmt = $conn->prepare($submission_check);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $submission_result = $stmt->get_result();
    $submission_count = $submission_result->fetch_assoc()['count'];
    
    if ($submission_count > 0) {
        return [
            'error' => "Cannot delete period {$period_name} because it has {$submission_count} associated submissions. Delete the submissions first or contact system administrator."
        ];
    }
    
    // Delete the reporting period
    $delete_query = "DELETE FROM reporting_periods WHERE period_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $period_id);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => "Reporting period {$period_name} deleted successfully"
        ];
    } else {
        return ['error' => 'Failed to delete reporting period: ' . $stmt->error];
    }
}

/**
 * Check if dates match standard quarter dates
 * @param int $year Year
 * @param int $quarter Quarter (1-4)
 * @param string $start_date Start date (YYYY-MM-DD)
 * @param string $end_date End date (YYYY-MM-DD)
 * @return bool True if dates match standard quarter dates
 */
function is_standard_quarter_date($year, $quarter, $start_date, $end_date) {
    $standard_dates = [
        1 => [
            'start' => "$year-01-01",
            'end' => "$year-03-31"
        ],
        2 => [
            'start' => "$year-04-01",
            'end' => "$year-06-30"
        ],
        3 => [
            'start' => "$year-07-01",
            'end' => "$year-09-30"
        ],
        4 => [
            'start' => "$year-10-01",
            'end' => "$year-12-31"
        ],
        5 => [ // Half Yearly 1 (H1)
            'start' => "$year-01-01",
            'end' => "$year-06-30"
        ],
        6 => [ // Half Yearly 2 (H2)
            'start' => "$year-07-01",
            'end' => "$year-12-31"
        ]
    ];
    
    $quarter = intval($quarter);
    if (!isset($standard_dates[$quarter])) {
        return false;
    }
    
    return ($start_date === $standard_dates[$quarter]['start'] && 
            $end_date === $standard_dates[$quarter]['end']);
}
?>