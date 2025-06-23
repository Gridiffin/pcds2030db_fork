<?php
/**
 * Outcome Automation Functions
 * 
 * Handles automated updates to outcome data when linked programs change status
 */

require_once 'db_connect.php';
require_once 'audit_log.php';

/**
 * Update outcome data when a linked program status changes
 * 
 * @param int $program_id The program that changed status
 * @param string $new_status The new status of the program
 * @param int $period_id The reporting period
 * @param int $user_id The user making the change
 * @return bool Success status
 */
function updateOutcomeDataOnProgramStatusChange($program_id, $new_status, $period_id, $user_id) {
    global $conn;
    
    try {
        // Check if this program is linked to any outcomes
        $links_query = "SELECT pol.outcome_id, od.detail_name, od.is_cumulative
                       FROM program_outcome_links pol
                       JOIN outcomes_details od ON pol.outcome_id = od.detail_id
                       WHERE pol.program_id = ?";
        
        $stmt = $conn->prepare($links_query);
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        $links_result = $stmt->get_result();
        
        if ($links_result->num_rows === 0) {
            // No outcome links, nothing to update
            return true;
        }
        
        // Get program information
        $program_query = "SELECT program_name, sector_id FROM programs WHERE program_id = ?";
        $prog_stmt = $conn->prepare($program_query);
        $prog_stmt->bind_param("i", $program_id);
        $prog_stmt->execute();
        $program_result = $prog_stmt->get_result();
        $program_data = $program_result->fetch_assoc();
        
        if (!$program_data) {
            throw new Exception("Program not found: $program_id");
        }
        
        $update_count = 0;
        
        // Process each linked outcome
        while ($link = $links_result->fetch_assoc()) {
            $outcome_id = $link['outcome_id'];
            $outcome_name = $link['detail_name'];
            $is_cumulative = $link['is_cumulative'];
            
            // Only process if the program is being marked as completed
            if (in_array($new_status, ['completed', 'target-achieved'])) {
                // Check if there's already outcome data for this period/sector/metric
                $existing_query = "SELECT id, data_json 
                                 FROM sector_outcomes_data 
                                 WHERE metric_id = ? AND sector_id = ? AND period_id = ? AND is_draft = 0";
                
                $existing_stmt = $conn->prepare($existing_query);
                $existing_stmt->bind_param("iii", $outcome_id, $program_data['sector_id'], $period_id);
                $existing_stmt->execute();
                $existing_result = $existing_stmt->get_result();
                
                if ($existing_result->num_rows > 0) {
                    // Update existing outcome data
                    $existing_data = $existing_result->fetch_assoc();
                    $data_json = json_decode($existing_data['data_json'], true);
                    
                    // Add program completion to the data
                    if (!isset($data_json['completed_programs'])) {
                        $data_json['completed_programs'] = [];
                    }
                    
                    // Check if program is already recorded as completed
                    $already_recorded = false;
                    foreach ($data_json['completed_programs'] as $completed_prog) {
                        if ($completed_prog['program_id'] == $program_id) {
                            $already_recorded = true;
                            break;
                        }
                    }
                    
                    if (!$already_recorded) {
                        $data_json['completed_programs'][] = [
                            'program_id' => $program_id,
                            'program_name' => $program_data['program_name'],
                            'completion_date' => date('Y-m-d'),
                            'period_id' => $period_id
                        ];
                        
                        // For cumulative metrics, increment the total
                        if ($is_cumulative && isset($data_json['total_value'])) {
                            $data_json['total_value'] = ($data_json['total_value'] ?? 0) + 1;
                        }
                        
                        // Update the database record
                        $update_query = "UPDATE sector_outcomes_data 
                                       SET data_json = ?, updated_at = CURRENT_TIMESTAMP 
                                       WHERE id = ?";
                        
                        $update_stmt = $conn->prepare($update_query);
                        $json_string = json_encode($data_json);
                        $update_stmt->bind_param("si", $json_string, $existing_data['id']);
                        $update_stmt->execute();
                        
                        $update_count++;
                        
                        // Log the automated update
                        log_audit_action($user_id, 'outcome_auto_update', 
                                      "Automatically updated outcome '$outcome_name' due to program completion: {$program_data['program_name']}", 
                                      'success');
                    }
                } else {
                    // Create new outcome data record
                    $data_json = [
                        'total_value' => $is_cumulative ? 1 : 0,
                        'completed_programs' => [
                            [
                                'program_id' => $program_id,
                                'program_name' => $program_data['program_name'],
                                'completion_date' => date('Y-m-d'),
                                'period_id' => $period_id
                            ]
                        ],
                        'auto_generated' => true,
                        'source' => 'program_completion'
                    ];
                    
                    $insert_query = "INSERT INTO sector_outcomes_data 
                                   (metric_id, sector_id, period_id, table_name, data_json, is_draft, submitted_by, created_at) 
                                   VALUES (?, ?, ?, ?, ?, 0, ?, CURRENT_TIMESTAMP)";
                    
                    $table_name = "auto_generated_" . $outcome_id . "_" . $program_data['sector_id'] . "_" . $period_id;
                    $json_string = json_encode($data_json);
                    
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bind_param("iiissi", $outcome_id, $program_data['sector_id'], $period_id, 
                                           $table_name, $json_string, $user_id);
                    $insert_stmt->execute();
                    
                    $update_count++;
                    
                    // Log the automated creation
                    log_audit_action($user_id, 'outcome_auto_create', 
                                  "Automatically created outcome data for '$outcome_name' due to program completion: {$program_data['program_name']}", 
                                  'success');
                }
            }
        }
        
        return $update_count > 0;
        
    } catch (Exception $e) {
        error_log("Error in updateOutcomeDataOnProgramStatusChange: " . $e->getMessage());
        log_audit_action($user_id, 'outcome_auto_update_error', 
                      "Failed to auto-update outcomes for program $program_id: " . $e->getMessage(), 
                      'error');
        return false;
    }
}

/**
 * Get programs linked to a specific outcome
 * 
 * @param int $outcome_id The outcome ID
 * @return array Array of linked programs
 */
function getLinkedPrograms($outcome_id) {
    global $conn;
    
    $query = "SELECT p.program_id, p.program_name, p.sector_id, u.agency_name
              FROM program_outcome_links pol
              JOIN programs p ON pol.program_id = p.program_id
              JOIN users u ON p.owner_agency_id = u.user_id
              WHERE pol.outcome_id = ?
              ORDER BY p.program_name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $outcome_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    return $programs;
}

/**
 * Check if a program is linked to any outcomes
 * 
 * @param int $program_id The program ID
 * @return bool True if linked to outcomes
 */
function isProgramLinkedToOutcomes($program_id) {
    global $conn;
    
    $query = "SELECT COUNT(*) as count FROM program_outcome_links WHERE program_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'] > 0;
}

/**
 * Get outcome data with cumulative calculations
 * 
 * @param int $outcome_id The outcome detail ID
 * @param int $sector_id The sector ID
 * @param int $period_id The period ID (for cumulative, this is the end period)
 * @return array Outcome data
 */
function getOutcomeDataWithCumulative($outcome_id, $sector_id, $period_id) {
    global $conn;
    
    // First, check if this outcome is cumulative
    $outcome_query = "SELECT is_cumulative, detail_name FROM outcomes_details WHERE detail_id = ?";
    $stmt = $conn->prepare($outcome_query);
    $stmt->bind_param("i", $outcome_id);
    $stmt->execute();
    $outcome_result = $stmt->get_result();
    $outcome_info = $outcome_result->fetch_assoc();
    
    if (!$outcome_info) {
        return null;
    }
    
    if ($outcome_info['is_cumulative']) {
        // For cumulative data, get all periods up to and including the specified period
        $query = "SELECT sod.*, rp.year, rp.quarter 
                 FROM sector_outcomes_data sod
                 JOIN reporting_periods rp ON sod.period_id = rp.period_id
                 WHERE sod.metric_id = ? AND sod.sector_id = ? AND sod.period_id <= ? AND sod.is_draft = 0
                 ORDER BY rp.year, rp.quarter";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $outcome_id, $sector_id, $period_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $cumulative_total = 0;
        $data_entries = [];
        
        while ($row = $result->fetch_assoc()) {
            $data_json = json_decode($row['data_json'], true);
            if (isset($data_json['total_value'])) {
                $cumulative_total += $data_json['total_value'];
            }
            $data_entries[] = $row;
        }
        
        return [
            'is_cumulative' => true,
            'cumulative_total' => $cumulative_total,
            'data_entries' => $data_entries,
            'outcome_name' => $outcome_info['detail_name']
        ];
    } else {
        // For non-cumulative data, get only the specific period
        $query = "SELECT sod.*, rp.year, rp.quarter 
                 FROM sector_outcomes_data sod
                 JOIN reporting_periods rp ON sod.period_id = rp.period_id
                 WHERE sod.metric_id = ? AND sod.sector_id = ? AND sod.period_id = ? AND sod.is_draft = 0";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $outcome_id, $sector_id, $period_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data_entries = [];
        while ($row = $result->fetch_assoc()) {
            $data_entries[] = $row;
        }
        
        return [
            'is_cumulative' => false,
            'data_entries' => $data_entries,
            'outcome_name' => $outcome_info['detail_name']
        ];
    }
}
?>
